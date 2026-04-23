<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use App\Models\User;
use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Services\AutoSubscriptionService;

class GoogleAuthController extends Controller
{
    public function mobileLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
            'role'     => 'required|in:user,agent,office',
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
            return response()->json([
                'message' => 'This Google account has no email address.',
            ], 422);
        }

        return match ($request->role) {
            'user'   => $this->handleUser($email, $name, $avatar, $firebaseUid, $emailVerified, $request),
            'agent'  => $this->handleAgent($email, $name, $avatar, $firebaseUid, $request),
            'office' => $this->handleOffice($email, $name, $avatar, $firebaseUid, $request),
        };
    }

    // =========================================================================
    // FCM TOKEN HELPER
    // Stores/replaces FCM token per device using X-Device-Name header.
    // Same structure as existing device_tokens JSON array.
    // Never throws — failure is logged and skipped silently.
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

            // Ensure it's an array (guard against null/malformed JSON)
            if (!is_array($tokens)) {
                $tokens = [];
            }

            // Remove any existing entry for this device name (dedup by device)
            $tokens = array_values(array_filter(
                $tokens,
                fn($t) => is_array($t) && ($t['device_name'] ?? '') !== $deviceName
            ));

            // Append fresh entry
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

    // =========================================================================
    // USER — NO subscription logic for users
    // =========================================================================
    private function handleUser(string $email, string $name, ?string $avatar, string $firebaseUid, bool $emailVerified, Request $request)
    {
        Log::info('[GoogleAuth] handleUser: ' . $email);

        $isNewUser = false;

        $user = User::where('google_id', $firebaseUid)->first()
            ?? User::where('email', $email)->first();

        if (!$user) {
            $isNewUser = true;

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
                'google_id'         => $firebaseUid,
                'photo_image'       => $avatar,
                'email_verified_at' => $emailVerified ? now() : null,
                'is_verified'       => $emailVerified,
                'language'          => 'en',
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

        // Store FCM token after user is guaranteed to exist
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

    // =========================================================================
    // AGENT
    // New agents  → created + auto-subscribed to default 6-month plan.
    // Existing agents → login only, subscription untouched.
    // =========================================================================
    private function handleAgent(string $email, string $name, ?string $avatar, string $firebaseUid, Request $request)
    {
        Log::info('[GoogleAuth] handleAgent: ' . $email);

        $isNewUser       = false;
        $profileComplete = true;

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
            $agent->language      = 'en';
            $agent->device_tokens = [];
            $agent->save();

            Log::info('[GoogleAuth] new Agent created: ' . $agent->id);

            // Auto-subscribe to default 6-month agent plan.
            // Never throws — failure is logged and skipped silently.
            app(AutoSubscriptionService::class)->assignDefaultAgentSubscription($agent);
        } else {
            // Existing agent — login only, no subscription change
            if (!$agent->google_id) {
                $agent->update(['google_id' => $firebaseUid]);
            }

            $phone = trim((string) ($agent->primary_phone ?? ''));
            $city  = trim((string) ($agent->city ?? ''));

            Log::info('[GoogleAuth] existing Agent', [
                'id'    => $agent->id,
                'phone' => $phone,
                'city'  => $city,
            ]);

            $profileComplete = !($phone === '' && $city === '');
            Log::info('[GoogleAuth] profileComplete: ' . ($profileComplete ? 'true' : 'false'));
        }

        // Store FCM token after agent is guaranteed to exist
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

    // =========================================================================
    // OFFICE
    // New offices  → created from Google claims + auto-subscribed to default
    //               6-month plan. profile_complete = false so Flutter shows
    //               the completion screen (phone_number + city needed).
    // Existing offices → login only, subscription untouched.
    // =========================================================================
    private function handleOffice(string $email, string $name, ?string $avatar, string $firebaseUid, Request $request)
    {
        Log::info('[GoogleAuth] handleOffice: ' . $email);

        $isNewUser       = false;
        $profileComplete = true;

        $office = RealEstateOffice::where('google_id', $firebaseUid)->first()
            ?? RealEstateOffice::where('email_address', $email)->first();

        if (!$office) {
            $isNewUser       = true;
            $profileComplete = false;

            // Ensure company_name is unique
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
                'language'      => 'en',
                'device_tokens' => [],
            ]);

            Log::info('[GoogleAuth] new Office created: ' . $office->id);

            // Auto-subscribe to default 6-month office plan.
            // Never throws — failure is logged and skipped silently.
            app(AutoSubscriptionService::class)->assignDefaultOfficeSubscription($office);
        } else {
            // Existing office — login only, no subscription change
            if (!$office->google_id) {
                $office->update(['google_id' => $firebaseUid]);
            }

            $phone = trim((string) ($office->phone_number ?? ''));
            $city  = trim((string) ($office->city ?? ''));

            Log::info('[GoogleAuth] existing Office', [
                'id'    => $office->id,
                'phone' => $phone,
                'city'  => $city,
            ]);

            $profileComplete = !($phone === '' && $city === '');
            Log::info('[GoogleAuth] profileComplete: ' . ($profileComplete ? 'true' : 'false'));
        }

        // Store FCM token after office is guaranteed to exist
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
}