<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ─────────────────────────────────────────────────────────────────────────────
// HeatmapTile
// ─────────────────────────────────────────────────────────────────────────────

class HeatmapTile extends Model
{
    protected $table = 'heatmap_tiles';

    protected $fillable = [
        'latitude',
        'longitude',
        'cell_min_lat',
        'cell_max_lat',
        'cell_min_lng',
        'cell_max_lng',
        'heatmap_type',
        'weight',
        'raw_value',
        'property_count',
        'avg_price',
        'avg_price_per_m2',
        'branch_id',
        'version',
        'is_active',
        'computed_at',
    ];

    protected $casts = [
        'latitude'        => 'float',
        'longitude'       => 'float',
        'cell_min_lat'    => 'float',
        'cell_max_lat'    => 'float',
        'cell_min_lng'    => 'float',
        'cell_max_lng'    => 'float',
        'weight'          => 'float',
        'raw_value'       => 'float',
        'property_count'  => 'integer',
        'avg_price'       => 'float',
        'avg_price_per_m2' => 'float',
        'version'         => 'integer',
        'is_active'       => 'boolean',
        'computed_at'     => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('heatmap_type', $type);
    }

    /**
     * Viewport filter — only return tiles visible on current map bounds.
     */
    public function scopeInViewport(
        Builder $query,
        float $minLat,
        float $maxLat,
        float $minLng,
        float $maxLng
    ): Builder {
        return $query
            ->where('latitude', '>=', $minLat)
            ->where('latitude', '<=', $maxLat)
            ->where('longitude', '>=', $minLng)
            ->where('longitude', '<=', $maxLng);
    }

    /**
     * Returns tile as Flutter-ready weighted point array.
     */
    public function toHeatmapPoint(): array
    {
        return [
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
            'weight'    => $this->weight,
        ];
    }

    public static function swapActiveVersion(int $newVersion, string $type): void
    {
        static::where('is_active', true)->where('heatmap_type', $type)
            ->update(['is_active' => false]);
        static::where('version', $newVersion)->where('heatmap_type', $type)
            ->update(['is_active' => true]);
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// PropertyValuation
// ─────────────────────────────────────────────────────────────────────────────

class PropertyValuation extends Model
{
    protected $table = 'property_valuations';

    protected $fillable = [
        'property_id',
        'predicted_price',
        'predicted_price_per_m2',
        'actual_price',
        'actual_price_per_m2',
        'actual_area_m2',
        'overprice_percent',
        'underprice_percent',
        'predicted_price_low',
        'predicted_price_high',
        'confidence_score',
        'verdict',
        'feature_inputs',
        'model_version',
        'algorithm',
        'comparable_property_ids',
        'status',
        'error_message',
        'predicted_at',
    ];

    protected $casts = [
        'predicted_price'         => 'float',
        'predicted_price_per_m2'  => 'float',
        'actual_price'            => 'float',
        'actual_price_per_m2'     => 'float',
        'actual_area_m2'          => 'float',
        'overprice_percent'       => 'float',
        'underprice_percent'      => 'float',
        'predicted_price_low'     => 'float',
        'predicted_price_high'    => 'float',
        'confidence_score'        => 'float',
        'feature_inputs'          => 'array',
        'comparable_property_ids' => 'array',
        'predicted_at'            => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeLatestVersion(Builder $query): Builder
    {
        return $query->orderByDesc('predicted_at');
    }

    /**
     * Returns human-readable verdict text for Flutter UI.
     */
    public function getVerdictLabelAttribute(): string
    {
        return match ($this->verdict) {
            'great_deal'  => '🔥 Great Deal',
            'underpriced' => '✅ Below Market',
            'fair_value'  => '⚖️ Fair Price',
            'overpriced'  => '⚠️ Overpriced',
            default       => $this->verdict,
        };
    }

    /**
     * Returns verdict color for Flutter UI badge.
     */
    public function getVerdictColorAttribute(): string
    {
        return match ($this->verdict) {
            'great_deal'  => '#16A34A',
            'underpriced' => '#22C55E',
            'fair_value'  => '#3B82F6',
            'overpriced'  => '#EF4444',
            default       => '#6B7280',
        };
    }

    /**
     * Confidence level label.
     */
    public function getConfidenceLabelAttribute(): string
    {
        return match (true) {
            $this->confidence_score >= 0.85 => 'High',
            $this->confidence_score >= 0.65 => 'Medium',
            default                         => 'Low',
        };
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// MarketTrend
// ─────────────────────────────────────────────────────────────────────────────

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
