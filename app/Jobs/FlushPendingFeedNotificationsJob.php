<?php

namespace App\Jobs;

use App\Models\PendingNotification;
use App\Models\User;
use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Services\FirebaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * FlushPendingFeedNotificationsJob
 *
 * Runs every 3 minutes via Laravel Scheduler.
 *
 * For each pending_notification row that is ready (is_flushed=false, cooldown passed):
 *   1. Build batched title  → "Ahmad and 12 others liked your post"
 *   2. Save 1 row to notifications table  ← shows in the in-app bell
 *   3. Send FCM to post author            ← phone push notification
 *   4. Set cooldown_until = now() + 15min
 *   5. Store notification_id UUID on the pending row for traceability
 */
class FlushPendingFeedNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const COOLDOWN_MINUTES = 15;

    // Milestone counts — always fire FCM at these exact numbers
    const LIKE_MILESTONES    = [1, 10, 25, 50, 100];
    const COMMENT_MILESTONES = [1, 5, 10, 25, 50];
    const SAVE_MILESTONES    = [1, 10, 50, 100];

    public function handle(): void
    {
        $pendingRows = PendingNotification::query()
            ->readyToFlush()
            ->orderBy('last_updated_at', 'asc')
            ->get();

        if ($pendingRows->isEmpty()) {
            return;
        }

        Log::info("FlushPendingFeedNotificationsJob: flushing {$pendingRows->count()} pending rows");

        $firebaseService = new FirebaseService();

        foreach ($pendingRows as $pending) {
            /** @var \App\Models\PendingNotification $pending */
            $this->flushOne($pending, $firebaseService);
        }
    }

    // ── Per-row flush ──────────────────────────────────────────────────────

    private function flushOne(PendingNotification $pending, FirebaseService $firebaseService): void
    {
        try {
            // If already flushed before and this isn't a milestone count, skip
            if ($pending->flushed_at !== null && !$this->isMilestone($pending)) {
                $this->resetForNextBatch($pending);
                return;
            }

            // ── Load post author ───────────────────────────────────────────
            $author = $this->loadAuthor($pending->post_author_type, $pending->post_author_id);
            if (!$author) {
                Log::warning("FlushPendingFeedNotificationsJob: author not found", [
                    'type' => $pending->post_author_type,
                    'id'   => $pending->post_author_id,
                ]);
                $pending->delete();
                return;
            }

            // ── Resolve author language for FCM title ──────────────────────
            $authorLang = strtolower(trim($author->language ?? 'en'));
            if (!in_array($authorLang, ['en', 'ar', 'ku'])) {
                $authorLang = 'en';
            }

            // ── Build multilingual titles + body ───────────────────────────
            $titles = $pending->buildTitleMultilingual();
            $title  = $titles[$authorLang] ?? $titles['en'];
            $body   = $pending->buildBody();

            // ── 1. Save to notifications table (in-app bell) ───────────────
            $notificationId = $this->saveToNotificationsTable(
                pending: $pending,
                author: $author,
                title: $title,
                body: $body,
                titles: $titles,
            );

            // ── 2. Send FCM push notification ──────────────────────────────
            $this->sendFcm(
                pending: $pending,
                author: $author,
                title: $title,
                body: $body,
                titles: $titles,
                notificationId: $notificationId,
                firebaseService: $firebaseService,
            );

            // ── 3. Mark flushed + apply cooldown ───────────────────────────
            $pending->update([
                'is_flushed'      => true,
                'flushed_at'      => now(),
                'cooldown_until'  => now()->addMinutes(self::COOLDOWN_MINUTES),
                'notification_id' => $notificationId,
            ]);

            Log::info("Flushed post {$pending->post_id} ({$pending->action_type})", [
                'actor_count'     => $pending->actor_count,
                'author_type'     => $pending->post_author_type,
                'author_id'       => $pending->post_author_id,
                'notification_id' => $notificationId,
            ]);
        } catch (\Exception $e) {
            Log::error("FlushPendingFeedNotificationsJob::flushOne failed: {$e->getMessage()}", [
                'pending_id' => $pending->id,
            ]);
        }
    }

    // ── Save to notifications table ────────────────────────────────────────

    /**
     * Saves one row to the notifications table so the author sees it
     * in their in-app notification bell.
     *
     * Sets ONLY the matching recipient column (user_id / agent_id / office_id)
     * and leaves the other two as NULL — matching NotificationController logic.
     */
    private function saveToNotificationsTable(
        PendingNotification $pending,
        mixed               $author,
        string              $title,
        string              $body,
        array               $titles,
    ): string {
        $notificationId = (string) Str::uuid();

        // Start with all recipient columns null
        $row = [
            'id'         => $notificationId,
            'user_id'    => null,
            'agent_id'   => null,
            'office_id'  => null,
            'title'      => $title,
            'message'    => $body,
            'type'       => 'system',
            'priority'   => match ($pending->action_type) {
                'comment' => 'medium',
                'like'    => 'low',
                'save'    => 'low',
                default   => 'low',
            },
            'data'       => json_encode([
                'feed_social'          => true,
                'action_type'          => $pending->action_type,
                'post_id'              => (string) $pending->post_id,
                'actor_count'          => $pending->actor_count,
                'last_actor_name'      => $pending->last_actor_name ?? '',
                'last_comment_preview' => $pending->last_comment_preview ?? '',
                // All 3 languages so Flutter picks the right one at display time
                'title_en'             => $titles['en'] ?? $title,
                'title_ar'             => $titles['ar'] ?? $title,
                'title_ku'             => $titles['ku'] ?? $title,
                'body'                 => $body,
                'action_url'           => "/feed/post/{$pending->post_id}",
                'action_text'          => 'View Post',
            ]),
            'action_url'  => "/feed/post/{$pending->post_id}",
            'action_text' => 'View Post',
            'is_read'     => false,
            'sent_at'     => now(),
            'expires_at'  => now()->addDays(30),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];

        // ── Set ONLY the correct recipient column ──────────────────────────
        // Mirrors NotificationController::getRecipientColumn()
        switch ($pending->post_author_type) {
            case 'user':
                $row['user_id'] = $author->id;
                break;
            case 'agent':
                $row['agent_id'] = $author->id;
                break;
            case 'office':
                $row['office_id'] = $author->id;
                break;
        }

        DB::table('notifications')->insert($row);

        Log::info("Notification saved to DB for {$pending->post_author_type} {$author->id}", [
            'notification_id' => $notificationId,
            'post_id'         => $pending->post_id,
            'action_type'     => $pending->action_type,
        ]);

        return $notificationId;
    }

    // ── FCM send ───────────────────────────────────────────────────────────

    private function sendFcm(
        PendingNotification $pending,
        mixed               $author,
        string              $title,
        string              $body,
        array               $titles,
        string              $notificationId,
        FirebaseService     $firebaseService,
    ): void {
        try {
            $fcmNotification = [
                'title'   => $title,
                'message' => $body,
            ];

            $fcmData = [
                'type'                 => 'feed_social',
                'action_type'          => $pending->action_type,
                'post_id'              => (string) $pending->post_id,
                'actor_count'          => (string) $pending->actor_count,
                'last_actor_name'      => $pending->last_actor_name ?? '',
                'last_comment_preview' => $pending->last_comment_preview ?? '',
                'notification_id'      => $notificationId,
                'action_url'           => "/feed/post/{$pending->post_id}",
                'action_text'          => 'View Post',
                // All 3 languages — Flutter picks based on app locale
                'title_en'             => $titles['en'] ?? $title,
                'title_ar'             => $titles['ar'] ?? $title,
                'title_ku'             => $titles['ku'] ?? $title,
                'body'                 => $body,
            ];

            $result = match ($pending->post_author_type) {
                'user'   => $firebaseService->sendToUser($author, $fcmNotification, $fcmData),
                'agent'  => $firebaseService->sendToAgent($author, $fcmNotification, $fcmData),
                'office' => $firebaseService->sendToOffice($author, $fcmNotification, $fcmData),
                default  => false,
            };

            if ($result) {
                Log::info("FCM sent to {$pending->post_author_type} {$author->id} for post {$pending->post_id}");
            } else {
                Log::warning("FCM failed for {$pending->post_author_type} {$author->id} — no device tokens?");
            }
        } catch (\Exception $e) {
            Log::error("FCM send failed: {$e->getMessage()}", [
                'author_type' => $pending->post_author_type,
                'author_id'   => $author->id,
                'post_id'     => $pending->post_id,
            ]);
        }
    }

    // ── Load author ────────────────────────────────────────────────────────

    private function loadAuthor(string $type, int $id): mixed
    {
        return match ($type) {
            'user'   => User::find($id),
            'agent'  => Agent::find($id),
            'office' => RealEstateOffice::find($id),
            default  => null,
        };
    }

    // ── Milestone logic ────────────────────────────────────────────────────

    private function isMilestone(PendingNotification $pending): bool
    {
        $count  = $pending->actor_count;
        $action = $pending->action_type;

        $milestones = match ($action) {
            'like'    => self::LIKE_MILESTONES,
            'comment' => self::COMMENT_MILESTONES,
            'save'    => self::SAVE_MILESTONES,
            default   => self::LIKE_MILESTONES,
        };

        if (in_array($count, $milestones, true)) return true;

        [$progressiveStart, $progressiveStep] = match ($action) {
            'like'    => [100, 50],
            'comment' => [50,  25],
            'save'    => [100, 50],
            default   => [100, 50],
        };

        return $count > $progressiveStart && ($count % $progressiveStep === 0);
    }

    private function resetForNextBatch(PendingNotification $pending): void
    {
        $pending->update([
            'is_flushed'     => false,
            'cooldown_until' => now()->addMinutes(self::COOLDOWN_MINUTES),
        ]);
    }
}