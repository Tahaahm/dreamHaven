<?php

// app/Models/Area.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'area_name_en',
        'area_name_ar',
        'area_name_ku',
        'latitude',
        'longitude',
        'is_active'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean'
    ];

    protected $appends = ['area_name', 'full_location', 'coordinates'];

    // Relationship: An area belongs to a branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Relationship: An area can have many properties
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    // Relationship: An area can have many users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scope: Get only active areas
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: Get areas by branch
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    // Helper: Get localized area name based on app locale
    public function getAreaNameAttribute()
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'ar' => $this->area_name_ar,
            'ku' => $this->area_name_ku,
            default => $this->area_name_en,
        };
    }

    // Helper: Get all area names
    public function getAreaNamesAttribute()
    {
        return [
            'en' => $this->area_name_en,
            'ar' => $this->area_name_ar,
            'ku' => $this->area_name_ku,
        ];
    }

    // Helper: Get full location name
    public function getFullLocationAttribute()
    {
        return $this->area_name . ', ' . $this->branch->city_name;
    }

    // Helper: Get coordinates as array
    public function getCoordinatesAttribute()
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude
        ];
    }

    // Helper: Get coordinates as string
    public function getCoordinatesStringAttribute()
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        return $this->latitude . ', ' . $this->longitude;
    }
}
