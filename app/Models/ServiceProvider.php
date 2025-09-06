<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ServiceProvider extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'category_id',
        'company_name',
        'company_bio',
        'is_verified',
        'profile_image',
        'average_rating',
        'latitude',
        'longitude',
        'city',
        'district',
        'business_type',
        'business_description',
        'years_in_business',
        'completed_projects',
        'phone_number',
        'email_address',
        'website_url',
        'business_hours',
        'company_overview',
        'plan_id',
        'plan_active',
        'plan_expires_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'average_rating' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'years_in_business' => 'integer',
        'completed_projects' => 'integer',
        'business_hours' => 'array',
        'plan_active' => 'boolean',
        'plan_expires_at' => 'datetime',
    ];

    /**
     * Get the category that owns the service provider
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the plan that belongs to the service provider
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(ServiceProviderPlan::class, 'plan_id', 'id');
    }

    /**
     * Get all galleries for this service provider
     */
    public function galleries(): HasMany
    {
        return $this->hasMany(ServiceProviderGallery::class)->orderBy('sort_order');
    }

    /**
     * Get all offerings for this service provider
     */
    public function offerings(): HasMany
    {
        return $this->hasMany(ServiceProviderOffering::class)->orderBy('sort_order');
    }

    /**
     * Get active offerings for this service provider
     */
    public function activeOfferings(): HasMany
    {
        return $this->hasMany(ServiceProviderOffering::class)
            ->where('active', true)
            ->orderBy('sort_order');
    }

    /**
     * Get all reviews for this service provider
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ServiceProviderReview::class)->orderBy('review_date', 'desc');
    }

    /**
     * Get featured reviews for this service provider
     */
    public function featuredReviews(): HasMany
    {
        return $this->hasMany(ServiceProviderReview::class)
            ->where('is_featured', true)
            ->orderBy('review_date', 'desc');
    }

    /**
     * Scope to get only verified providers
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get providers by city
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope to get providers by district
     */
    public function scopeByDistrict($query, $district)
    {
        return $query->where('district', $district);
    }

    /**
     * Scope to get providers with minimum rating
     */
    public function scopeWithMinRating($query, $rating)
    {
        return $query->where('average_rating', '>=', $rating);
    }

    /**
     * Calculate distance from given coordinates (in kilometers)
     */
    public function scopeWithinRadius($query, $latitude, $longitude, $radius)
    {
        $haversine = "(6371 * acos(cos(radians(?))
                     * cos(radians(latitude))
                     * cos(radians(longitude) - radians(?))
                     + sin(radians(?))
                     * sin(radians(latitude))))";

        return $query
            ->selectRaw("{$haversine} AS distance", [$latitude, $longitude, $latitude])
            ->whereRaw("{$haversine} < ?", [$latitude, $longitude, $latitude, $radius])
            ->orderBy('distance');
    }

    /**
     * Scope to get providers with active plans
     */
    public function scopeWithActivePlan($query)
    {
        return $query->where('plan_active', true)
            ->where(function ($q) {
                $q->whereNull('plan_expires_at')
                    ->orWhere('plan_expires_at', '>', now());
            });
    }

    /**
     * Scope to get providers by plan
     */
    public function scopeByPlan($query, $planId)
    {
        return $query->where('plan_id', $planId);
    }

    /**
     * Check if service provider has an active plan
     */
    public function hasActivePlan(): bool
    {
        return $this->plan_active &&
            $this->plan_id &&
            (!$this->plan_expires_at || $this->plan_expires_at->isFuture());
    }

    /**
     * Get remaining days on current plan
     */
    public function remainingPlanDays(): int
    {
        if (!$this->hasActivePlan() || !$this->plan_expires_at) {
            return 0;
        }

        return max(0, now()->diffInDays($this->plan_expires_at, false));
    }

    /**
     * Check if service provider can use specific feature
     */
    public function canUseFeature(string $feature): bool
    {
        if (!$this->hasActivePlan()) {
            return false;
        }

        $plan = $this->plan;
        if (!$plan) {
            return false;
        }

        $features = json_decode($plan->features, true) ?? [];
        return in_array($feature, $features);
    }

    /**
     * Get plan advertisement slots available
     */
    public function getAdvertisementSlots(): int
    {
        if (!$this->hasActivePlan()) {
            return 0;
        }

        $plan = $this->plan;
        return $plan ? $plan->advertisement_slots : 0;
    }

    /**
     * Get plan banner allowance
     */
    public function getBannerAllowance(): int
    {
        if (!$this->hasActivePlan()) {
            return 0;
        }

        $plan = $this->plan;
        return $plan ? $plan->banner : 0;
    }
}