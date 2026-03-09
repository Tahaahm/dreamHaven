<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalDataSource extends Model
{
    protected $table = 'external_data_sources';

    protected $fillable = [
        'name',
        'category',
        'subcategory',
        'latitude',
        'longitude',
        'address',
        'area_id',
        'branch_id',
        'impact_weight',
        'source',
        'source_id',
        'source_url',
        'is_verified',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'latitude'      => 'float',
        'longitude'     => 'float',
        'impact_weight' => 'integer',
        'is_verified'   => 'boolean',
        'is_active'     => 'boolean',
        'meta'          => 'array',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Find all POIs within a given radius (km) of a lat/lng point.
     * Uses bounding box approximation for speed (no PostGIS required).
     * 1 degree lat ≈ 111 km. 1 degree lng ≈ 111 * cos(lat) km.
     */
    public function scopeNearby(
        Builder $query,
        float $lat,
        float $lng,
        float $radiusKm = 2.0
    ): Builder {
        $latDelta = $radiusKm / 111.0;
        $lngDelta = $radiusKm / (111.0 * cos(deg2rad($lat)));

        return $query
            ->where('latitude',  '>=', $lat - $latDelta)
            ->where('latitude',  '<=', $lat + $latDelta)
            ->where('longitude', '>=', $lng - $lngDelta)
            ->where('longitude', '<=', $lng + $lngDelta)
            ->where('is_active', true);
    }
}
