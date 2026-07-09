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
            return ($token['device_name'] ?? $token['device'] ?? '') !== $deviceName;
        });

        // Add new token
        $deviceTokens[] = [
            'device_name' => $deviceName,
            'fcm_token'   => $tokenId,
            'added_at'    => now()->toISOString(),
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
                'user_id'     => $user->id,
                'device_name' => $deviceName,
                'fcm_token'   => substr($fcmToken, 0, 20) . '...',
            ]);

            $deviceTokens = $user->device_tokens ?? [];

            if (is_string($deviceTokens)) {
                $deviceTokens = json_decode($deviceTokens, true) ?? [];
            }

            $deviceExists = false;

            foreach ($deviceTokens as $index => $deviceData) {
                // Match on device_name (new schema) or device (old schema)
                $existingName = $deviceData['device_name'] ?? $deviceData['device'] ?? null;
                if ($existingName === $deviceName) {
                    $deviceTokens[$index] = [
                        'device_name' => $deviceName,
                        'fcm_token'   => $fcmToken,
                        'updated_at'  => now()->toISOString(),
                    ];
                    $deviceExists = true;
                    break;
                }
            }

            if (!$deviceExists) {
                $deviceTokens[] = [
                    'device_name' => $deviceName,
                    'fcm_token'   => $fcmToken,
                    'added_at'    => now()->toISOString(),
                ];
            }

            $user->update(['device_tokens' => array_values($deviceTokens)]);

            Log::info('Device token updated successfully', [
                'user_id'     => $user->id,
                'device_name' => $deviceName,
                'action'      => $deviceExists ? 'updated' : 'created',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user device token', [
                'user_id'     => $user->id,
                'device_name' => $deviceName,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove device token
     */
    public function removeDeviceToken(User $user, string $deviceName): array
    {
        $deviceTokens = $user->device_tokens ?? [];

        if (is_string($deviceTokens)) {
            $deviceTokens = json_decode($deviceTokens, true) ?? [];
        }

        $deviceTokens = array_filter($deviceTokens, function ($token) use ($deviceName) {
            // Handle both old schema (device) and new schema (device_name)
            $name = $token['device_name'] ?? $token['device'] ?? '';
            return $name !== $deviceName;
        });

        $user->update(['device_tokens' => array_values($deviceTokens)]);

        return ['device_count' => count($deviceTokens)];
    }

    /**
     * Get device tokens (safe for API response)
     * Handles both old schema {device, tokenId, last_used}
     * and new schema {device_name, fcm_token, added_at/updated_at}
     */
    public function getDeviceTokens(User $user): array
    {
        $deviceTokens = $user->device_tokens ?? [];

        // Guard against stored JSON string
        if (is_string($deviceTokens)) {
            $deviceTokens = json_decode($deviceTokens, true) ?? [];
        }

        $safeTokens = array_map(function ($token) {
            // Support both old (device, tokenId) and new (device_name, fcm_token) schema
            $deviceName = $token['device_name'] ?? $token['device']   ?? 'Unknown Device';
            $fcmToken   = $token['fcm_token']   ?? $token['tokenId']  ?? null;

            // Detect whether this entry was updated or freshly added
            $wasUpdated = isset($token['updated_at']) || isset($token['last_updated']);

            // Best available timestamp
            $timestamp  = $token['updated_at']   ??
                $token['last_updated']  ??
                $token['last_used']     ??
                $token['added_at']      ??
                $token['created_at']    ?? null;

            return [
                'device_name' => $deviceName,
                'fcm_token'   => $fcmToken,
                'added_at'    => $wasUpdated ? null : ($token['added_at'] ?? $token['created_at'] ?? $timestamp),
                'updated_at'  => $wasUpdated ? $timestamp : null,
            ];
        }, array_values($deviceTokens));

        return [
            'devices'       => $safeTokens,
            'total_devices' => count($safeTokens),
        ];
    }
}
