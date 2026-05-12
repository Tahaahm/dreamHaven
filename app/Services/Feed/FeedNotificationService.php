<?php

namespace App\Services\Feed;

use App\Models\Agent;
use App\Models\Feed\FeedComment;
use App\Models\Feed\FeedFollow;
use App\Models\Feed\FeedPost;
use App\Models\PendingNotification;
use App\Models\RealEstateOffice;
use App\Models\User;
use App\Services\FCMNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * FeedNotificationService
 *
 * Handles ALL push notifications + DB saves for the Social Feed.
 *
 * Strategy:
 *   BATCHED  (pending_notifications → FlushPendingFeedNotificationsJob every 3 min):
 *     - notifyPostLiked()       ← can get 200 likes, must batch
 *     - notifyPostCommented()   ← can get 100 comments, must batch
 *
 *   INSTANT (direct FCM + saved to notifications table immediately):
 *     - notifyCommentLiked()    ← personal 1:1
 *     - notifyCommentReplied()  ← personal 1:1
 *     - notifyNewFollower()     ← personal 1:1
 *     - notifyPostFeatured()    ← admin action, always instant
 *     - notifyPostRejected()    ← admin action, always instant
 *
 *   BULK (called from queued Job — never inline):
 *     - notifyFollowersOfNewPost() ← can have 5,000 followers
 */
class FeedNotificationService
{
    // Max actors stored in pending arrays before we stop growing (still count)
    const MAX_STORED_ACTORS = 50;

    public function __construct(
        private readonly FCMNotificationService $fcm
    ) {}

    // =========================================================================
    // BATCHED — POST LIKES
    // =========================================================================

    /**
     * Called by FeedPostController@toggleLike when action === 'liked'
     * → Queues into pending_notifications (NOT instant FCM)
     * → FlushPendingFeedNotificationsJob sends batched FCM every 3 min
     */
    public function notifyPostLiked(FeedPost $post, object $liker): void
    {
        $author = $this->resolveAuthor($post->author_type, $post->author_id);
        if (!$author) return;
        if ($this->isSamePerson($author, $liker)) return;

        $this->queuePending(
            post: $post,
            actorId: $liker->id,
            actorType: $this->shortType($liker),
            actorName: $this->getName($liker),
            actionType: 'like',
        );
    }

    /**
     * Called by FeedPostController@toggleLike when action === 'unliked'
     * → Removes actor from pending_notifications
     */
    public function notifyPostUnliked(FeedPost $post, object $liker): void
    {
        $this->dequeuePending(
            postId: (string) $post->id,
            actorId: (string) $liker->id,
            actorType: $this->shortType($liker),
            actionType: 'like',
        );
    }

    // =========================================================================
    // BATCHED — POST COMMENTS
    // =========================================================================

    /**
     * Called by FeedCommentController@store for top-level comments
     * → Queues into pending_notifications (batched)
     */
    public function notifyPostCommented(FeedPost $post, FeedComment $comment, object $commenter): void
    {
        $author = $this->resolveAuthor($post->author_type, $post->author_id);
        if (!$author) return;
        if ($this->isSamePerson($author, $commenter)) return;

        $this->queuePending(
            post: $post,
            actorId: $commenter->id,
            actorType: $this->shortType($commenter),
            actorName: $this->getName($commenter),
            actionType: 'comment',
            commentText: $comment->body_en ?? $comment->body_ar ?? $comment->body_ku,
        );
    }

    // =========================================================================
    // INSTANT — COMMENT LIKED
    // =========================================================================

    /**
     * Called by FeedCommentController@toggleLike when action === 'liked'
     * → Instant FCM + saved to notifications table
     */
    public function notifyCommentLiked(FeedComment $comment, object $liker): void
    {
        $author = $this->resolveAuthor($comment->author_type, $comment->author_id);
        if (!$author) return;
        if ($this->isSamePerson($author, $liker)) return;

        $likerName = $this->getName($liker);
        $preview   = $this->bodyPreview($comment);

        $title = '❤️ ' . $likerName . ' liked your comment';
        $body  = $preview ?: 'Tap to see your comment';

        $data = [
            'type'       => 'feed_comment_liked',
            'post_id'    => (string) $comment->post_id,
            'comment_id' => (string) $comment->id,
            'liker_id'   => (string) $liker->id,
            'liker_type' => $this->shortType($liker),
            'liker_name' => $likerName,
            'action_url' => '/feed/post/' . $comment->post_id,
            'action_text' => 'View Post',
        ];

        // 1. Save to notifications table
        $this->saveInstantNotification($author, $title, $body, $data, 'low');

        // 2. Send FCM
        $this->sendToAuthor($author, $title, $body, $data);
    }

