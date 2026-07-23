<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'owner_id',
        'owner_type',
        'name',
        'description',
        'images',
        'availability',
        'type',
        'area',
        'furnished',
        'price',
        'listing_type',
        'rental_period',
        'rooms',
        'features',
        'amenities',
        'locations',
        'address_details',
        'address',
        'floor_number',
        'floor_details',
        'year_built',
        'construction_details',
        'energy_rating',
        'energy_details',
        'electricity',
        'water',
        'internet',
        'virtual_tour_url',
        'virtual_tour_details',
        'floor_plan_url',
        'additional_media',
        'verified',
        'is_active',
        'published',
        'status',
        'views',
        'view_analytics',
        'favorites_count',
        'favorites_analytics',
        'rating',
        'is_boosted',
        'boost_start_date',
        'boost_end_date',
        'legal_information',
        'investment_analysis',
        'furnishing_details',
        'seo_metadata',
        'nearby_amenities',
    ];

    protected $casts = [
        'id' => 'string',
        'name' => 'array',
        'description' => 'array',
        'images' => 'array',
        'availability' => 'array',
        'type' => 'array',
        'price' => 'array',
        'rooms' => 'array',
        'features' => 'array',
        'amenities' => 'array',
        'locations' => 'array',
        'address_details' => 'array',
        'floor_details' => 'array',
        'construction_details' => 'array',
        'energy_details' => 'array',
        'virtual_tour_details' => 'array',
        'additional_media' => 'array',
        'view_analytics' => 'array',
        'favorites_analytics' => 'array',
        'furnished' => 'boolean',
        'electricity' => 'boolean',
        'water' => 'boolean',
        'internet' => 'boolean',
        'verified' => 'boolean',
        'is_active' => 'boolean',
        'published' => 'boolean',
        'is_boosted' => 'boolean',
        'views' => 'integer',
        'favorites_count' => 'integer',
        'rating' => 'decimal:2',

        // Read-only, DB-generated mirrors of values already inside price/rooms/type
        // (see migration add_indexed_generated_columns_to_properties_table).
        // Not listed in $fillable — MySQL computes these, the app never writes them.
        'price_usd' => 'decimal:2',
        'price_iqd' => 'decimal:2',
        'bedrooms_count' => 'integer',
        'bathrooms_count' => 'integer',
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    /**
     * is_active + published together — the "visible to end users" gate
     * repeated across nearly every read endpoint in PropertyController.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_active', true)->where('published', true);
    }

    /**
     * Exclude a caller-supplied set of statuses. Kept generic (rather than
     * hard-coded) because different endpoints exclude slightly different
     * status lists (e.g. index() excludes only cancelled/pending, while
     * getRecommended()/getRecent()/getPopular() also exclude sold/rented).
     * Passing the exact same list each caller already used preserves
     * each endpoint's existing behavior exactly.
     */
    public function scopeExcludingStatuses($query, array $statuses)
    {
        return $query->whereNotIn('status', $statuses);
    }

    /**
     * Price filter on the indexed, DB-generated price_usd/price_iqd column
     * instead of a raw JSON_EXTRACT scan. Mirrors the exact semantics the
     * controller previously implemented by hand: a currency's price only
     * counts when it is present and greater than zero, so a $0/missing
     * price in the requested currency never matches a min/max filter.
     */
    public function scopePriceBetween($query, string $currency, $min = null, $max = null)
    {
        $column = strtolower($currency) === 'iqd' ? 'price_iqd' : 'price_usd';

        if ($min !== null && (float) $min > 0) {
            $query->where($column, '>=', $min)->where($column, '>', 0);
        }

        if ($max !== null && (float) $max > 0) {
            $query->where($column, '<=', $max)->where($column, '>', 0);
        }

        return $query;
    }

    /**
     * Bedroom/bathroom count filter on the indexed generated columns.
     * $orMore mirrors the app's "5+" bedrooms_plus / bathrooms_plus option.
     */
    public function scopeBedroomCount($query, int $count, bool $orMore = false)
    {
        return $orMore
            ? $query->where('bedrooms_count', '>=', $count)
            : $query->where('bedrooms_count', $count);
    }

    public function scopeBathroomCount($query, int $count, bool $orMore = false)
    {
        return $orMore
            ? $query->where('bathrooms_count', '>=', $count)
            : $query->where('bathrooms_count', $count);
    }

    /**
     * Exact property-type-category match on the indexed generated column
     * (already lower-cased at write time, matching the controller's
     * previous LOWER(JSON_UNQUOTE(...)) = ? comparison).
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('property_type_category', strtolower($category));
    }

    public function isBoosted()
    {
        return $this->is_boosted;
    }


    public function interactions()
    {
        return $this->hasMany(\App\Models\UserPropertyInteraction::class, 'property_id');
    }

    // Add this new method for view interactions
    public function viewInteractions()
    {
        return $this->hasMany(\App\Models\UserPropertyInteraction::class, 'property_id')
            ->where('interaction_type', 'impression'); // Changed from 'view' to 'impression'
    }
    public function viewers()
    {
        return $this->hasManyThrough(
            \App\Models\User::class,
            \App\Models\UserPropertyInteraction::class,
            'property_id', // Foreign key on interactions table
            'id',          // Foreign key on users table
            'id',          // Local key on properties table
            'user_id'      // Local key on interactions table
        )->where('interaction_type', 'view')
            ->distinct();
    }
}
