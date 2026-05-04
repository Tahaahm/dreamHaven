<?php


// ============================================================
// FeedReport.php
// ============================================================
namespace App\Models\Feed;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedReport extends Model
{
    protected $table = 'feed_reports';

    protected $fillable = [
        'post_id',
        'reporter_id',
        'reporter_type',
        'reason',
        'notes',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }

    public function reporter(): MorphTo
    {
        return $this->morphTo('reporter', 'reporter_type', 'reporter_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
