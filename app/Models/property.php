<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties'; // Ensure this matches your table name
    protected $primaryKey = 'property_id'; // Specify the primary key if it's not 'id'
    public $incrementing = true; // If the primary key is an auto-incrementing integer

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
    'furnishing_details' => 'array',
    'legal_information' => 'array',
    'seo_metadata' => 'array',
    'nearby_amenities' => 'array',
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
];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
