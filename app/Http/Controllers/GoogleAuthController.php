<?php
// app/Http/Controllers/GoogleAuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\JWK;
use Kreait\Firebase\Factory;
use App\Models\User;
use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Services\AutoSubscriptionService;

class GoogleAuthController extends Controller
{
    // =========================================================================
    //  GOOGLE — UNCHANGED
    // =========================================================================
    public function mobileLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
            'role'     => 'required|in:user,agent,office',
            'language' => 'nullable|in:en,ar,ku',
        ]);

        try {
            $credPath = base_path('real-estate.json');
            Log::info('[GoogleAuth] cred file exists: ' . (file_exists($credPath) ? 'YES' : 'NO'));
            Log::info('[GoogleAuth] server time: ' . now()->toISOString());
            Log::info('[GoogleAuth] role: ' . $request->role);

            $factory       = (new Factory)->withServiceAccount($credPath);
            $auth          = $factory->createAuth();
            $verifiedToken = $auth->verifyIdToken($request->id_token);
            Log::info('[GoogleAuth] token verified OK');
        } catch (\Throwable $e) {
            Log::error('[GoogleAuth] token verification failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Invalid or expired Google token.',
                'debug'   => $e->getMessage(),
            ], 401);
        }

        $firebaseUid   = $verifiedToken->claims()->get('sub');
        $email         = $verifiedToken->claims()->get('email');
        $name          = $verifiedToken->claims()->get('name') ?? 'Google User';
        $avatar        = $verifiedToken->claims()->get('picture');
        $emailVerified = $verifiedToken->claims()->get('email_verified', false);

        Log::info('[GoogleAuth] claims', ['uid' => $firebaseUid, 'email' => $email]);

        if (!$email) {
            return response()->json(['message' => 'This Google account has no email address.'], 422);
        }

        return match ($request->role) {
            'user'   => $this->handleUser($email, $name, $avatar, $firebaseUid, $emailVerified, $request),
            'agent'  => $this->handleAgent($email, $name, $avatar, $firebaseUid, $request),
            'office' => $this->handleOffice($email, $name, $avatar, $firebaseUid, $request),
        };
    }

    // =========================================================================
    //  APPLE — NEW endpoint
    //  Route: POST /api/v1/auth/apple/mobile
    //  Handles all 3 roles: user / agent / office
    // =========================================================================
    public function mobileLoginApple(Request $request)
    {
        $request->validate([
            'identity_token' => 'required|string',
            'role'           => 'required|in:user,agent,office',
            'language'       => 'nullable|in:en,ar,ku',
            'display_name'   => 'nullable|string|max:100',
        ]);

        // ── 1. Verify Apple identity token ─────────────────────────────────
        try {
            $claims = $this->verifyAppleToken($request->identity_token);
        } catch (\Throwable $e) {
            Log::error('[AppleAuth] token verification failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Invalid or expired Apple token.',
                'debug'   => $e->getMessage(),
            ], 401);
        }

        $appleUid      = $claims->sub;
        $email         = $claims->email ?? null;
        $emailVerified = property_exists($claims, 'email_verified')
            ? ($claims->email_verified === 'true' || $claims->email_verified === true)
            : false;

        // Apple only sends display_name on first sign-in via the Flutter SDK
        $name = $request->input('display_name') ?: null;

        Log::info('[AppleAuth] claims', [
            'uid'   => $appleUid,
            'email' => $email,
            'role'  => $request->role,
            'name'  => $name,
        ]);

        // Apple hides email after the very first sign-in.
        // On subsequent logins, look up the existing record by apple_id.
        if (!$email) {
            $email = $this->resolveEmailByAppleId($appleUid, $request->role);
            if (!$email) {
                return response()->json([
                    'message' => 'Apple did not provide an email address. Please go to iPhone Settings → Apple ID → Password & Security → Apps Using Apple ID, remove Dream Mulk, then sign in again.',
                ], 422);
            }
        }

        // Fallback name when Apple didn't send one and no cached name from Flutter
        if (!$name) {
            $name = explode('@', $email)[0];
        }

        return match ($request->role) {
            'user'   => $this->handleAppleUser($email, $name, $appleUid, $emailVerified, $request),
            'agent'  => $this->handleAppleAgent($email, $name, $appleUid, $request),
            'office' => $this->handleAppleOffice($email, $name, $appleUid, $request),
        };
    }

    // =========================================================================
    //  APPLE TOKEN VERIFICATION
    //  Uses firebase/php-jwt ≥ 6.x  (composer require firebase/php-jwt:^6.0)
    //  Apple's JWKS is cached for 24 hours.
    // =========================================================================
    private function verifyAppleToken(string $identityToken): object
    {
        // Fetch + cache Apple's public keys (rotated infrequently)
        $jwks = Cache::remember('apple_public_keys', 86400, function () {
            $response = Http::timeout(10)->get('https://appleid.apple.com/auth/keys');
            if (!$response->ok()) {
                throw new \RuntimeException('Failed to fetch Apple public keys');
            }
            return $response->json();
        });

        $keys    = JWK::parseKeySet($jwks);
        $decoded = JWT::decode($identityToken, $keys);

        // Validate audience = your iOS bundle ID
        $bundleId = config('services.apple.bundle_id', env('APPLE_BUNDLE_ID', 'com.dreammulk.app'));
        if ($decoded->aud !== $bundleId) {
            throw new \RuntimeException('Apple token audience mismatch: ' . $decoded->aud);
        }

        if ($decoded->iss !== 'https://appleid.apple.com') {
            throw new \RuntimeException('Apple token issuer mismatch');
        }

        return $decoded;
    }

    // =========================================================================
    //  Resolve email for returning Apple users (Apple hides it after 1st login)
    // =========================================================================
    private function resolveEmailByAppleId(string $appleUid, string $role): ?string
    {
        return match ($role) {
            'agent'  => Agent::where('apple_id', $appleUid)->value('primary_email'),
            'office' => RealEstateOffice::where('apple_id', $appleUid)->value('email_address'),
            default  => User::where('apple_id', $appleUid)->value('email'),
        };
    }

    // =========================================================================
    //  APPLE — USER
    // =========================================================================
    private function handleAppleUser(
        string $email,
        string $name,
        string $appleUid,
        bool $emailVerified,
        Request $request
    ) {
        Log::info('[AppleAuth] handleAppleUser: ' . $email);

        $isNewUser = false;
        $language  = $request->input('language', 'en');

        $user = User::where('apple_id', $appleUid)->first()
            ?? User::where('email', $email)->first();

        if (!$user) {
            $isNewUser = true;

            // Unique username
            $base     = Str::slug(Str::lower($name), '_') ?: 'user';
            $username = $base;
            $i        = 1;
            while (User::where('username', $username)->exists()) {
                $username = $base . '_' . $i++;
            }

            $user = User::create([
                'username'          => $username,
                'email'             => $email,
                'password'          => Hash::make(Str::random(32)),
                'apple_id'          => $appleUid,
                'photo_image'       => null,          // Apple never returns an avatar
                'email_verified_at' => $emailVerified ? now() : null,
                'is_verified'       => $emailVerified,
                'language'          => $language,
                'role'              => 'user',
                'device_tokens'     => [],
            ]);

            Log::info('[AppleAuth] new User created: ' . $user->id);
        } else {
            $patch = [];
            if (!$user->apple_id)                             $patch['apple_id']          = $appleUid;
            if (!$user->email_verified_at && $emailVerified) $patch['email_verified_at'] = now();
            if (!empty($patch))                               $user->update($patch);

            Log::info('[AppleAuth] existing User: ' . $user->id);
        }

        $user->update(['last_login_at' => now(), 'last_activity_at' => now()]);
        $this->storeFcmToken($user, $request);

        $token = $user->createToken('apple-mobile')->plainTextToken;

        return response()->json([
            'message'     => 'Login successful',
            'role'        => 'user',
            'token'       => $token,
            'is_new_user' => $isNewUser,
            'data'        => ['user' => $user, 'token' => $token],
        ]);
    }

    // =========================================================================
    //  APPLE — AGENT
    //  New agents: created + auto-subscribed to default 6-month plan.
    //  Existing: login only.
    // =========================================================================
    private function handleAppleAgent(
        string $email,
        string $name,
        string $appleUid,
        Request $request
    ) {
        Log::info('[AppleAuth] handleAppleAgent: ' . $email);

        $isNewUser       = false;
        $profileComplete = true;
        $language        = $request->input('language', 'en');

        $agent = Agent::where('apple_id', $appleUid)->first()
            ?? Agent::where('primary_email', $email)->first();

        if (!$agent) {
            $isNewUser       = true;
            $profileComplete = false;

            $agent                = new Agent();
            $agent->id            = (string) Str::uuid();
            $agent->agent_name    = $name;
            $agent->primary_email = $email;
            $agent->primary_phone = '';
            $agent->city          = '';
            $agent->password      = Hash::make(Str::random(32));
            $agent->is_verified   = false;
            $agent->status        = 'active';
            $agent->apple_id      = $appleUid;
            $agent->profile_image = null;
            $agent->language      = $language;
            $agent->device_tokens = [];
            $agent->type          = 'agent';
            $agent->save();

            Log::info('[AppleAuth] new Agent created: ' . $agent->id);

            // Auto-subscribe — never throws
            app(AutoSubscriptionService::class)->assignDefaultAgentSubscription($agent);
        } else {
            if (!$agent->apple_id) $agent->update(['apple_id' => $appleUid]);

            $phone           = trim((string) ($agent->primary_phone ?? ''));
            $city            = trim((string) ($agent->city ?? ''));
            $profileComplete = !($phone === '' && $city === '');

            Log::info('[AppleAuth] existing Agent', [
                'id'             => $agent->id,
                'profileComplete' => $profileComplete,
            ]);
        }

        $this->storeFcmToken($agent, $request);

        $token = $agent->createToken('apple-mobile')->plainTextToken;

        return response()->json([
            'message'          => 'Login successful',
            'role'             => 'agent',
            'token'            => $token,
            'is_new_user'      => $isNewUser,
            'data'             => ['user' => $agent, 'token' => $token],
            'profile_complete' => $profileComplete,
            'missing_fields'   => $profileComplete ? [] : ['primary_phone', 'city'],
        ]);
    }

    // =========================================================================
    //  APPLE — OFFICE
    //  New offices: created + auto-subscribed to default 6-month plan.
    //  Existing: login only.
    // =========================================================================
    private function handleAppleOffice(
        string $email,
        string $name,
        string $appleUid,
        Request $request
    ) {
        Log::info('[AppleAuth] handleAppleOffice: ' . $email);

        $isNewUser       = false;
        $profileComplete = true;
        $language        = $request->input('language', 'en');

        $office = RealEstateOffice::where('apple_id', $appleUid)->first()
            ?? RealEstateOffice::where('email_address', $email)->first();

        if (!$office) {
            $isNewUser       = true;
            $profileComplete = false;

            $companyName = $name ?: 'Apple Office';
            $i           = 1;
            while (RealEstateOffice::where('company_name', $companyName)->exists()) {
                $companyName = ($name ?: 'Apple Office') . ' ' . $i++;
            }

            $office = RealEstateOffice::create([
                'company_name'  => $companyName,
                'email_address' => $email,
                'password'      => Hash::make(Str::random(32)),
                'phone_number'  => '',
                'city'          => '',
                'profile_image' => null,
                'apple_id'      => $appleUid,
                'account_type'  => 'real_estate_official',
                'is_verified'   => false,
                'language'      => $language,
                'device_tokens' => [],
            ]);

            Log::info('[AppleAuth] new Office created: ' . $office->id);

            // Auto-subscribe — never throws
            app(AutoSubscriptionService::class)->assignDefaultOfficeSubscription($office);
        } else {
            if (!$office->apple_id) $office->update(['apple_id' => $appleUid]);

            $phone           = trim((string) ($office->phone_number ?? ''));
            $city            = trim((string) ($office->city ?? ''));
            $profileComplete = !($phone === '' && $city === '');

            Log::info('[AppleAuth] existing Office', [
                'id'             => $office->id,
                'profileComplete' => $profileComplete,
            ]);
        }

        $this->storeFcmToken($office, $request);

        $token = $office->createToken('apple-mobile')->plainTextToken;

        return response()->json([
            'message'          => 'Login successful',
            'role'             => 'office',
            'token'            => $token,
            'is_new_user'      => $isNewUser,
            'data'             => ['office' => $office, 'token' => $token],
            'profile_complete' => $profileComplete,
            'missing_fields'   => $profileComplete ? [] : ['phone_number', 'city'],
        ]);
    }

    // =========================================================================
    //  GOOGLE — handleUser / handleAgent / handleOffice  (UNCHANGED)
    // =========================================================================
    private function handleUser(string $email, string $name, ?string $avatar, string $firebaseUid, bool $emailVerified, Request $request)
    {
        Log::info('[GoogleAuth] handleUser: ' . $email);
        $isNewUser = false;
        $language  = $request->input('language', 'en');

        $user = User::where('google_id', $firebaseUid)->first()
            ?? User::where('email', $email)->first();

        if (!$user) {
            $isNewUser = true;
            $base      = Str::slug(Str::lower($name), '_') ?: 'user';
            $username  = $base;
            $i         = 1;
            while (User::where('username', $username)->exists()) {
                $username = $base . '_' . $i++;
            }
            $user = User::create([
                'username'          => $username,
                'email'             => $email,
                'password'          => Hash::make(Str::random(32)),
                'google_id'         => $firebaseUid,
                'photo_image'       => $avatar,
                'email_verified_at' => $emailVerified ? now() : null,
                'is_verified'       => $emailVerified,
                'language'          => $language,
                'role'              => 'user',
                'device_tokens'     => [],
            ]);
            Log::info('[GoogleAuth] new User created: ' . $user->id);
        } else {
            $patch = [];
            if (!$user->google_id)                           $patch['google_id']         = $firebaseUid;
            if (!$user->email_verified_at && $emailVerified) $patch['email_verified_at'] = now();
            if (!empty($patch))                              $user->update($patch);
            Log::info('[GoogleAuth] existing User: ' . $user->id);
        }

        $user->update(['last_login_at' => now(), 'last_activity_at' => now()]);
        $this->storeFcmToken($user, $request);
        $token = $user->createToken('google-mobile')->plainTextToken;

        return response()->json([
            'message'     => 'Login successful',
            'role'        => 'user',
            'token'       => $token,
            'is_new_user' => $isNewUser,
            'data'        => ['user' => $user, 'token' => $token],
        ]);
    }

    private function handleAgent(string $email, string $name, ?string $avatar, string $firebaseUid, Request $request)
    {
        Log::info('[GoogleAuth] handleAgent: ' . $email);
        $isNewUser       = false;
        $profileComplete = true;
        $language        = $request->input('language', 'en');

        $agent = Agent::where('google_id', $firebaseUid)->first()
            ?? Agent::where('primary_email', $email)->first();

        if (!$agent) {
            $isNewUser       = true;
            $profileComplete = false;

            $agent                = new Agent();
            $agent->id            = (string) Str::uuid();
            $agent->agent_name    = $name;
            $agent->primary_email = $email;
            $agent->primary_phone = '';
            $agent->city          = '';
            $agent->password      = Hash::make(Str::random(32));
            $agent->is_verified   = false;
            $agent->status        = 'active';
            $agent->google_id     = $firebaseUid;
            $agent->profile_image = $avatar;
            $agent->language      = $language;
            $agent->device_tokens = [];
            $agent->type          = 'agent';
            $agent->save();

            Log::info('[GoogleAuth] new Agent created: ' . $agent->id);
            app(AutoSubscriptionService::class)->assignDefaultAgentSubscription($agent);
        } else {
            if (!$agent->google_id) $agent->update(['google_id' => $firebaseUid]);
            $phone           = trim((string) ($agent->primary_phone ?? ''));
            $city            = trim((string) ($agent->city ?? ''));
            $profileComplete = !($phone === '' && $city === '');
            Log::info('[GoogleAuth] existing Agent', ['id' => $agent->id, 'phone' => $phone, 'city' => $city]);
        }

        $this->storeFcmToken($agent, $request);
        $token = $agent->createToken('google-mobile')->plainTextToken;

        return response()->json([
            'message'          => 'Login successful',
            'role'             => 'agent',
            'token'            => $token,
            'is_new_user'      => $isNewUser,
            'data'             => ['user' => $agent, 'token' => $token],
            'profile_complete' => $profileComplete,
            'missing_fields'   => $profileComplete ? [] : ['primary_phone', 'city'],
        ]);
    }

    private function handleOffice(string $email, string $name, ?string $avatar, string $firebaseUid, Request $request)
    {
        Log::info('[GoogleAuth] handleOffice: ' . $email);
        $isNewUser       = false;
        $profileComplete = true;
        $language        = $request->input('language', 'en');

        $office = RealEstateOffice::where('google_id', $firebaseUid)->first()
            ?? RealEstateOffice::where('email_address', $email)->first();

        if (!$office) {
            $isNewUser       = true;
            $profileComplete = false;

            $companyName = $name ?: 'Google Office';
            $i           = 1;
            while (RealEstateOffice::where('company_name', $companyName)->exists()) {
                $companyName = ($name ?: 'Google Office') . ' ' . $i++;
            }

            $office = RealEstateOffice::create([
                'company_name'  => $companyName,
                'email_address' => $email,
                'password'      => Hash::make(Str::random(32)),
                'phone_number'  => '',
                'city'          => '',
                'profile_image' => $avatar,
                'google_id'     => $firebaseUid,
                'account_type'  => 'real_estate_official',
                'is_verified'   => false,
                'language'      => $language,
                'device_tokens' => [],
            ]);

            Log::info('[GoogleAuth] new Office created: ' . $office->id);
            app(AutoSubscriptionService::class)->assignDefaultOfficeSubscription($office);
        } else {
            if (!$office->google_id) $office->update(['google_id' => $firebaseUid]);
            $phone           = trim((string) ($office->phone_number ?? ''));
            $city            = trim((string) ($office->city ?? ''));
            $profileComplete = !($phone === '' && $city === '');
            Log::info('[GoogleAuth] existing Office', ['id' => $office->id]);
        }

        $this->storeFcmToken($office, $request);
        $token = $office->createToken('google-mobile')->plainTextToken;

        return response()->json([
            'message'          => 'Login successful',
            'role'             => 'office',
            'token'            => $token,
            'is_new_user'      => $isNewUser,
            'data'             => ['office' => $office, 'token' => $token],
            'profile_complete' => $profileComplete,
            'missing_fields'   => $profileComplete ? [] : ['phone_number', 'city'],
        ]);
    }

    // =========================================================================
    //  FCM TOKEN HELPER — UNCHANGED
    // =========================================================================
    private function storeFcmToken($model, Request $request): void
    {
        $fcmToken = $request->input('fcm_token');
        if (!$fcmToken) {
            Log::info('[GoogleAuth] storeFcmToken: no fcm_token in request, skipping');
            return;
        }

        $deviceName = $request->header('X-Device-Name')
            ?? $request->input('device_name')
            ?? 'unknown';

        try {
            $tokens = $model->device_tokens ?? [];
            if (!is_array($tokens)) $tokens = [];

            $tokens = array_values(array_filter(
                $tokens,
                fn($t) => is_array($t) && ($t['device_name'] ?? '') !== $deviceName
            ));

            $tokens[] = [
                'fcm_token'    => $fcmToken,
                'device_name'  => $deviceName,
                'created_at'   => now()->toDateTimeString(),
                'last_login'   => now()->toDateTimeString(),
                'last_updated' => now()->toDateTimeString(),
            ];

            $model->update(['device_tokens' => $tokens]);
            Log::info('[GoogleAuth] FCM token stored', [
                'model_id'    => $model->id,
                'device_name' => $deviceName,
                'token_count' => count($tokens),
            ]);
        } catch (\Throwable $e) {
            Log::error('[GoogleAuth] storeFcmToken failed: ' . $e->getMessage(), [
                'model_id'    => $model->id,
                'device_name' => $deviceName,
            ]);
        }
    }
}
