<?php

namespace App\Models\Feed;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedFollow extends Model
{
    protected $table = 'feed_follows';

    protected $fillable = [
        'follower_id',
        'follower_type',
        'followee_id',
        'followee_type',
        'status',
    ];

    // ── Who is following ──────────────────────────────────────────────────

    public function follower(): MorphTo
    {
        return $this->morphTo('follower', 'follower_type', 'follower_id');
    }

    // ── Who is being followed ─────────────────────────────────────────────

    public function followee(): MorphTo
    {
        return $this->morphTo('followee', 'followee_type', 'followee_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
