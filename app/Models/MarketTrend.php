<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketTrend extends Model
{
    protected $table = 'market_trends';

    protected $fillable = [
        'area_id',
        'snapshot_date',
        'avg_price',
        'median_price',
        'avg_price_per_m2',
        'min_price',
        'max_price',
        'listing_count',
        'new_listings',
        'removed_listings',
        'demand_score',
        'liquidity_score',
        'price_change_vs_yesterday',
        'price_by_type',
        'count_by_listing_type',
        'period_type',
    ];

    protected $casts = [
        'snapshot_date'             => 'date',
        'avg_price'                 => 'float',
        'median_price'              => 'float',
        'avg_price_per_m2'          => 'float',
        'min_price'                 => 'float',
        'max_price'                 => 'float',
        'listing_count'             => 'integer',
        'new_listings'              => 'integer',
        'removed_listings'          => 'integer',
        'demand_score'              => 'float',
        'liquidity_score'           => 'float',
        'price_change_vs_yesterday' => 'float',
        'price_by_type'             => 'array',
        'count_by_listing_type'     => 'array',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function scopeForArea(Builder $query, int $areaId): Builder
    {
        return $query->where('area_id', $areaId);
    }

    public function scopeLastDays(Builder $query, int $days): Builder
    {
        return $query->where('snapshot_date', '>=', now()->subDays($days)->toDateString());
    }

    public function scopeDaily(Builder $query): Builder
    {
        return $query->where('period_type', 'daily');
    }

    public function scopeMonthly(Builder $query): Builder
    {
        return $query->where('period_type', 'monthly');
    }

    /**
     * Compute growth % between first and last snapshot in a collection.
     * Used by the API to return period growth rate.
     */
    public static function computeGrowthPercent(
        float $firstPrice,
        float $lastPrice
    ): float {
        if ($firstPrice <= 0) return 0.0;
        return round((($lastPrice - $firstPrice) / $firstPrice) * 100, 2);
    }
}