    // =========================================================================
    // INSTANT — COMMENT REPLIED
    // =========================================================================

    /**
     * Called by FeedCommentController@store for replies (parent_id is set)
     * → Instant FCM + saved to notifications table
     */
    public function notifyCommentReplied(FeedComment $parentComment, FeedComment $reply, object $replier): void
    {
        $author = $this->resolveAuthor($parentComment->author_type, $parentComment->author_id);
        if (!$author) return;
        if ($this->isSamePerson($author, $replier)) return;

        $replierName = $this->getName($replier);
        $preview     = $this->bodyPreview($reply);

        $title = '↩️ ' . $replierName . ' replied to your comment';
        $body  = $preview ?: 'Tap to see the reply';

        $data = [
            'type'         => 'feed_comment_replied',
            'post_id'      => (string) $parentComment->post_id,
            'comment_id'   => (string) $parentComment->id,
            'reply_id'     => (string) $reply->id,
            'replier_id'   => (string) $replier->id,
            'replier_type' => $this->shortType($replier),
            'replier_name' => $replierName,
            'action_url'   => '/feed/post/' . $parentComment->post_id,
            'action_text'  => 'View Post',
        ];

        // 1. Save to notifications table
        $this->saveInstantNotification($author, $title, $body, $data, 'medium');

        // 2. Send FCM
        $this->sendToAuthor($author, $title, $body, $data);
    }

    // =========================================================================
    // INSTANT — NEW FOLLOWER
    // =========================================================================

    /**
     * Called by FeedFollowController@toggle when action === 'followed'
     * → Instant FCM + saved to notifications table
     */
    public function notifyNewFollower(object $followee, object $follower): void
    {
        if ($this->isSamePerson($followee, $follower)) return;

        $followerName = $this->getName($follower);

        $title = '👤 ' . $followerName . ' started following you';
        $body  = 'Tap to view their profile';

        $data = [
            'type'          => 'feed_new_follower',
            'follower_id'   => (string) $follower->id,
            'follower_type' => $this->shortType($follower),
            'follower_name' => $followerName,
            'action_url'    => '/feed/profile/' . $this->shortType($follower) . '/' . $follower->id,
            'action_text'   => 'View Profile',
        ];

        // 1. Save to notifications table
        $this->saveInstantNotification($followee, $title, $body, $data, 'medium');

        // 2. Send FCM
        $this->sendToAuthor($followee, $title, $body, $data);
    }

    // =========================================================================
    // BULK — NEW POST FROM FOLLOWED ACCOUNT
    // =========================================================================

    /**
     * Called from NotifyFollowersOfNewPost Job (queued — never inline in controller)
     * → Instant FCM to all followers, chunked in 100s
     * → Does NOT save to notifications table (too many rows for bulk)
     */
    public function notifyFollowersOfNewPost(FeedPost $post): void
    {
        $author = $this->resolveAuthor($post->author_type, $post->author_id);
        if (!$author) return;

        $authorName = $this->getName($author);
        $preview    = $this->bodyPreview($post);
        $title      = '🏠 ' . $authorName . ' posted something new';
        $body       = $preview ?: 'Tap to see the post';

        $data = [
            'type'        => 'feed_new_post_from_following',
            'post_id'     => (string) $post->id,
            'author_id'   => (string) $author->id,
            'author_type' => $this->shortType($author),
            'author_name' => $authorName,
            'action_url'  => '/feed/post/' . $post->id,
            'action_text' => 'View Post',
        ];

        // Chunk followers — never load thousands at once
        FeedFollow::where('followee_id', $author->id)
            ->where('followee_type', $post->author_type)
            ->where('status', 'accepted')
            ->with('follower')
            ->chunkById(100, function ($follows) use ($title, $body, $data) {
                foreach ($follows as $follow) {
                    $follower = $follow->follower;
                    if (!$follower) continue;
                    $this->sendToAuthor($follower, $title, $body, $data);
                }
            });
    }

    // =========================================================================
    // INSTANT — ADMIN ACTIONS
    // =========================================================================

    /**
     * Notify post author when admin features their post
     */
    public function notifyPostFeatured(FeedPost $post): void
    {
        $author = $this->resolveAuthor($post->author_type, $post->author_id);
        if (!$author) return;

        $title = '⭐ Your post was featured!';
        $body  = 'Dream Mulk selected your post as featured content';

        $data = [
            'type'       => 'feed_post_featured',
            'post_id'    => (string) $post->id,
            'action_url' => '/feed/post/' . $post->id,
            'action_text' => 'View Post',
        ];

        $this->saveInstantNotification($author, $title, $body, $data, 'high');
        $this->sendToAuthor($author, $title, $body, $data);
    }

