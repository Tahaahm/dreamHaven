<?php

namespace App\Services\Feed;

use App\Models\Agent;
use App\Models\Feed\FeedComment;
use App\Models\Feed\FeedFollow;
use App\Models\Feed\FeedPost;
use App\Models\RealEstateOffice;
use App\Models\User;
use App\Services\FCMNotificationService;
use Illuminate\Support\Facades\Log;

/**
 * FeedNotificationService
 *
 * Handles ALL push notifications related to the Social Feed.
 * Wraps the existing FCMNotificationService — never calls FCM directly.
 *
 * Notification types covered:
 *   - Someone liked your post
 *   - Someone commented on your post
 *   - Someone replied to your comment
 *   - Someone liked your comment
 *   - Someone followed you
 *   - Someone you follow posted something new
 *   - Your post was featured by admin
 */
class FeedNotificationService
{
    public function __construct(
        private readonly FCMNotificationService $fcm
    ) {}

    // =========================================================================
    // LIKES
    // =========================================================================

    /**
     * Notify post author when someone likes their post
     */
    public function notifyPostLiked(FeedPost $post, object $liker): void
    {
        $author = $this->resolveAuthor($post->author_type, $post->author_id);
        if (!$author) return;

        // Don't notify if you liked your own post
        if ($this->isSamePerson($author, $liker)) return;

        $likerName = $this->getName($liker);
        $preview   = $this->bodyPreview($post);

        $this->sendToAuthor(
            author: $author,
            title: '❤️ ' . $likerName . ' liked your post',
            body: $preview ?: 'Tap to see your post',
            data: [
                'type'    => 'feed_post_liked',
                'post_id' => $post->id,
                'liker_id'   => $liker->id,
                'liker_type' => $this->shortType($liker),
                'liker_name' => $likerName,
            ]
        );
    }

    /**
     * Notify comment author when someone likes their comment
     */
    public function notifyCommentLiked(FeedComment $comment, object $liker): void
    {
        $author = $this->resolveAuthor($comment->author_type, $comment->author_id);
        if (!$author) return;
        if ($this->isSamePerson($author, $liker)) return;

        $likerName = $this->getName($liker);

        $this->sendToAuthor(
            author: $author,
            title: '❤️ ' . $likerName . ' liked your comment',
            body: $this->bodyPreview($comment) ?: 'Tap to see',
            data: [
                'type'       => 'feed_comment_liked',
                'post_id'    => $comment->post_id,
                'comment_id' => $comment->id,
                'liker_name' => $likerName,
            ]
        );
    }

    // =========================================================================
    // COMMENTS
    // =========================================================================

    /**
     * Notify post author when someone comments on their post
     */
    public function notifyPostCommented(FeedPost $post, FeedComment $comment, object $commenter): void
    {
        $author = $this->resolveAuthor($post->author_type, $post->author_id);
        if (!$author) return;
        if ($this->isSamePerson($author, $commenter)) return;

        $commenterName = $this->getName($commenter);

        $this->sendToAuthor(
            author: $author,
            title: '💬 ' . $commenterName . ' commented on your post',
            body: $this->bodyPreview($comment) ?: 'Tap to see the comment',
            data: [
                'type'         => 'feed_post_commented',
                'post_id'      => $post->id,
                'comment_id'   => $comment->id,
                'commenter_name' => $commenterName,
            ]
        );
    }

    /**
     * Notify comment author when someone replies to their comment
     */
    public function notifyCommentReplied(FeedComment $parentComment, FeedComment $reply, object $replier): void
    {
        $author = $this->resolveAuthor($parentComment->author_type, $parentComment->author_id);
        if (!$author) return;
        if ($this->isSamePerson($author, $replier)) return;

        $replierName = $this->getName($replier);

        $this->sendToAuthor(
            author: $author,
            title: '↩️ ' . $replierName . ' replied to your comment',
            body: $this->bodyPreview($reply) ?: 'Tap to see the reply',
            data: [
                'type'          => 'feed_comment_replied',
                'post_id'       => $parentComment->post_id,
                'comment_id'    => $parentComment->id,
                'reply_id'      => $reply->id,
                'replier_name'  => $replierName,
            ]
        );
    }

    // =========================================================================
    // FOLLOWS
    // =========================================================================

    /**
     * Notify someone when they get a new follower
     */
    public function notifyNewFollower(object $followee, object $follower): void
    {
        if ($this->isSamePerson($followee, $follower)) return;

        $followerName = $this->getName($follower);

        $this->sendToAuthor(
            author: $followee,
            title: '👤 ' . $followerName . ' started following you',
            body: 'Tap to view their profile',
            data: [
                'type'          => 'feed_new_follower',
                'follower_id'   => $follower->id,
                'follower_type' => $this->shortType($follower),
                'follower_name' => $followerName,
            ]
        );
    }

