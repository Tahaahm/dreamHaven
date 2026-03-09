<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * PriceZone
 * A geospatial cluster polygon representing a price zone on the map.
 * GeoJSON polygon stored in geojson_polygon field.
 *
 * @property int    $id
 * @property string $zone_name
 * @property string $zone_code
 * @property string $tier           affordable|medium|expensive|luxury
 * @property string $color_hex
 * @property string $geojson_polygon
 * @property float  $centroid_lat
 * @property float  $centroid_lng
 * @property float  $avg_price_per_m2
 * @property int    $property_count
 * @property float  $demand_score
 * @property float  $investment_score
 * @property int    $version
 * @property bool   $is_active
 */
class PriceZone extends Model
{
    protected $table = 'price_zones';

    protected $fillable = [
        'zone_name',
        'zone_code',
        'tier',
        'color_hex',
        'geojson_polygon',
        'bbox_min_lat',
        'bbox_max_lat',
        'bbox_min_lng',
        'bbox_max_lng',
        'centroid_lat',
        'centroid_lng',
        'avg_price_per_m2',
        'min_price_per_m2',
        'max_price_per_m2',
        'avg_total_price',
        'property_count',
        'demand_score',
        'investment_score',
        'algorithm',
        'cluster_id',
        'branch_id',
        'version',
        'is_active',
        'computed_at',
    ];

    protected $casts = [
        'bbox_min_lat'     => 'float',
        'bbox_max_lat'     => 'float',
        'bbox_min_lng'     => 'float',
        'bbox_max_lng'     => 'float',
        'centroid_lat'     => 'float',
        'centroid_lng'     => 'float',
        'avg_price_per_m2' => 'float',
        'min_price_per_m2' => 'float',
        'max_price_per_m2' => 'float',
        'avg_total_price'  => 'float',
        'property_count'   => 'integer',
        'demand_score'     => 'float',
        'investment_score' => 'float',
        'cluster_id'       => 'integer',
        'version'          => 'integer',
        'is_active'        => 'boolean',
        'computed_at'      => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLatestVersion(Builder $query): Builder
    {
        $latest = static::max('version');
        return $query->where('version', $latest);
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByTier(Builder $query, string $tier): Builder
    {
        return $query->where('tier', $tier);
    }

    /**
     * Viewport bounding box filter — only return zones visible on screen.
     */
    public function scopeInViewport(
        Builder $query,
        float $minLat,
        float $maxLat,
        float $minLng,
        float $maxLng
    ): Builder {
        return $query
            ->where('bbox_min_lat', '<=', $maxLat)
            ->where('bbox_max_lat', '>=', $minLat)
            ->where('bbox_min_lng', '<=', $maxLng)
            ->where('bbox_max_lng', '>=', $minLng);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    /**
     * Returns the GeoJSON polygon as a decoded PHP array.
     */
    public function getPolygonArrayAttribute(): array
    {
        return json_decode($this->geojson_polygon, true) ?? [];
    }

    /**
     * Returns tier label for display.
     */
    public function getTierLabelAttribute(): string
    {
        return match ($this->tier) {
            'affordable' => 'Affordable',
            'medium'     => 'Mid-Range',
            'expensive'  => 'Premium',
            'luxury'     => 'Luxury',
            default      => ucfirst($this->tier),
        };
    }

    // ── Static helpers ───────────────────────────────────────────────────────

    /**
     * Deactivate all zones of the current version and activate the new one.
     * Called atomically after a new clustering run completes.
     */
    public static function swapActiveVersion(int $newVersion): void
    {
        static::where('is_active', true)->update(['is_active' => false]);
        static::where('version', $newVersion)->update(['is_active' => true]);
    }

    /**
     * Returns hex color for a given tier.
     */
    public static function colorForTier(string $tier): string
    {
        return match ($tier) {
            'affordable' => '#22C55E',
            'medium'     => '#EAB308',
            'expensive'  => '#F97316',
            'luxury'     => '#EF4444',
            default      => '#6B7280',
        };
    }
}
