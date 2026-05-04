<?php
// ============================================================
// FeedPostMedia.php
// ============================================================
namespace App\Models\Feed;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedPostMedia extends Model
{
    protected $table = 'feed_post_media';

    protected $fillable = [
        'post_id',
        'media_type',       // image | video
        'url',
        'thumbnail_url',
        'duration_seconds', // video only
        'mime_type',
        'file_size_bytes',
        'sort_order',
        'alt_text',
    ];

    protected $casts = [
        'sort_order'       => 'integer',
        'duration_seconds' => 'integer',
        'file_size_bytes'  => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }

    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }
}
