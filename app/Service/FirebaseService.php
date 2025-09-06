<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Agent;
use App\Models\RealEstateOffice;

class FirebaseService
{
    private $serverKey;
    private $baseUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('firebase.server_key');
    }

    /**
     * Send FCM notification to a single device token
     */
    public function sendToToken(string $fcmToken, array $notification, array $data = [])
    {
        try {
            $payload = [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $notification['title'] ?? '',
                    'body' => $notification['message'] ?? $notification['body'] ?? '',
                    'sound' => 'default',
                    'badge' => 1,
                    'priority' => 'high',
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'notification_id' => $data['id'] ?? null,
                    'type' => $data['type'] ?? 'general'
                ]),
                'priority' => 'high'
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, $payload);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('FCM notification sent successfully', [
                    'token' => substr($fcmToken, 0, 20) . '...',
                    'message_id' => $result['multicast_id'] ?? null,
                    'success' => $result['success'] ?? 0,
                    'failure' => $result['failure'] ?? 0
                ]);
                return $result;
            } else {
                Log::error('FCM notification failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'token' => substr($fcmToken, 0, 20) . '...'
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('FCM notification exception', [
                'error' => $e->getMessage(),
                'token' => substr($fcmToken, 0, 20) . '...'
            ]);
            return false;
        }
    }

    /**
     * Send FCM notification to multiple device tokens
     */
    public function sendToMultipleTokens(array $fcmTokens, array $notification, array $data = [])
    {
        // Firebase allows max 1000 tokens per request
        $chunks = array_chunk($fcmTokens, 1000);
        $results = [];

        foreach ($chunks as $tokenChunk) {
            try {
                $payload = [
                    'registration_ids' => $tokenChunk,
                    'notification' => [
                        'title' => $notification['title'] ?? '',
                        'body' => $notification['message'] ?? $notification['body'] ?? '',
                        'sound' => 'default',
                        'badge' => 1,
                        'priority' => 'high',
                    ],
                    'data' => array_merge($data, [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'notification_id' => $data['id'] ?? null,
                        'type' => $data['type'] ?? 'general'
                    ]),
                    'priority' => 'high'
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $this->serverKey,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl, $payload);

                if ($response->successful()) {
                    $result = $response->json();
                    $results[] = $result;

                    Log::info('FCM batch notification sent', [
                        'tokens_count' => count($tokenChunk),
                        'success' => $result['success'] ?? 0,
                        'failure' => $result['failure'] ?? 0
                    ]);

                    // Handle failed tokens (expired/invalid)
                    if (isset($result['results'])) {
                        $this->handleFailedTokens($tokenChunk, $result['results']);
                    }
                } else {
                    Log::error('FCM batch notification failed', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                        'tokens_count' => count($tokenChunk)
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('FCM batch notification exception', [
                    'error' => $e->getMessage(),
                    'tokens_count' => count($tokenChunk)
                ]);
            }
        }

        return $results;
    }

    /**
     * Send FCM notification to a user (all their devices)
     */
    public function sendToUser(User $user, array $notification, array $data = [])
    {
        $fcmTokens = $user->getFCMTokens();

        if (empty($fcmTokens)) {
            Log::info('No FCM tokens found for user', ['user_id' => $user->id]);
            return false;
        }

        return $this->sendToMultipleTokens($fcmTokens, $notification, $data);
    }

    /**
     * Send FCM notification to an agent (all their devices)
     */
    public function sendToAgent(Agent $agent, array $notification, array $data = [])
    {
        // Assuming Agent model has similar device_tokens structure
        $fcmTokens = $agent->getFCMTokens();

        if (empty($fcmTokens)) {
            Log::info('No FCM tokens found for agent', ['agent_id' => $agent->id]);
            return false;
        }

        return $this->sendToMultipleTokens($fcmTokens, $notification, $data);
    }

    /**
     * Send FCM notification to a real estate office (all their devices)
     */
    public function sendToOffice(RealEstateOffice $office, array $notification, array $data = [])
    {
        // Assuming RealEstateOffice model has similar device_tokens structure
        $fcmTokens = $office->getFCMTokens();

        if (empty($fcmTokens)) {
            Log::info('No FCM tokens found for office', ['office_id' => $office->id]);
            return false;
        }

        return $this->sendToMultipleTokens($fcmTokens, $notification, $data);
    }

    /**
     * Handle failed/invalid FCM tokens by removing them from user records
     */
    private function handleFailedTokens(array $tokens, array $results)
    {
        try {
            foreach ($results as $index => $result) {
                if (isset($result['error'])) {
                    $errorType = $result['error'];
                    $failedToken = $tokens[$index] ?? null;

                    if ($failedToken && in_array($errorType, ['NotRegistered', 'InvalidRegistration'])) {
                        Log::info('Removing invalid FCM token', [
                            'token' => substr($failedToken, 0, 20) . '...',
                            'error' => $errorType
                        ]);

                        // Remove invalid token from all users
                        $this->removeInvalidToken($failedToken);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error handling failed FCM tokens', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove invalid FCM token from user records
     */
    private function removeInvalidToken(string $invalidToken)
    {
        try {
            // Remove from users
            $users = User::whereJsonContains('device_tokens', [['fcm_token' => $invalidToken]])->get();
            foreach ($users as $user) {
                $deviceTokens = $user->device_tokens ?? [];
                $filteredTokens = array_filter($deviceTokens, function ($device) use ($invalidToken) {
                    return ($device['fcm_token'] ?? '') !== $invalidToken;
                });
                $user->update(['device_tokens' => array_values($filteredTokens)]);
            }

            // TODO: Remove from agents and offices if they have similar structure

        } catch (\Exception $e) {
            Log::error('Error removing invalid FCM token', [
                'token' => substr($invalidToken, 0, 20) . '...',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send topic-based notification (for broadcasting)
     */
    public function sendToTopic(string $topic, array $notification, array $data = [])
    {
        try {
            $payload = [
                'to' => '/topics/' . $topic,
                'notification' => [
                    'title' => $notification['title'] ?? '',
                    'body' => $notification['message'] ?? $notification['body'] ?? '',
                    'sound' => 'default',
                    'badge' => 1,
                    'priority' => 'high',
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'type' => $data['type'] ?? 'general'
                ]),
                'priority' => 'high'
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, $payload);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('FCM topic notification sent', [
                    'topic' => $topic,
                    'message_id' => $result['message_id'] ?? null
                ]);
                return $result;
            } else {
                Log::error('FCM topic notification failed', [
                    'topic' => $topic,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('FCM topic notification exception', [
                'topic' => $topic,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Create notification payload optimized for different notification types
     */
    public function createNotificationPayload(string $type, array $data): array
    {
        $basePayload = [
            'title' => $data['title'] ?? 'Notification',
            'message' => $data['message'] ?? '',
        ];

        switch ($type) {
            case 'property':
                return array_merge($basePayload, [
                    'icon' => 'property_icon',
                    'color' => '#2196F3',
                    'category' => 'property'
                ]);

            case 'appointment':
                return array_merge($basePayload, [
                    'icon' => 'appointment_icon',
                    'color' => '#4CAF50',
                    'category' => 'appointment'
                ]);

            case 'system':
                return array_merge($basePayload, [
                    'icon' => 'system_icon',
                    'color' => '#FF9800',
                    'category' => 'system'
                ]);

            case 'promotion':
                return array_merge($basePayload, [
                    'icon' => 'promotion_icon',
                    'color' => '#E91E63',
                    'category' => 'promotion'
                ]);

            default:
                return $basePayload;
        }
    }
}