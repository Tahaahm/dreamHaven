<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserDeviceService
{
    /**
     * Update device token for user
     */
    public function updateDeviceToken(User $user, string $deviceName, string $tokenId): array
    {
        $deviceTokens = $user->device_tokens ?? [];

        // Remove existing token for this device if exists
        $deviceTokens = array_filter($deviceTokens, function ($token) use ($deviceName) {
            return ($token['device'] ?? '') !== $deviceName;
        });

        // Add new token
        $deviceTokens[] = [
            'device' => $deviceName,
            'tokenId' => $tokenId,
            'created_at' => now()->toISOString(),
            'last_used' => now()->toISOString()
        ];

        // Keep only last 5 tokens per user
        if (count($deviceTokens) > 5) {
            $deviceTokens = array_slice($deviceTokens, -5);
        }

        $user->update(['device_tokens' => array_values($deviceTokens)]);

        return ['device_count' => count($deviceTokens)];
    }

    /**
     * Update device token for user during login/registration
     */
    public function updateDeviceTokenForUser(User $user, string $deviceName, string $fcmToken): void
    {
        try {
            Log::info('Starting device token update', [
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'fcm_token' => substr($fcmToken, 0, 20) . '...',
            ]);

            $deviceTokens = $user->device_tokens ?? [];
            $deviceExists = false;

            foreach ($deviceTokens as $index => $deviceData) {
                if (isset($deviceData['device_name']) && $deviceData['device_name'] === $deviceName) {
                    $deviceTokens[$index] = [
                        'device_name' => $deviceName,
                        'fcm_token' => $fcmToken,
                        'last_updated' => now()->format('Y-m-d H:i:s'),
                        'last_login' => now()->format('Y-m-d H:i:s')
                    ];
                    $deviceExists = true;
                    break;
                }
            }

            if (!$deviceExists) {
                $deviceTokens[] = [
                    'device_name' => $deviceName,
                    'fcm_token' => $fcmToken,
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'last_updated' => now()->format('Y-m-d H:i:s'),
                    'last_login' => now()->format('Y-m-d H:i:s')
                ];
            }

            $user->update(['device_tokens' => $deviceTokens]);

            Log::info('Device token updated successfully', [
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'action' => $deviceExists ? 'updated' : 'created',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user device token', [
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove device token
     */
    public function removeDeviceToken(User $user, string $deviceName): array
    {
        $deviceTokens = $user->device_tokens ?? [];

        $deviceTokens = array_filter($deviceTokens, function ($token) use ($deviceName) {
            return ($token['device'] ?? '') !== $deviceName;
        });

        $user->update(['device_tokens' => array_values($deviceTokens)]);

        return ['device_count' => count($deviceTokens)];
    }

    /**
     * Get device tokens (safe for API response)
     */
    public function getDeviceTokens(User $user): array
    {
        $deviceTokens = $user->device_tokens ?? [];

        $safeTokens = array_map(function ($token) {
            return [
                'device' => $token['device'] ?? 'Unknown Device',
                'created_at' => $token['created_at'] ?? null,
                'last_used' => $token['last_used'] ?? null
            ];
        }, $deviceTokens);

        return [
            'devices' => $safeTokens,
            'total_devices' => count($safeTokens)
        ];
    }
}