    /**
     * Notify post author when their post is rejected by moderation
     */
    public function notifyPostRejected(FeedPost $post, string $reason = ''): void
    {
        $author = $this->resolveAuthor($post->author_type, $post->author_id);
        if (!$author) return;

        $title = 'Your post was removed';
        $body  = $reason ?: 'Your post did not meet our community guidelines';

        $data = [
            'type'    => 'feed_post_rejected',
            'post_id' => (string) $post->id,
            'reason'  => $reason,
        ];

        $this->saveInstantNotification($author, $title, $body, $data, 'high');
        $this->sendToAuthor($author, $title, $body, $data);
    }

    // =========================================================================
    // PRIVATE — PENDING (BATCHED) QUEUE
    // =========================================================================

    /**
     * Upsert a pending_notifications row.
     * FlushPendingFeedNotificationsJob reads these every 3 min,
     * sends one batched FCM, and saves one row to notifications table.
     */
    private function queuePending(
        FeedPost $post,
        string   $actorId,
        string   $actorType,
        string   $actorName,
        string   $actionType,
        ?string  $commentText = null,
    ): void {
        try {
            $existing = PendingNotification::where('post_id', $post->id)
                ->where('action_type', $actionType)
                ->first();

            if ($existing) {
                $actorIds   = $existing->actor_ids   ?? [];
                $actorTypes = $existing->actor_types ?? [];
                $actorNames = $existing->actor_names ?? [];

                // Deduplicate — don't add same actor twice
                $alreadyIn = false;
                foreach ($actorIds as $i => $id) {
                    if ($id == $actorId && ($actorTypes[$i] ?? '') === $actorType) {
                        $alreadyIn = true;
                        break;
                    }
                }

                if (!$alreadyIn && count($actorIds) < self::MAX_STORED_ACTORS) {
                    $actorIds[]   = $actorId;
                    $actorTypes[] = $actorType;
                    $actorNames[] = $actorName;
                }

                $updates = [
                    'actor_ids'       => $actorIds,
                    'actor_types'     => $actorTypes,
                    'actor_names'     => $actorNames,
                    'actor_count'     => $alreadyIn
                        ? $existing->actor_count
                        : $existing->actor_count + 1,
                    'last_actor_id'   => $actorId,
                    'last_actor_type' => $actorType,
                    'last_actor_name' => $actorName,
                    'last_updated_at' => now(),
                    // Reset so scheduler picks it up again after cooldown
                    'is_flushed'      => false,
                ];

                if ($commentText !== null) {
                    $text = trim($commentText);
                    $updates['last_comment_preview'] = mb_strlen($text) > 150
                        ? mb_substr($text, 0, 147) . '...'
                        : $text;
                }

                $existing->update($updates);
            } else {
                // First action on this post — create new pending row
                $preview = null;
                if ($commentText !== null) {
                    $text    = trim($commentText);
                    $preview = mb_strlen($text) > 150
                        ? mb_substr($text, 0, 147) . '...'
                        : $text;
                }

                PendingNotification::create([
                    'post_id'              => $post->id,
                    'post_author_type'     => $post->author_type,
                    'post_author_id'       => $post->author_id,
                    'action_type'          => $actionType,
                    'actor_ids'            => [$actorId],
                    'actor_types'          => [$actorType],
                    'actor_names'          => [$actorName],
                    'actor_count'          => 1,
                    'last_actor_id'        => $actorId,
                    'last_actor_type'      => $actorType,
                    'last_actor_name'      => $actorName,
                    'last_comment_preview' => $preview,
                    'last_updated_at'      => now(),
                    'cooldown_until'       => null,
                    'is_flushed'           => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('FeedNotificationService::queuePending failed: ' . $e->getMessage(), [
                'post_id'     => $post->id,
                'actor_id'    => $actorId,
                'action_type' => $actionType,
            ]);
        }
    }

    /**
     * Remove an actor from pending (called on unlike/unsave)
     */
    private function dequeuePending(
        string $postId,
        string $actorId,
        string $actorType,
        string $actionType,
    ): void {
        try {
            $pending = PendingNotification::where('post_id', $postId)
                ->where('action_type', $actionType)
                ->first();

            if (!$pending) return;

            $actorIds   = $pending->actor_ids   ?? [];
            $actorTypes = $pending->actor_types ?? [];
            $actorNames = $pending->actor_names ?? [];

            $indexToRemove = null;
            foreach ($actorIds as $i => $id) {
                if ($id == $actorId && ($actorTypes[$i] ?? '') === $actorType) {
                    $indexToRemove = $i;
                    break;
                }
            }

            if ($indexToRemove !== null) {
                array_splice($actorIds, $indexToRemove, 1);
                array_splice($actorTypes, $indexToRemove, 1);
                array_splice($actorNames, $indexToRemove, 1);
            }

            $newCount = max(0, $pending->actor_count - 1);

            if ($newCount === 0) {
                $pending->delete();
                return;
            }

            $lastIdx = count($actorIds) - 1;

            $pending->update([
                'actor_ids'       => $actorIds,
                'actor_types'     => $actorTypes,
                'actor_names'     => $actorNames,
                'actor_count'     => $newCount,
                'last_actor_id'   => $actorIds[$lastIdx]   ?? null,
                'last_actor_type' => $actorTypes[$lastIdx] ?? null,
                'last_actor_name' => $actorNames[$lastIdx] ?? null,
                'last_updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('FeedNotificationService::dequeuePending failed: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // PRIVATE — INSTANT NOTIFICATION SAVE
    // =========================================================================

    /**
     * Save a single instant notification to the notifications table.
     * Sets ONLY the correct recipient column (user_id / agent_id / office_id).
     * Called for all non-batched notifications so they show in the in-app bell.
     */
    private function saveInstantNotification(
        object $author,
        string $title,
        string $body,
        array  $data,
        string $priority = 'low',
    ): void {
        try {
            $row = [
                'id'          => (string) Str::uuid(),
                'user_id'     => null,
                'agent_id'    => null,
                'office_id'   => null,
                'title'       => $title,
                'message'     => $body,
                'type'        => 'system',
                'priority'    => $priority,
                'data'        => json_encode($data),
                'action_url'  => $data['action_url']  ?? null,
                'action_text' => $data['action_text'] ?? null,
                'is_read'     => false,
                'sent_at'     => now(),
                'expires_at'  => now()->addDays(30),
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            // Set ONLY the correct recipient column
            switch ($this->shortType($author)) {
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
        } catch (\Exception $e) {
            Log::error('FeedNotificationService::saveInstantNotification failed: ' . $e->getMessage(), [
                'author_id'   => $author->id,
                'author_type' => get_class($author),
                'title'       => $title,
            ]);
        }
    }

    // =========================================================================
    // PRIVATE — FCM SEND
    // =========================================================================

    /**
     * Send FCM to any author type using existing FCMNotificationService.
     * Reads getFCMTokens() from the model — same as your existing service.
     */
    private function sendToAuthor(object $author, string $title, string $body, array $data = []): void
    {
        try {
            $tokens = $author->getFCMTokens();
            if (empty($tokens)) return;

            foreach ($tokens as $token) {
                $this->fcm->sendToToken($token, $title, $body, $data);
            }
        } catch (\Exception $e) {
            Log::error('FeedNotificationService::sendToAuthor failed', [
                'author_type' => get_class($author),
                'author_id'   => $author->id,
                'title'       => $title,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    // =========================================================================
    // PRIVATE — HELPERS
    // =========================================================================

    /**
     * Resolve author model from morph type string + ID.
     * Supports both short names ('user') and full class paths.
     */
    private function resolveAuthor(string $type, mixed $id): ?object
    {
        $map = [
            'user'                          => User::class,
            'agent'                         => Agent::class,
            'office'                        => RealEstateOffice::class,
            'App\\Models\\User'             => User::class,
            'App\\Models\\Agent'            => Agent::class,
            'App\\Models\\RealEstateOffice' => RealEstateOffice::class,
        ];

        $class = $map[$type] ?? null;

        if (!$class) {
            Log::warning('FeedNotificationService: Unknown author type', ['type' => $type]);
            return null;
        }

        return $class::find($id);
    }

    /**
     * Get display name from any author model.
     * Tries username → agent_name → company_name → 'Someone'
     */
    private function getName(object $model): string
    {
        return $model->username
            ?? $model->agent_name
            ?? $model->full_name
            ?? $model->name
            ?? $model->company_name
            ?? 'Someone';
    }

    /**
     * Get short morph type name from a model instance.
     */
    private function shortType(object $model): string
    {
        return match (true) {
            $model instanceof User             => 'user',
            $model instanceof Agent            => 'agent',
            $model instanceof RealEstateOffice => 'office',
            default                            => 'user',
        };
    }

    /**
     * Get a short body preview from post or comment.
     * Tries EN → AR → KU.
     */
    private function bodyPreview(object $model, int $length = 80): string
    {
        $body = $model->body_en ?? $model->body_ar ?? $model->body_ku ?? '';
        $body = trim($body);
        return mb_strlen($body) > $length
            ? mb_substr($body, 0, $length) . '...'
            : $body;
    }

    /**
     * Check if two author objects are the same person.
     * Prevents self-notifications.
     */
    private function isSamePerson(object $a, object $b): bool
    {
        return (string) $a->id === (string) $b->id
            && get_class($a) === get_class($b);
    }
}
