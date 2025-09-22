<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserAuthService
{
    protected UserDeviceService $deviceService;
    protected UserProfileService $profileService;

    public function __construct(UserDeviceService $deviceService, UserProfileService $profileService)
    {
        $this->deviceService = $deviceService;
        $this->profileService = $profileService;
    }

    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        DB::beginTransaction();

        try {
            $userData = $this->prepareUserData($data);

            DB::table('users')->insert($userData);
            $user = User::find($userData['id']);

            // Generate auth token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Send welcome notification
            $this->sendWelcomeNotification($user->id);

            DB::commit();

            return [
                'user' => $this->profileService->transformUserData($user),
                'token' => $token
            ];
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Authenticate user login
     */
    public function login(array $data): ?array
    {
        $loginField = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($loginField, $data['login'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return null;
        }

        $deviceName = $data['device_name'] ?? 'Unknown Device';
        $deviceToken = $data['device_token'] ?? null;

        // Handle device token management
        if ($deviceToken && $deviceName) {
            $this->deviceService->updateDeviceTokenForUser($user, $deviceName, $deviceToken);
        }

        // Create auth token
        $token = $user->createToken('auth-token - ' . $deviceName)->plainTextToken;

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Send login notification
        $this->sendLoginNotification($user->id, $deviceName);

        return [
            'user' => $this->profileService->transformUserData($user->fresh()),
            'token' => $token
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user, bool $logoutAll = false): void
    {
        if ($logoutAll) {
            $user->tokens()->delete();
        } else {
            $user->currentAccessToken()->delete();
        }
    }

    /**
     * Refresh user token
     */
    public function refreshToken(User $user, string $deviceName = 'Unknown Device'): array
    {
        // Revoke current token
        $user->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('auth-token - ' . $deviceName)->plainTextToken;

        return [
            'user' => $this->profileService->transformUserData($user),
            'token' => $token
        ];
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->update([
            'password' => Hash::make($newPassword),
            'updated_at' => now()
        ]);

        return true;
    }

    // Private helper methods

    /**
     * Prepare user data for registration
     */
    private function prepareUserData(array $data): array
    {
        $userData = [
            'id' => (string) Str::uuid(),
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'place' => $data['place'] ?? null,
            'lat' => $data['lat'] ?? null,
            'lng' => $data['lng'] ?? null,
            'about_me' => $data['about_me'] ?? null,
            'photo_image' => $data['photo_image'] ?? null,
            'language' => $data['language'] ?? 'en',
            'search_preferences' => json_encode($data['search_preferences'] ?? $this->getDefaultSearchPreferences()),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Handle device tokens during registration
        $deviceTokens = [];
        if (isset($data['device_token']) && isset($data['device_name'])) {
            $deviceTokens[] = [
                'device' => $data['device_name'],
                'tokenId' => $data['device_token'],
                'created_at' => now()->toISOString(),
                'last_used' => now()->toISOString()
            ];
        }
        $userData['device_tokens'] = json_encode($deviceTokens);

        return $userData;
    }

    /**
     * Get default search preferences
     */
    private function getDefaultSearchPreferences(): array
    {
        return [
            'filters' => [
                'price_enabled' => false,
                'min_price' => null,
                'max_price' => null,
                'location_enabled' => false,
                'location_radius' => 10.0,
                'property_types' => [],
                'min_bedrooms' => null,
                'max_bedrooms' => null,
            ],
            'sorting' => [
                'price_enabled' => false,
                'price_order' => 'low_to_high',
                'popularity_enabled' => false,
                'date_enabled' => false,
                'date_order' => 'newest',
                'distance_enabled' => false,
            ],
            'behavior' => [
                'enable_notifications' => true,
                'save_search_history' => true,
                'auto_suggestions' => true,
                'recent_searches' => true,
                'max_history_items' => 50,
            ]
        ];
    }

    /**
     * Send welcome notification
     */
    private function sendWelcomeNotification(string $userId): void
    {
        try {
            if (class_exists('App\Http\Controllers\NotificationController')) {
                app(\App\Http\Controllers\NotificationController::class)->sendWelcomeNotification($userId);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send welcome notification', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send login notification
     */
    private function sendLoginNotification(string $userId, string $deviceName): void
    {
        try {
            if (class_exists('App\Http\Controllers\NotificationController')) {
                app(\App\Http\Controllers\NotificationController::class)->sendLoginNotification($userId, $deviceName);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send login notification', [
                'user_id' => $userId,
                'device' => $deviceName,
                'error' => $e->getMessage()
            ]);
        }
    }
}
