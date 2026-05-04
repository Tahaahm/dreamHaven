<?php



// ============================================================
// FeedLike.php
// ============================================================
namespace App\Models\Feed;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedLike extends Model
{
    protected $table = 'feed_likes';

    protected $fillable = [
        'post_id',
        'liker_id',
        'liker_type',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }

    /**
     * Who liked it — resolves to User, Agent, or RealEstateOffice
     */
    public function liker(): MorphTo
    {
        return $this->morphTo('liker', 'liker_type', 'liker_id');
    }
}
