<?php

// app/Models/Branch.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'city_name_en',
        'city_name_ar',
        'city_name_ku',
        'latitude',
        'longitude',
        'is_active'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean'
    ];

    protected $appends = ['city_name', 'coordinates'];

    // Relationship: A branch has many areas
    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    // Relationship: A branch can have many properties
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    // Relationship: A branch can have many users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scope: Get only active branches
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper: Get localized city name based on app locale
    public function getCityNameAttribute()
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'ar' => $this->city_name_ar,
            'ku' => $this->city_name_ku,
            default => $this->city_name_en,
        };
    }

    // Helper: Get all city names
    public function getCityNamesAttribute()
    {
        return [
            'en' => $this->city_name_en,
            'ar' => $this->city_name_ar,
            'ku' => $this->city_name_ku,
        ];
    }

    // Helper: Get coordinates as array
    public function getCoordinatesAttribute()
    {
        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude
        ];
    }

    // Helper: Get coordinates as string
    public function getCoordinatesStringAttribute()
    {
        return $this->latitude . ', ' . $this->longitude;
    }
}
