<?php


// ============================================================
// NeighborhoodFeed.php
// ============================================================
namespace App\Models\Feed;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NeighborhoodFeed extends Model
{
    protected $table = 'neighborhood_feeds';

    protected $fillable = [
        'branch_id',
        'period',
        'period_type',
        'total_posts',
        'total_views',
        'total_likes',
        'total_comments',
        'active_posters',
        'avg_price_usd',
        'avg_price_per_m2_usd',
        'price_change_pct',
        'total_listings',
        'trending_score',
        'calculated_at',
    ];

    protected $casts = [
        'avg_price_usd'        => 'decimal:2',
        'avg_price_per_m2_usd' => 'decimal:2',
        'price_change_pct'     => 'decimal:2',
        'total_posts'          => 'integer',
        'total_views'          => 'integer',
        'total_likes'          => 'integer',
        'total_comments'       => 'integer',
        'active_posters'       => 'integer',
        'total_listings'       => 'integer',
        'trending_score'       => 'integer',
        'calculated_at'        => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeCurrentMonth($query)
    {
        return $query->where('period', now()->format('Y-m'))
            ->where('period_type', 'monthly');
    }

    public function scopeCurrentWeek($query)
    {
        return $query->where('period', now()->format('Y-\WW'))
            ->where('period_type', 'weekly');
    }

    public function scopeTrending($query, int $limit = 10)
    {
        return $query->currentMonth()
            ->orderBy('trending_score', 'desc')
            ->limit($limit);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Is price trending up or down?
     */
    public function getPriceTrendLabel(): string
    {
        if ($this->price_change_pct === null) return 'stable';
        if ($this->price_change_pct > 2)      return 'rising';
        if ($this->price_change_pct < -2)     return 'falling';
        return 'stable';
    }

    /**
     * Recalculate trending score
     * Formula: (views * 1) + (likes * 3) + (comments * 5) + (posts * 2)
     */
    public function recalculateTrendingScore(): void
    {
        $score = ($this->total_views * 1)
            + ($this->total_likes * 3)
            + ($this->total_comments * 5)
            + ($this->total_posts * 2);

        $this->update(['trending_score' => $score]);
    }
}
