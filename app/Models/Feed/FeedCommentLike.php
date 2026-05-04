<?php
// ============================================================
// FeedCommentLike.php
// ============================================================
namespace App\Models\Feed;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedCommentLike extends Model
{
    protected $table = 'feed_comment_likes';

    protected $fillable = [
        'comment_id',
        'liker_id',
        'liker_type',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(FeedComment::class, 'comment_id');
    }

    public function liker(): MorphTo
    {
        return $this->morphTo('liker', 'liker_type', 'liker_id');
    }
}
