<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'developer_id',
        'developer_type',
        'name',
        'description',
        'slug',
        'images',
        'logo_url',
        'cover_image_url',
        'project_type',
        'project_category',
        'locations',
        'address_details',
        'full_address',
        'latitude',
        'longitude',
        'total_area',
        'built_area',
        'total_units',
        'available_units',
        'unit_types',
        'total_floors',
        'buildings_count',
        'building_details',
        'year_built',
        'completion_year',
        'construction_details',
        'construction_materials',
        'finishing_options',
        'quality_certifications',
        'customization_available',
        'customization_options',
        'construction_updates',
        'architect',
        'contractor',
        'price_range',
        'pricing_details',
        'pricing_currency',
        'payment_plans',
        'down_payment_percentage',
        'financing_options',
        'installment_available',
        'installment_months',
        'early_bird_offers',
        'project_features',
        'nearby_amenities',
        'transportation',
        'facilities',
        'community_features',
        'safety_security',
        'utilities',
        'parking_details',
        'virtual_tour_url',
        'floor_plans',
        'brochures',
        'videos',
        'additional_media',
        'status',
        'sales_status',
        'completion_percentage',
        'phases',
        'launch_date',
        'construction_start_date',
        'expected_completion_date',
        'handover_date',
        'approvals',
        'certifications',
        'legal_information',
        'rera_registration',
        'sales_team',
        'sales_office_address',
        'sales_office_timings',
        'marketing_campaigns',
        'lead_sources',
        'target_audience',
        'investment_highlights',
        'expected_appreciation',
        'rental_yield_potential',
        'resale_potential',
        'market_comparison',
        'developer_info',
        'developer_portfolio',
        'developer_awards',
        'developer_rating',
        'contact_info',
        'warranty_details',
        'after_sales_service',
        'handover_process',
        'maintenance_service_available',
        'maintenance_charges',
        'green_features',
        'environmental_certifications',
        'eco_friendly',
        'energy_efficiency',
        'waste_management',
        'competing_projects',
        'unique_selling_points',
        'market_positioning',
        'price_vs_market',
        'project_milestones',
        'risk_factors',
        'change_log',
        'delayed',
        'delay_months',
        'delay_reason',
        'seo_metadata',
        'marketing_highlights',
        'is_featured',
        'is_premium',
        'is_hot_project',
        'awards_recognitions',
        'views',
        'view_analytics',
        'inquiries_count',
        'qualified_leads_count',
        'site_visits_count',
        'bookings_count',
        'conversion_rate',
        'favorites_count',
        'rating',
        'reviews_count',
        'units_sold',
        'sales_velocity',
        'average_selling_price',
        'sales_milestones',
        'sales_by_unit_type',
        'is_boosted',
        'boost_start_date',
        'boost_end_date',
        'is_active',
        'published'
    ];

    protected $casts = [
        'id' => 'string',
        'name' => 'array',
        'description' => 'array',
        'images' => 'array',
        'project_category' => 'array',
        'locations' => 'array',
        'address_details' => 'array',
        'unit_types' => 'array',
        'building_details' => 'array',
        'construction_details' => 'array',
        'construction_materials' => 'array',
        'finishing_options' => 'array',
        'quality_certifications' => 'array',
        'customization_available' => 'boolean',
        'customization_options' => 'array',
        'construction_updates' => 'array',
        'price_range' => 'array',
        'pricing_details' => 'array',
        'payment_plans' => 'array',
        'down_payment_percentage' => 'decimal:2',
        'financing_options' => 'array',
        'installment_available' => 'boolean',
        'early_bird_offers' => 'array',
        'project_features' => 'array',
        'nearby_amenities' => 'array',
        'transportation' => 'array',
        'facilities' => 'array',
        'community_features' => 'array',
        'safety_security' => 'array',
        'utilities' => 'array',
        'parking_details' => 'array',
        'floor_plans' => 'array',
        'brochures' => 'array',
        'videos' => 'array',
        'additional_media' => 'array',
        'phases' => 'array',
        'launch_date' => 'date',
        'construction_start_date' => 'date',
        'expected_completion_date' => 'date',
        'handover_date' => 'date',
        'approvals' => 'array',
        'certifications' => 'array',
        'legal_information' => 'array',
        'sales_team' => 'array',
        'sales_office_timings' => 'array',
        'marketing_campaigns' => 'array',
        'lead_sources' => 'array',
        'target_audience' => 'array',
        'investment_highlights' => 'array',
        'expected_appreciation' => 'decimal:2',
        'rental_yield_potential' => 'array',
        'resale_potential' => 'array',
        'market_comparison' => 'array',
        'developer_info' => 'array',
        'developer_portfolio' => 'array',
        'developer_awards' => 'array',
        'developer_rating' => 'decimal:2',
        'contact_info' => 'array',
        'warranty_details' => 'array',
        'after_sales_service' => 'array',
        'handover_process' => 'array',
        'maintenance_service_available' => 'boolean',
        'maintenance_charges' => 'array',
        'green_features' => 'array',
        'environmental_certifications' => 'array',
        'eco_friendly' => 'boolean',
        'energy_efficiency' => 'array',
        'waste_management' => 'array',
        'competing_projects' => 'array',
        'unique_selling_points' => 'array',
        'market_positioning' => 'array',
        'price_vs_market' => 'decimal:2',
        'project_milestones' => 'array',
        'risk_factors' => 'array',
        'change_log' => 'array',
        'delayed' => 'boolean',
        'seo_metadata' => 'array',
        'marketing_highlights' => 'array',
        'is_featured' => 'boolean',
        'is_premium' => 'boolean',
        'is_hot_project' => 'boolean',
        'awards_recognitions' => 'array',
        'view_analytics' => 'array',
        'conversion_rate' => 'decimal:2',
        'rating' => 'decimal:2',
        'sales_velocity' => 'decimal:2',
        'average_selling_price' => 'decimal:2',
        'sales_milestones' => 'array',
        'sales_by_unit_type' => 'array',
        'is_boosted' => 'boolean',
        'boost_start_date' => 'datetime',
        'boost_end_date' => 'datetime',
        'is_active' => 'boolean',
        'published' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'total_area' => 'decimal:2',
        'built_area' => 'decimal:2'
    ];

    // Relationships
    public function developer(): MorphTo
    {
        return $this->morphTo();
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(ProjectInquiry::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProjectReview::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(ProjectFavorite::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function scopeHot($query)
    {
        return $query->where('is_hot_project', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('project_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySalesStatus($query, $status)
    {
        return $query->where('sales_status', $status);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->whereJsonContains('locations', $location);
    }

    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->where(function ($q) use ($minPrice, $maxPrice) {
            $q->whereJsonExtract('price_range', '$.min', '>=', $minPrice)
                ->whereJsonExtract('price_range', '$.max', '<=', $maxPrice);
        });
    }

    public function scopeEcoFriendly($query)
    {
        return $query->where('eco_friendly', true);
    }

    // Accessors
    public function getFormattedPriceRangeAttribute()
    {
        $priceRange = $this->price_range;
        if (!$priceRange || !isset($priceRange['min']) || !isset($priceRange['max'])) {
            return null;
        }

        $currency = $this->pricing_currency;
        $min = number_format($priceRange['min']);
        $max = number_format($priceRange['max']);

        return "{$currency} {$min} - {$max}";
    }

    public function getMainImageAttribute()
    {
        if ($this->cover_image_url) {
            return $this->cover_image_url;
        }

        if ($this->images && count($this->images) > 0) {
            return $this->images[0];
        }

        return null;
    }

    public function getCompletionStatusAttribute()
    {
        $percentage = $this->completion_percentage;

        if ($percentage == 0) return 'Not Started';
        if ($percentage < 25) return 'Early Stage';
        if ($percentage < 50) return 'Foundation Complete';
        if ($percentage < 75) return 'Structure Complete';
        if ($percentage < 100) return 'Finishing Work';

        return 'Completed';
    }

    // Mutators
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = Str::slug($value);
    }

    // Helper methods
    public function incrementViews()
    {
        $this->increment('views');
    }

    public function incrementInquiries()
    {
        $this->increment('inquiries_count');
    }

    public function incrementFavorites()
    {
        $this->increment('favorites_count');
    }

    public function updateRating()
    {
        $avgRating = $this->reviews()->avg('rating');
        $this->update([
            'rating' => round($avgRating, 2),
            'reviews_count' => $this->reviews()->count()
        ]);
    }

    public function isAvailableForSale()
    {
        return $this->is_active &&
            $this->published &&
            in_array($this->sales_status, ['launched', 'selling']) &&
            $this->available_units > 0;
    }

    public function getDaysUntilCompletion()
    {
        if (!$this->expected_completion_date) {
            return null;
        }

        return now()->diffInDays($this->expected_completion_date, false);
    }
}
