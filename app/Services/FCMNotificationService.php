<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FCMNotification;

class FCMNotificationService
{
    private $messaging;

    public function __construct()
    {
        try {
            $serviceAccountPath = config('firebase.service_account_path');

            if (!$serviceAccountPath || !file_exists($serviceAccountPath)) {
                Log::error('FCM: Service account file not found', [
                    'path' => $serviceAccountPath
                ]);
                $this->messaging = null;
                return;
            }

            $factory = (new Factory)->withServiceAccount($serviceAccountPath);
            $this->messaging = $factory->createMessaging();

            Log::info('FCM: Messaging service initialized successfully');
        } catch (\Exception $e) {
            Log::error('FCM: Failed to initialize messaging service', [
                'error' => $e->getMessage()
            ]);
            $this->messaging = null;
        }
    }

    /**
     * Send notification to a single user
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): array
    {
        if (!$this->messaging) {
            Log::warning('FCM: Messaging service not available');
            return [
                'success' => false,
                'error' => 'FCM service not initialized',
                'sent_count' => 0
            ];
        }

        $fcmTokens = $user->getFCMTokens();

        if (empty($fcmTokens)) {
            Log::info('FCM: No tokens found for user', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return [
                'success' => false,
                'error' => 'No FCM tokens found',
                'sent_count' => 0
            ];
        }

        Log::info('FCM: Sending notification to user', [
            'user_id' => $user->id,
            'email' => $user->email,
            'tokens_count' => count($fcmTokens),
            'title' => $title
        ]);

        $results = [];
        $successCount = 0;

        foreach ($fcmTokens as $token) {
            $result = $this->sendToToken($token, $title, $body, $data);
            $results[] = $result;

            if ($result['success']) {
                $successCount++;
            }
        }

        return [
            'success' => $successCount > 0,
            'sent_count' => $successCount,
            'total_tokens' => count($fcmTokens),
            'results' => $results
        ];
    }

    /**
     * Send notification to a single device token using V1 API
     */
    public function sendToToken(string $deviceToken, string $title, string $body, array $data = []): array
    {
        if (!$this->messaging) {
            return [
                'success' => false,
                'error' => 'FCM service not initialized'
            ];
        }

        try {
            // Create FCM notification
            $notification = FCMNotification::create($title, $body);

            // Build message
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification)
                ->withData(array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]));

            Log::info('FCM: Sending request (V1 API)', [
                'token_preview' => substr($deviceToken, 0, 20) . '...',
                'title' => $title
            ]);

            // Send message
            $result = $this->messaging->send($message);

            Log::info('FCM: Notification sent successfully (V1 API)', [
                'token_preview' => substr($deviceToken, 0, 20) . '...',
                'message_id' => $result
            ]);

            return [
                'success' => true,
                'message_id' => $result
            ];
        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            Log::warning('FCM: Token not found or expired', [
                'token_preview' => substr($deviceToken, 0, 20) . '...',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Token not found or expired'
            ];
        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            Log::error('FCM: Messaging exception', [
                'token_preview' => substr($deviceToken, 0, 20) . '...',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('FCM: Exception during send', [
                'token_preview' => substr($deviceToken, 0, 20) . '...',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendToMultipleUsers(array $users, string $title, string $body, array $data = []): array
    {
        $totalSent = 0;
        $totalFailed = 0;

        foreach ($users as $user) {
            $result = $this->sendToUser($user, $title, $body, $data);

            if ($result['success']) {
                $totalSent += $result['sent_count'];
            } else {
                $totalFailed++;
            }
        }

        return [
            'success' => $totalSent > 0,
            'sent_count' => $totalSent,
            'failed_count' => $totalFailed,
            'total_users' => count($users)
        ];
    }

    /**
     * Create database notification and send FCM push
     */
    public function createAndSendNotification(
        User $user,
        string $title,
        string $message,
        string $type,
        array $data = []
    ): array {
        // Create database notification
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
            'read_at' => null,
        ]);

        Log::info('Notification created in database', [
            'notification_id' => $notification->id,
            'user_id' => $user->id,
            'type' => $type
        ]);

        // Send FCM push notification
        $fcmResult = $this->sendToUser($user, $title, $message, array_merge($data, [
            'notification_id' => $notification->id,
            'type' => $type
        ]));

        return [
            'success' => true,
            'notification' => $notification,
            'fcm_result' => $fcmResult
        ];
    }
}
