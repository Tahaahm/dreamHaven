<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ─────────────────────────────────────────────────────────────────────────────
// InvestmentScore
// ─────────────────────────────────────────────────────────────────────────────

class InvestmentScore extends Model
{
    protected $table = 'investment_scores';

    protected $fillable = [
        'area_id',
        'investment_score',
        'price_growth_score',
        'demand_score',
        'supply_score',
        'liquidity_score',
        'development_score',
        'weight_price_growth',
        'weight_demand',
        'weight_supply',
        'weight_liquidity',
        'weight_development',
        'grade',
        'recommendation',
        'price_growth_90d',
        'listing_velocity',
        'avg_days_on_market',
        'poi_count',
        'active_listing_count',
        'trend',
        'analysis_summary',
        'risk_flags',
        'positive_signals',
        'computed_at',
    ];

    protected $casts = [
        'investment_score'    => 'float',
        'price_growth_score'  => 'float',
        'demand_score'        => 'float',
        'supply_score'        => 'float',
        'liquidity_score'     => 'float',
        'development_score'   => 'float',
        'weight_price_growth' => 'float',
        'weight_demand'       => 'float',
        'weight_supply'       => 'float',
        'weight_liquidity'    => 'float',
        'weight_development'  => 'float',
        'price_growth_90d'    => 'float',
        'listing_velocity'    => 'float',
        'avg_days_on_market'  => 'float',
        'poi_count'           => 'integer',
        'active_listing_count' => 'integer',
        'risk_flags'          => 'array',
        'positive_signals'    => 'array',
        'computed_at'         => 'datetime',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function scopeStrongBuy(Builder $query): Builder
    {
        return $query->where('recommendation', 'strong_buy');
    }

    public function scopeTopScores(Builder $query, int $limit = 10): Builder
    {
        return $query->orderByDesc('investment_score')->limit($limit);
    }

    public function scopeRising(Builder $query): Builder
    {
        return $query->where('trend', 'rising');
    }

    /**
     * Recommendation label with emoji for Flutter UI.
     */
    public function getRecommendationLabelAttribute(): string
    {
        return match ($this->recommendation) {
            'strong_buy' => '🚀 Strong Buy',
            'buy'        => '✅ Buy',
            'hold'       => '⏸️ Hold',
            'avoid'      => '🚫 Avoid',
            default      => ucfirst($this->recommendation),
        };
    }

    /**
     * Score breakdown as array for Flutter radar/chart widget.
     */
    public function getScoreBreakdownAttribute(): array
    {
        return [
            ['label' => 'Price Growth', 'value' => $this->price_growth_score, 'weight' => $this->weight_price_growth],
            ['label' => 'Demand',       'value' => $this->demand_score,       'weight' => $this->weight_demand],
            ['label' => 'Supply',       'value' => $this->supply_score,       'weight' => $this->weight_supply],
            ['label' => 'Liquidity',    'value' => $this->liquidity_score,    'weight' => $this->weight_liquidity],
            ['label' => 'Development',  'value' => $this->development_score,  'weight' => $this->weight_development],
        ];
    }
}
