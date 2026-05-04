<?php

namespace App\Models\Feed;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedComment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'feed_comments';

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'post_id',
        'author_id',
        'author_type',
        'body_en',
        'body_ar',
        'body_ku',
        'parent_id',    // null = top-level, UUID = reply
        'likes_count',
        'status',
    ];

    protected $casts = [
        'likes_count' => 'integer',
    ];

    // =====================================================================
    // RELATIONSHIPS
    // =====================================================================

    /**
     * The post this comment belongs to
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }

    /**
     * Polymorphic author — User / Agent / RealEstateOffice
     */
    public function author(): MorphTo
    {
        return $this->morphTo('author', 'author_type', 'author_id');
    }

    /**
     * Parent comment (if this is a reply)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(FeedComment::class, 'parent_id');
    }

    /**
     * Replies to this comment (one level only)
     */
    public function replies(): HasMany
    {
        return $this->hasMany(FeedComment::class, 'parent_id')
            ->where('status', 'approved')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Likes on this comment
     */
    public function likes(): HasMany
    {
        return $this->hasMany(FeedCommentLike::class, 'comment_id');
    }

    // =====================================================================
    // SCOPES
    // =====================================================================

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    // =====================================================================
    // HELPERS
    // =====================================================================

    public function getBody(string $lang = 'en'): ?string
    {
        return match ($lang) {
            'ar'    => $this->body_ar ?? $this->body_en ?? $this->body_ku,
            'ku'    => $this->body_ku ?? $this->body_en ?? $this->body_ar,
            default => $this->body_en ?? $this->body_ar ?? $this->body_ku,
        };
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function isLikedBy(string $likerId, string $likerType): bool
    {
        return $this->likes()
            ->where('liker_id', $likerId)
            ->where('liker_type', $likerType)
            ->exists();
    }
}
