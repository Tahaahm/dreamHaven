<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SearchInteraction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'filters',
        'city',
        'property_type',
        'listing_type',
        'results_count',
        'device_id',
        'device_name',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'filters' => 'array', // Automatically converts JSON to PHP array
        'results_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the search.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include searches from a specific city.
     */
    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope a query to only include successful searches (found results).
     */
    public function scopeSuccessful($query)
    {
        return $query->where('results_count', '>', 0);
    }
}
