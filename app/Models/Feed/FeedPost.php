<?php

namespace App\Models\Feed;

use App\Models\Branch;
use App\Models\Property;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedPost extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'feed_posts';

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'author_id',
        'author_type',
        'post_type',
        'body_en',
        'body_ar',
        'body_ku',
        'branch_id',        // nullable — post can be global
        'property_id',      // nullable — only for listing_share type
        'status',
        'likes_count',
        'comments_count',
        'saves_count',
        'shares_count',
        'views_count',
        'is_pinned',
        'is_featured',
    ];

    protected $casts = [
        'is_pinned'      => 'boolean',
        'is_featured'    => 'boolean',
        'likes_count'    => 'integer',
        'comments_count' => 'integer',
        'saves_count'    => 'integer',
        'shares_count'   => 'integer',
        'views_count'    => 'integer',
    ];

    // =====================================================================
    // RELATIONSHIPS
    // =====================================================================

    /**
     * Polymorphic author — resolves to User, Agent, or RealEstateOffice
     */
    public function author(): MorphTo
    {
        return $this->morphTo('author', 'author_type', 'author_id');
    }

    /**
     * Branch (neighborhood) — nullable, not required
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Linked property — only present for listing_share posts
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Media (images + videos)
     */
    public function media(): HasMany
    {
        return $this->hasMany(FeedPostMedia::class, 'post_id')->orderBy('sort_order');
    }

    /**
     * Only images
     */
    public function images(): HasMany
    {
        return $this->hasMany(FeedPostMedia::class, 'post_id')
            ->where('media_type', 'image')
            ->orderBy('sort_order');
    }

    /**
     * Only videos
     */
    public function videos(): HasMany
    {
        return $this->hasMany(FeedPostMedia::class, 'post_id')
            ->where('media_type', 'video')
            ->orderBy('sort_order');
    }

    /**
     * Tags
     */
    public function tags(): HasMany
    {
        return $this->hasMany(FeedPostTag::class, 'post_id');
    }

    /**
     * Likes
     */
    public function likes(): HasMany
    {
        return $this->hasMany(FeedLike::class, 'post_id');
    }

    /**
     * Top-level comments only (not replies)
     */
    public function comments(): HasMany
    {
        return $this->hasMany(FeedComment::class, 'post_id')
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc');
    }

    /**
     * All comments including replies
     */
    public function allComments(): HasMany
    {
        return $this->hasMany(FeedComment::class, 'post_id');
    }

    /**
     * Saves / Favorites
     */
    public function saves(): HasMany
    {
        return $this->hasMany(FeedSave::class, 'post_id');
    }

    /**
     * Reports (moderation)
     */
    public function reports(): HasMany
    {
        return $this->hasMany(FeedReport::class, 'post_id');
    }

    /**
     * View tracking
     */
    public function views(): HasMany
    {
        return $this->hasMany(FeedPostView::class, 'post_id');
    }

    // =====================================================================
    // SCOPES
    // =====================================================================

    /**
     * Only approved/visible posts
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Filter by neighborhood
     */
    public function scopeInBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Global feed — no area filter, all public posts
     */
    public function scopeGlobal($query)
    {
        return $query->published()->orderBy('created_at', 'desc');
    }

    /**
     * Posts by a specific author
     */
    public function scopeByAuthor($query, string $authorId, string $authorType)
    {
        return $query->where('author_id', $authorId)->where('author_type', $authorType);
    }

    /**
     * Trending posts — by engagement in last 48 hours
     */
    public function scopeTrending($query)
    {
        return $query->published()
            ->where('created_at', '>=', now()->subHours(48))
            ->orderByRaw('(likes_count * 3 + comments_count * 5 + views_count) DESC');
    }

    /**
     * Featured posts
     */
    public function scopeFeatured($query)
    {
        return $query->published()->where('is_featured', true);
    }

    // =====================================================================
    // HELPERS
    // =====================================================================

    /**
     * Get body in the requested language — fallback to EN then AR then KU
     */
    public function getBody(string $lang = 'en'): ?string
    {
        return match ($lang) {
            'ar'    => $this->body_ar ?? $this->body_en ?? $this->body_ku,
            'ku'    => $this->body_ku ?? $this->body_en ?? $this->body_ar,
            default => $this->body_en ?? $this->body_ar ?? $this->body_ku,
        };
    }

    /**
     * Check if a given author has liked this post
     * Usage: $post->isLikedBy($user->id, 'App\Models\User')
     */
    public function isLikedBy(string $likerId, string $likerType): bool
    {
        return $this->likes()
            ->where('liker_id', $likerId)
            ->where('liker_type', $likerType)
            ->exists();
    }

    /**
     * Check if a given author has saved this post
     */
    public function isSavedBy(string $saverId, string $saverType): bool
    {
        return $this->saves()
            ->where('saver_id', $saverId)
            ->where('saver_type', $saverType)
            ->exists();
    }

    /**
     * Increment views counter — call this when post is viewed
     * The actual feed_post_views row is inserted in batch by PostViewService
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
