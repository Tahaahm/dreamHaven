<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class UserNotificationService
{
    /**
     * Get user notifications with filters and pagination
     */
    public function getUserNotifications(User $user, array $filters = []): array
    {
        $query = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });

        // Apply filters
        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        $limit = $filters['limit'] ?? 20;
        $offset = $filters['offset'] ?? 0;

        $notifications = $query
            ->orderBy('sent_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $transformedNotifications = $notifications->map(function ($notification) {
            return $this->transformNotificationData($notification);
        });

        $totalCount = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->count();

        $unreadCount = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->count();

        return [
            'notifications' => $transformedNotifications,
            'total_count' => $totalCount,
            'unread_count' => $unreadCount,
            'current_count' => $notifications->count(),
        ];
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = DB::table('notifications')
            ->where('id', $notificationId)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification || $notification->is_read) {
            return false;
        }

        $updated = DB::table('notifications')
            ->where('id', $notificationId)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now()
            ]);

        return $updated > 0;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(User $user): int
    {
        return DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * Delete notification
     */
    public function deleteNotification(User $user, string $notificationId): bool
    {
        $deleted = DB::table('notifications')
            ->where('id', $notificationId)
            ->where('user_id', $user->id)
            ->delete();

        return $deleted > 0;
    }

    /**
     * Clear all notifications
     */
    public function clearAllNotifications(User $user): int
    {
        return DB::table('notifications')
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * Create new notification
     */
    public function createNotification(array $data): array
    {
        $notificationData = [
            'id' => (string) Str::uuid(),
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'],
            'priority' => $data['priority'],
            'data' => isset($data['data']) ? json_encode($data['data']) : null,
            'action_url' => $data['action_url'] ?? null,
            'action_text' => $data['action_text'] ?? null,
            'is_read' => $data['is_read'] ?? false,
            'sent_at' => now(),
            'expires_at' => $data['expires_at'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('notifications')->insert($notificationData);

        return $notificationData;
    }

    /**
     * Create bulk notifications
     */
    public function createBulkNotifications(array $notifications): array
    {
        $insertData = [];
        foreach ($notifications as $notification) {
            $insertData[] = [
                'id' => (string) Str::uuid(),
                'user_id' => $notification['user_id'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'type' => $notification['type'],
                'priority' => $notification['priority'],
                'data' => isset($notification['data']) ? json_encode($notification['data']) : null,
                'action_url' => $notification['action_url'] ?? null,
                'action_text' => $notification['action_text'] ?? null,
                'is_read' => $notification['is_read'] ?? false,
                'sent_at' => now(),
                'expires_at' => $notification['expires_at'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('notifications')->insert($insertData);

        return [
            'created_count' => count($insertData),
            'notifications' => $insertData
        ];
    }

    private function transformNotificationData($notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'priority' => $notification->priority,
            'data' => $notification->data ? json_decode($notification->data, true) : null,
            'action_url' => $notification->action_url,
            'action_text' => $notification->action_text,
            'is_read' => (bool) $notification->is_read,
            'read_at' => $notification->read_at,
            'sent_at' => $notification->sent_at,
            'expires_at' => $notification->expires_at,
            'created_at' => $notification->created_at,
            'updated_at' => $notification->updated_at,
        ];
    }
}
