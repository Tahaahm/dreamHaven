<?php

namespace App\Models\Support;

use App\Models\Feed\FeedComment;
use App\Models\Feed\FeedLike;
use App\Models\Feed\FeedPost;
use App\Models\Feed\FeedSave;
use Illuminate\Database\Eloquent\Relations\MorphMany;


/**
 * HasFeedActivity
 *
 * Add this trait to: User, Agent, RealEstateOffice
 *
 * Usage in each model:
 *   use HasFeedActivity;
 *
 * Then add to $fillable if needed (nothing extra required in DB —
 * all relationships are polymorphic on the feed_ tables).
 */
trait HasFeedActivity
{
    // ── Posts authored by this model ─────────────────────────────────────

    public function feedPosts(): MorphMany
    {
        return $this->morphMany(FeedPost::class, 'author', 'author_type', 'author_id');
    }

    // ── Likes given by this model ─────────────────────────────────────────

    public function feedLikes(): MorphMany
    {
        return $this->morphMany(FeedLike::class, 'liker', 'liker_type', 'liker_id');
    }

    // ── Comments made by this model ───────────────────────────────────────

    public function feedComments(): MorphMany
    {
        return $this->morphMany(FeedComment::class, 'author', 'author_type', 'author_id');
    }

    // ── Saved / bookmarked posts ──────────────────────────────────────────

    public function feedSaves(): MorphMany
    {
        return $this->morphMany(FeedSave::class, 'saver', 'saver_type', 'saver_id');
    }

    // =====================================================================
    // HELPER METHODS
    // =====================================================================

    /**
     * Like a feed post
     * Returns true if liked, false if already liked (idempotent)
     */
    public function likePost(string $postId): bool
    {
        $exists = $this->feedLikes()
            ->where('post_id', $postId)
            ->exists();

        if ($exists) {
            return false;
        }

        $this->feedLikes()->create(['post_id' => $postId]);

        // Increment denormalized counter on the post
        FeedPost::where('id', $postId)->increment('likes_count');

        return true;
    }

    /**
     * Unlike a feed post
     * Returns true if unliked, false if was not liked
     */
    public function unlikePost(string $postId): bool
    {
        $deleted = $this->feedLikes()
            ->where('post_id', $postId)
            ->delete();

        if ($deleted) {
            FeedPost::where('id', $postId)->decrement('likes_count');
            return true;
        }

        return false;
    }

    /**
     * Toggle like on a post
     * Returns ['action' => 'liked'|'unliked', 'likes_count' => int]
     */
    public function togglePostLike(string $postId): array
    {
        $liked = $this->feedLikes()->where('post_id', $postId)->exists();

        if ($liked) {
            $this->unlikePost($postId);
            $action = 'unliked';
        } else {
            $this->likePost($postId);
            $action = 'liked';
        }

        $post = FeedPost::find($postId);

        return [
            'action'      => $action,
            'likes_count' => $post?->likes_count ?? 0,
        ];
    }

    /**
     * Save / bookmark a feed post
     */
    public function savePost(string $postId): bool
    {
        $exists = $this->feedSaves()->where('post_id', $postId)->exists();

        if ($exists) {
            return false;
        }

        $this->feedSaves()->create(['post_id' => $postId]);
        FeedPost::where('id', $postId)->increment('saves_count');

        return true;
    }

    /**
     * Unsave / remove bookmark from a feed post
     */
    public function unsavePost(string $postId): bool
    {
        $deleted = $this->feedSaves()->where('post_id', $postId)->delete();

        if ($deleted) {
            FeedPost::where('id', $postId)->decrement('saves_count');
            return true;
        }

        return false;
    }

    /**
     * Toggle save on a post
     */
    public function togglePostSave(string $postId): array
    {
        $saved = $this->feedSaves()->where('post_id', $postId)->exists();

        if ($saved) {
            $this->unsavePost($postId);
            $action = 'unsaved';
        } else {
            $this->savePost($postId);
            $action = 'saved';
        }

        $post = FeedPost::find($postId);

        return [
            'action'      => $action,
            'saves_count' => $post?->saves_count ?? 0,
        ];
    }

    /**
     * Check if this model has liked a specific post
     */
    public function hasLikedPost(string $postId): bool
    {
        return $this->feedLikes()->where('post_id', $postId)->exists();
    }

    /**
     * Check if this model has saved a specific post
     */
    public function hasSavedPost(string $postId): bool
    {
        return $this->feedSaves()->where('post_id', $postId)->exists();
    }
}
