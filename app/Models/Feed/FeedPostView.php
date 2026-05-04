<?php


// ============================================================
// FeedPostView.php
// ============================================================
namespace App\Models\Feed;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedPostView extends Model
{
    protected $table = 'feed_post_views';

    // No updated_at needed — views are insert-only
    const UPDATED_AT = null;

    protected $fillable = [
        'post_id',
        'viewer_id',
        'viewer_type',
        'guest_token',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }

    public function viewer(): MorphTo
    {
        return $this->morphTo('viewer', 'viewer_type', 'viewer_id');
    }
}
