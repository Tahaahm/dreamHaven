<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Agent;
use App\Models\RealEstateOffice;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Collection;
use Illuminate\Http\Client\Pool;

class FirebaseService
{
    private $projectId;
    private $serviceAccountPath;
    private $baseUrl;
    private $batchSize = 100; // FCM batch limit is 500, we use 100 for safety

    public function __construct()
    {
        $this->projectId = config('firebase.project_id');
        $this->serviceAccountPath = config('firebase.service_account_path');
        $this->baseUrl = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
    }

    /**
     * Get OAuth 2.0 access token for FCM API
     */
    private function getAccessToken(): string
    {
        $cacheKey = 'firebase_access_token';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);

            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                $serviceAccount
            );

            $token = $credentials->fetchAuthToken();
            Cache::put($cacheKey, $token['access_token'], 50 * 60);

            return $token['access_token'];
        } catch (\Exception $e) {
            Log::error('Failed to get Firebase access token', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Send FCM notification to a single device token
     */
    public function sendToToken(string $fcmToken, array $notification, array $data = [])
    {
        try {
            $payload = $this->buildNotificationPayload($fcmToken, $notification, $data);
            $accessToken = $this->getAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl, $payload);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('FCM notification sent successfully', [
                    'token' => substr($fcmToken, 0, 20) . '...',
                    'message_name' => $result['name'] ?? null
                ]);
                return $result;
            } else {
                Log::error('FCM notification failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'token' => substr($fcmToken, 0, 20) . '...'
                ]);

                $this->handleTokenErrors($response, $fcmToken);
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
     * Send FCM notifications to multiple tokens using concurrent requests
     */
    public function sendToMultipleTokensBatch(array $fcmTokens, array $notification, array $data = [])
    {
        if (empty($fcmTokens)) {
            return [];
        }

        $chunks = array_chunk($fcmTokens, $this->batchSize);
        $allResults = [];

        foreach ($chunks as $tokenChunk) {
            $results = $this->sendConcurrentRequests($tokenChunk, $notification, $data);
            $allResults = array_merge($allResults, $results);
        }

        return $allResults;
    }

    /**
     * Send concurrent HTTP requests for better performance
     */
    private function sendConcurrentRequests(array $tokens, array $notification, array $data = [])
    {
        try {
            $accessToken = $this->getAccessToken();
            $results = [];

            $responses = Http::pool(function (Pool $pool) use ($tokens, $notification, $data, $accessToken) {
                $requests = [];
                foreach ($tokens as $token) {
                    $payload = $this->buildNotificationPayload($token, $notification, $data);
                    $requests[] = $pool->withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                    ])->timeout(30)->post($this->baseUrl, $payload);
                }
                return $requests;
            });

            foreach ($responses as $index => $response) {
                $token = $tokens[$index];

                if ($response->successful()) {
                    $result = $response->json();
                    $results[] = [
                        'token' => $token,
                        'success' => true,
                        'result' => $result
                    ];

                    Log::info('FCM batch notification sent', [
                        'token' => substr($token, 0, 20) . '...',
                        'message_name' => $result['name'] ?? null
                    ]);
                } else {
                    $results[] = [
                        'token' => $token,
                        'success' => false,
                        'error' => $response->body()
                    ];

                    Log::error('FCM batch notification failed', [
                        'token' => substr($token, 0, 20) . '...',
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);

                    $this->handleTokenErrors($response, $token);
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('FCM batch notification exception', [
                'error' => $e->getMessage(),
                'token_count' => count($tokens)
            ]);
            return [];
        }
    }

    /**
     * Build notification payload
     */
    private function buildNotificationPayload(string $fcmToken, array $notification, array $data = [])
    {
        return [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $notification['title'] ?? '',
                    'body' => $notification['message'] ?? $notification['body'] ?? '',
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'notification_id' => (string)($data['id'] ?? ''),
                    'type' => $data['type'] ?? 'general'
                ]),
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'default_sound' => true,
                        'channel_id' => 'default'
                    ]
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10'
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $notification['title'] ?? '',
                                'body' => $notification['message'] ?? $notification['body'] ?? '',
                            ],
                            'sound' => 'default',
                            'badge' => 1
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Send FCM notification to a user (all their devices) - Async version
     */
    public function sendToUser(User $user, array $notification, array $data = [])
    {
        $fcmTokens = $user->getFCMTokens();

        if (empty($fcmTokens)) {
            Log::info('No FCM tokens found for user', ['user_id' => $user->id]);
            return false;
        }

        return $this->sendToMultipleTokensBatch($fcmTokens, $notification, $data);
    }

    /**
     * Send FCM notification to an agent (all their devices) - Async version
     */
    public function sendToAgent(Agent $agent, array $notification, array $data = [])
    {
        $fcmTokens = $agent->getFCMTokens();

        if (empty($fcmTokens)) {
            Log::info('No FCM tokens found for agent', ['agent_id' => $agent->id]);
            return false;
        }

        return $this->sendToMultipleTokensBatch($fcmTokens, $notification, $data);
    }

    /**
     * Send FCM notification to multiple users in batch
     */
    public function sendToMultipleUsers(Collection $users, array $notification, array $data = [])
    {
        $allTokens = [];

        foreach ($users as $user) {
            $tokens = $user->getFCMTokens();
            if (!empty($tokens)) {
                foreach ($tokens as $token) {
                    $allTokens[] = [
                        'token' => $token,
                        'user_id' => $user->id,
                        'user_type' => get_class($user)
                    ];
                }
            }
        }

        if (empty($allTokens)) {
            Log::info('No FCM tokens found for batch users', ['user_count' => $users->count()]);
            return [];
        }

        $tokens = array_column($allTokens, 'token');
        return $this->sendToMultipleTokensBatch($tokens, $notification, $data);
    }

    /**
     * Send topic-based notification (for broadcasting)
     */
    public function sendToTopic(string $topic, array $notification, array $data = [])
    {
        try {
            $payload = [
                'message' => [
                    'topic' => $topic,
                    'notification' => [
                        'title' => $notification['title'] ?? '',
                        'body' => $notification['message'] ?? $notification['body'] ?? '',
                    ],
                    'data' => array_merge($data, [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'type' => $data['type'] ?? 'general'
                    ]),
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'default_sound' => true,
                            'channel_id' => 'default'
                        ]
                    ],
                    'apns' => [
                        'headers' => [
                            'apns-priority' => '10'
                        ],
                        'payload' => [
                            'aps' => [
                                'alert' => [
                                    'title' => $notification['title'] ?? '',
                                    'body' => $notification['message'] ?? $notification['body'] ?? '',
                                ],
                                'sound' => 'default',
                                'badge' => 1
                            ]
                        ]
                    ]
                ]
            ];

            $accessToken = $this->getAccessToken();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl, $payload);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('FCM topic notification sent', [
                    'topic' => $topic,
                    'message_name' => $result['name'] ?? null
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
     * Handle token errors and cleanup invalid tokens
     */
    private function handleTokenErrors($response, string $token)
    {
        $responseBody = $response->json();

        if (isset($responseBody['error']['details'])) {
            foreach ($responseBody['error']['details'] as $detail) {
                if (isset($detail['errorCode'])) {
                    $errorCode = $detail['errorCode'];

                    if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                        Log::info('Removing invalid FCM token', [
                            'token' => substr($token, 0, 20) . '...',
                            'error' => $errorCode
                        ]);
                        $this->removeInvalidToken($token);
                    }
                }
            }
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

            // Remove from agents
            $agents = Agent::whereJsonContains('device_tokens', [['fcm_token' => $invalidToken]])->get();
            foreach ($agents as $agent) {
                $deviceTokens = $agent->device_tokens ?? [];
                $filteredTokens = array_filter($deviceTokens, function ($device) use ($invalidToken) {
                    return ($device['fcm_token'] ?? '') !== $invalidToken;
                });
                $agent->update(['device_tokens' => array_values($filteredTokens)]);
            }

            // Remove from offices
            $offices = RealEstateOffice::whereJsonContains('device_tokens', [['fcm_token' => $invalidToken]])->get();
            foreach ($offices as $office) {
                $deviceTokens = $office->device_tokens ?? [];
                $filteredTokens = array_filter($deviceTokens, function ($device) use ($invalidToken) {
                    return ($device['fcm_token'] ?? '') !== $invalidToken;
                });
                $office->update(['device_tokens' => array_values($filteredTokens)]);
            }
        } catch (\Exception $e) {
            Log::error('Error removing invalid FCM token', [
                'token' => substr($invalidToken, 0, 20) . '...',
                'error' => $e->getMessage()
            ]);
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