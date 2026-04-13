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
            'user'   => $this->handleUser($email, $name, $avatar, $firebaseUid, $emailVerified),
            'agent'  => $this->handleAgent($email, $name, $avatar, $firebaseUid),
            'office' => $this->handleOffice($email, $firebaseUid),
        };
    }

    // =========================================================================
    // USER — unchanged from original, NO subscription logic for users
    // =========================================================================
    private function handleUser(string $email, string $name, ?string $avatar, string $firebaseUid, bool $emailVerified)
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
            if (!$user->google_id)                            $patch['google_id']         = $firebaseUid;
            if (!$user->email_verified_at && $emailVerified)  $patch['email_verified_at'] = now();
            if (!empty($patch))                               $user->update($patch);

            Log::info('[GoogleAuth] existing User: ' . $user->id);
        }

        $user->update(['last_login_at' => now(), 'last_activity_at' => now()]);
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
    // Existing agents → login only, no subscription change.
    // =========================================================================
    private function handleAgent(string $email, string $name, ?string $avatar, string $firebaseUid)
    {
        Log::info('[GoogleAuth] handleAgent: ' . $email);

        $isNewUser       = false;
        $profileComplete = true;

        $agent = Agent::where('google_id', $firebaseUid)->first()
            ?? Agent::where('primary_email', $email)->first();

        if (!$agent) {
            // ── Brand-new agent via Google sign-up ───────────────────────────
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
            // ── Existing agent — login only ───────────────────────────────────
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
    // Login only — offices CANNOT self-register via Google.
    // No subscription logic here — subscription is assigned at registration
    // time via OfficeAuthController::register() / registerApi().
    // =========================================================================
    private function handleOffice(string $email, string $firebaseUid)
    {
        Log::info('[GoogleAuth] handleOffice: ' . $email);

        $office = RealEstateOffice::where('google_id', $firebaseUid)->first()
            ?? RealEstateOffice::where('email_address', $email)->first();

        if (!$office) {
            Log::warning('[GoogleAuth] office not found: ' . $email);
            return response()->json([
                'message'               => 'No office account found for this Google account. Please register your office first.',
                'requires_registration' => true,
                'role'                  => 'office',
            ], 404);
        }

        if (!$office->google_id) {
            $office->update(['google_id' => $firebaseUid]);
        }

        $token = $office->createToken('google-mobile')->plainTextToken;
        Log::info('[GoogleAuth] office login success: ' . $office->id);

        return response()->json([
            'message'     => 'Login successful',
            'role'        => 'office',
            'token'       => $token,
            'is_new_user' => false,
            'office'      => $office,
        ]);
    }
}