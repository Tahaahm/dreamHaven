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
