<?php

namespace App\Models\Feed;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedPostTag extends Model
{
    protected $table = 'feed_post_tags';

    protected $fillable = [
        'post_id',
        'tag',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }
}
