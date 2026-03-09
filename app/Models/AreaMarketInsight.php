<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AreaMarketInsight
 * Pre-computed aggregated market metrics per area.
 * One row per area — upserted every 6 hours by ComputeAreaInsightsJob.
 *
 * @property int         $id
 * @property int         $area_id
 * @property float       $average_price
 * @property float       $median_price
 * @property float       $min_price
 * @property float       $max_price
 * @property float       $average_price_per_m2
 * @property float       $median_price_per_m2
 * @property int         $listing_count
 * @property int         $active_listings
 * @property int         $sold_listings
 * @property float       $average_days_on_market
 * @property float       $demand_score
 * @property float       $liquidity_score
 * @property float       $price_growth_7d
 * @property float       $price_growth_30d
 * @property float       $price_growth_90d
 * @property float       $price_growth_1y
 * @property float       $investment_score
 * @property string      $price_tier
 * @property string      $currency
 * @property \Carbon\Carbon|null $computed_at
 */
class AreaMarketInsight extends Model
{
    protected $table = 'area_market_insights';

    protected $fillable = [
        'area_id',
        'average_price',
        'median_price',
        'min_price',
        'max_price',
        'average_price_per_m2',
        'median_price_per_m2',
        'listing_count',
        'active_listings',
        'sold_listings',
        'average_days_on_market',
        'demand_score',
        'liquidity_score',
        'price_growth_7d',
        'price_growth_30d',
        'price_growth_90d',
        'price_growth_1y',
        'investment_score',
        'price_tier',
        'currency',
        'computed_at',
    ];

    protected $casts = [
        'average_price'          => 'float',
        'median_price'           => 'float',
        'min_price'              => 'float',
        'max_price'              => 'float',
        'average_price_per_m2'   => 'float',
        'median_price_per_m2'    => 'float',
        'listing_count'          => 'integer',
        'active_listings'        => 'integer',
        'sold_listings'          => 'integer',
        'average_days_on_market' => 'float',
        'demand_score'           => 'float',
        'liquidity_score'        => 'float',
        'price_growth_7d'        => 'float',
        'price_growth_30d'       => 'float',
        'price_growth_90d'       => 'float',
        'price_growth_1y'        => 'float',
        'investment_score'       => 'float',
        'computed_at'            => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeByTier($query, string $tier)
    {
        return $query->where('price_tier', $tier);
    }

    public function scopeTopInvestment($query, int $limit = 10)
    {
        return $query->orderByDesc('investment_score')->limit($limit);
    }

    public function scopeHighDemand($query, float $threshold = 70.0)
    {
        return $query->where('demand_score', '>=', $threshold);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    /**
     * Human-readable price tier label for the Flutter UI.
     */
    public function getTierLabelAttribute(): string
    {
        return match ($this->price_tier) {
            'affordable' => 'Affordable',
            'medium'     => 'Mid-Range',
            'expensive'  => 'Premium',
            'luxury'     => 'Luxury',
            default      => ucfirst($this->price_tier),
        };
    }

    /**
     * Map color hex for this tier.
     */
    public function getTierColorAttribute(): string
    {
        return match ($this->price_tier) {
            'affordable' => '#22C55E',  // green
            'medium'     => '#EAB308',  // yellow
            'expensive'  => '#F97316',  // orange
            'luxury'     => '#EF4444',  // red
            default      => '#6B7280',
        };
    }

    /**
     * Trend direction label based on 30d growth.
     */
    public function getTrendDirectionAttribute(): string
    {
        if ($this->price_growth_30d > 2)  return 'rising';
        if ($this->price_growth_30d < -2) return 'declining';
        return 'stable';
    }

    /**
     * Investment grade A+/A/B/C/D based on score.
     */
    public function getInvestmentGradeAttribute(): string
    {
        return match (true) {
            $this->investment_score >= 85 => 'A+',
            $this->investment_score >= 70 => 'A',
            $this->investment_score >= 55 => 'B',
            $this->investment_score >= 40 => 'C',
            default                       => 'D',
        };
    }
}
