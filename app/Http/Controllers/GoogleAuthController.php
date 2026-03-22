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

class GoogleAuthController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/auth/google/mobile
    // Body: { "id_token": "...", "role": "user|agent|office" }
    // ─────────────────────────────────────────────────────────────────────────
    public function mobileLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
            'role'     => 'required|in:user,agent,office',
        ]);

        // ── 1. Verify Firebase ID token ──────────────────────────────────────
        try {
            $factory = (new Factory)->withServiceAccount(
                base_path('real-estate.json')  // ← points to project root
            );
            $auth          = $factory->createAuth();
            $verifiedToken = $auth->verifyIdToken($request->id_token);
        } catch (\Throwable $e) {
            Log::error('GoogleAuth: token verification failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Invalid or expired Google token.'], 401);
        }

        // ── 2. Extract claims from token ─────────────────────────────────────
        $firebaseUid   = $verifiedToken->claims()->get('sub');
        $email         = $verifiedToken->claims()->get('email');
        $name          = $verifiedToken->claims()->get('name') ?? 'Google User';
        $avatar        = $verifiedToken->claims()->get('picture');
        $emailVerified = $verifiedToken->claims()->get('email_verified', false);

        if (!$email) {
            return response()->json([
                'message' => 'This Google account has no email address. Please use a different account.',
            ], 422);
        }

        return match ($request->role) {
            'user'   => $this->handleUser($email, $name, $avatar, $firebaseUid, $emailVerified),
            'agent'  => $this->handleAgent($email, $name, $avatar, $firebaseUid),
            'office' => $this->handleOffice($email, $firebaseUid),
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // USER
    // Table: users
    // Required in fillable: username (unique), email (unique), password
    // Available from Google: email, display name (→ username), avatar (→ photo_image)
    // Already has google_id in fillable ✅
    // ─────────────────────────────────────────────────────────────────────────
    private function handleUser(
        string $email,
        string $name,
        ?string $avatar,
        string $firebaseUid,
        bool $emailVerified
    ) {
        // Find by google_id first (fastest), fall back to email
        $user = User::where('google_id', $firebaseUid)->first()
            ?? User::where('email', $email)->first();

        if (!$user) {
            // ── Generate a unique username from Google display name ──────────
            $base     = Str::slug(Str::lower($name), '_');
            $base     = $base ?: 'user'; // fallback if name slugs to empty
            $username = $base;
            $i        = 1;
            while (User::where('username', $username)->exists()) {
                $username = $base . '_' . $i++;
            }

            $user = User::create([
                // ── Required ─────────────────────────────────────────────────
                'username'          => $username,
                'email'             => $email,
                'password'          => Hash::make(Str::random(32)), // never usable directly

                // ── From Google ───────────────────────────────────────────────
                'google_id'         => $firebaseUid,
                'photo_image'       => $avatar,
                'email_verified_at' => $emailVerified ? now() : null,
                'is_verified'       => $emailVerified,

                // ── Defaults ─────────────────────────────────────────────────
                'language'          => 'en',
                'role'              => 'user',
                'device_tokens'     => [],
            ]);

            Log::info('GoogleAuth: new User created', [
                'id' => $user->id,
                'email' => $email,
            ]);
        } else {
            // ── Existing user — patch missing google_id / verify email ────────
            $patch = [];
            if (!$user->google_id)         $patch['google_id']         = $firebaseUid;
            if (!$user->email_verified_at && $emailVerified)
                $patch['email_verified_at'] = now();
            if (!empty($patch))             $user->update($patch);

            Log::info('GoogleAuth: existing User found', ['id' => $user->id]);
        }

        $user->update(['last_login_at' => now(), 'last_activity_at' => now()]);

        $token = $user->createToken('google-mobile')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'role'    => 'user',
            'token'   => $token,
            'data'    => [
                'user'  => $user,
                'token' => $token,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AGENT
    // Table: agents
    // Required (NOT NULL): agent_name, primary_email, primary_phone, password,
    //                      city, is_verified, status
    // Available from Google: display name (→ agent_name), email, avatar (→ profile_image)
    // NOT available from Google: primary_phone, city
    //   → stored as '' (empty string, satisfies NOT NULL)
    //   → response includes profile_complete: false + missing_fields
    //   → Flutter shows "Complete your profile" screen
    //
    // google_id NOT in fillable — we'll add it to the migration.
    // ─────────────────────────────────────────────────────────────────────────
    private function handleAgent(
        string $email,
        string $name,
        ?string $avatar,
        string $firebaseUid
    ) {
        $agent = Agent::where('google_id', $firebaseUid)->first()
            ?? Agent::where('primary_email', $email)->first();

        $profileComplete = true;

        if (!$agent) {
            $profileComplete = false;

            $agent             = new Agent();
            $agent->id         = (string) Str::uuid();

            // ── Required fields — all must be non-null ────────────────────────
            $agent->agent_name    = $name;
            $agent->primary_email = $email;
            $agent->primary_phone = '';          // required NOT NULL — user fills later
            $agent->city          = '';          // required NOT NULL — user fills later
            $agent->password      = Hash::make(Str::random(32));
            $agent->is_verified   = false;
            $agent->status        = 'active';    // status column from registerApi

            // ── Optional fields from Google ───────────────────────────────────
            $agent->google_id     = $firebaseUid;
            $agent->profile_image = $avatar;

            // ── Defaults ─────────────────────────────────────────────────────
            $agent->language      = 'en';
            $agent->device_tokens = [];

            $agent->save();

            Log::info('GoogleAuth: new Agent created', [
                'id' => $agent->id,
                'email' => $email,
            ]);
        } else {
            if (!$agent->google_id) {
                $agent->update(['google_id' => $firebaseUid]);
            }
            // Profile complete if required fields are filled by user
            $profileComplete = !empty(trim($agent->primary_phone))
                && !empty(trim($agent->city));

            Log::info('GoogleAuth: existing Agent found', ['id' => $agent->id]);
        }

        $token = $agent->createToken('google-mobile')->plainTextToken;

        return response()->json([
            'message'          => 'Login successful',
            'role'             => 'agent',
            'token'            => $token,
            // Flutter mirrors the shape of AgentLoginSubmitted response
            'data'             => [
                'user'  => $agent,
                'token' => $token,
            ],
            // Flutter reads these to decide whether to push profile completion
            'profile_complete' => $profileComplete,
            'missing_fields'   => $profileComplete
                ? []
                : ['primary_phone', 'city'],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OFFICE (RealEstateOffice)
    // Table: real_estate_offices (HasUuids)
    // Office CANNOT self-register via Google — must already exist.
    // Google sign-in = login only for existing office accounts.
    //
    // Email column is `email_address` (not `email`) — checked from model.
    // google_id NOT in fillable — added via migration.
    // ─────────────────────────────────────────────────────────────────────────
    private function handleOffice(string $email, string $firebaseUid)
    {
        // Note: RealEstateOffice uses email_address not email
        $office = RealEstateOffice::where('google_id', $firebaseUid)->first()
            ?? RealEstateOffice::where('email_address', $email)->first();

        if (!$office) {
            return response()->json([
                'message'                => 'No office account found for this Google account. '
                    . 'Please register your office first, then sign in with Google.',
                'requires_registration'  => true,
                'role'                   => 'office',
            ], 404);
        }

        // First time Google login → link the account
        if (!$office->google_id) {
            $office->update(['google_id' => $firebaseUid]);
        }

        $token = $office->createToken('google-mobile')->plainTextToken;

        Log::info('GoogleAuth: Office login', [
            'id' => $office->id,
            'email' => $email,
        ]);

        // Mirror the shape of OfficeLoginSuccess so Flutter's _handleState works
        return response()->json([
            'message' => 'Login successful',
            'role'    => 'office',
            'token'   => $token,
            'office'  => $office,
        ]);
    }
}