    // =========================================================================
    // NEW POST FROM FOLLOWED ACCOUNT
    // =========================================================================

    /**
     * Notify all followers of an author when they publish a new post.
     *
     * ⚠️  Call this from a queued Job — never inline in a controller.
     *     An agent with 5,000 followers = 5,000 FCM calls.
     *
     * Usage in controller:
     *   NotifyFollowersOfNewPost::dispatch($post)->onQueue('notifications');
     */
    public function notifyFollowersOfNewPost(FeedPost $post): void
    {
        $author     = $this->resolveAuthor($post->author_type, $post->author_id);
        if (!$author) return;

        $authorName = $this->getName($author);
        $preview    = $this->bodyPreview($post);

        // Load followers in chunks — never load 10,000 at once
        FeedFollow::where('followee_id', $author->id)
            ->where('followee_type', $post->author_type)
            ->where('status', 'accepted')
            ->with('follower') // eager load
            ->chunkById(100, function ($follows) use ($post, $authorName, $preview) {
                foreach ($follows as $follow) {
                    $follower = $follow->follower;
                    if (!$follower) continue;

                    $this->sendToAuthor(
                        author: $follower,
                        title: '🏠 ' . $authorName . ' posted something new',
                        body: $preview ?: 'Tap to see the post',
                        data: [
                            'type'        => 'feed_new_post_from_following',
                            'post_id'     => $post->id,
                            'author_name' => $authorName,
                        ]
                    );
                }
            });
    }

    // =========================================================================
    // ADMIN ACTIONS
    // =========================================================================

    /**
     * Notify post author when admin features their post
     */
    public function notifyPostFeatured(FeedPost $post): void
    {
        $author = $this->resolveAuthor($post->author_type, $post->author_id);
        if (!$author) return;

        $this->sendToAuthor(
            author: $author,
            title: '⭐ Your post was featured!',
            body: 'Dream Mulk selected your post as featured content',
            data: [
                'type'    => 'feed_post_featured',
                'post_id' => $post->id,
            ]
        );
    }

    /**
     * Notify post author when their post is rejected by moderation
     */
    public function notifyPostRejected(FeedPost $post, string $reason = ''): void
    {
        $author = $this->resolveAuthor($post->author_type, $post->author_id);
        if (!$author) return;

        $this->sendToAuthor(
            author: $author,
            title: 'Your post was removed',
            body: $reason ?: 'Your post did not meet our community guidelines',
            data: [
                'type'    => 'feed_post_rejected',
                'post_id' => $post->id,
                'reason'  => $reason,
            ]
        );
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Send notification to any author type (User / Agent / RealEstateOffice)
     * Handles each model's FCM token structure correctly
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
            Log::error('FeedNotificationService: Failed to send notification', [
                'author_type' => get_class($author),
                'author_id'   => $author->id,
                'title'       => $title,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    /**
     * Resolve the author model from type string + ID
     * Supports both short morph names and full class paths
     */
    private function resolveAuthor(string $type, string $id): ?object
    {
        $map = [
            'user'                              => User::class,
            'agent'                             => Agent::class,
            'office'                            => RealEstateOffice::class,
            'App\\Models\\User'                 => User::class,
            'App\\Models\\Agent'                => Agent::class,
            'App\\Models\\RealEstateOffice'     => RealEstateOffice::class,
        ];

        $class = $map[$type] ?? null;

        if (!$class) {
            Log::warning('FeedNotificationService: Unknown author type', ['type' => $type]);
            return null;
        }

        return $class::find($id);
    }

    /**
     * Get display name from any author model
     */
    private function getName(object $model): string
    {
        return $model->username
            ?? $model->agent_name
            ?? $model->company_name
            ?? 'Someone';
    }

    /**
     * Get short morph type name
     */
    private function shortType(object $model): string
    {
        return match (get_class($model)) {
            User::class              => 'user',
            Agent::class             => 'agent',
            RealEstateOffice::class  => 'office',
            default                  => 'user',
        };
    }

    /**
     * Get a short preview of post/comment body in the best available language
     */
    private function bodyPreview(object $model, int $length = 80): string
    {
        $body = $model->body_en ?? $model->body_ar ?? $model->body_ku ?? '';
        return mb_strlen($body) > $length
            ? mb_substr($body, 0, $length) . '...'
            : $body;
    }

    /**
     * Check if two author objects are the same person
     * Prevents self-notifications
     */
    private function isSamePerson(object $a, object $b): bool
    {
        return $a->id === $b->id && get_class($a) === get_class($b);
    }
}
