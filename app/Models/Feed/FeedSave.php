<?php



// ============================================================
// FeedSave.php  (Favorites / Bookmarks on feed posts)
// ============================================================
namespace App\Models\Feed;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedSave extends Model
{
    protected $table = 'feed_saves';

    protected $fillable = [
        'post_id',
        'saver_id',
        'saver_type',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }

    /**
     * Who saved it — resolves to User, Agent, or RealEstateOffice
     */
    public function saver(): MorphTo
    {
        return $this->morphTo('saver', 'saver_type', 'saver_id');
    }
}
