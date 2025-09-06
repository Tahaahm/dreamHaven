<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Sanctum\HasApiTokens;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties';

    protected $fillable = [
        'id',
        'owner_id',
        'owner_type',

        // Basic information
        'name',
        'description',
        'images',
        'availability',
        'type',
        'area',
        'furnished',
        'furnishing_details',

        // Pricing
        'price',
        'listing_type',
        'rental_period',

        // Structure
        'rooms',
        'features',
        'amenities',

        // Location
        'locations',
        'address_details',
        'address',

        // Building details
        'floor_number',
        'floor_details',
        'year_built',
        'construction_details',

        // Energy and utilities
        'energy_rating',
        'energy_details',
        'electricity',
        'water',
        'internet',

        // Media
        'virtual_tour_url',
        'virtual_tour_details',
        'floor_plan_url',
        'additional_media',

        // Status and verification
        'verified',
        'verification_details',
        'is_active',
        'published',
        'status',

        // Analytics
        'views',
        'view_analytics',
        'favorites_count',
        'favorites_analytics',
        'rating',

        // Promotion
        'is_boosted',
        'boost_start_date',
        'boost_end_date',

        // Additional data
        'legal_information',
        'investment_analysis',
        'seo_metadata',
        'nearby_amenities',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'images' => 'array',
        'availability' => 'array',
        'type' => 'array',
        'area' => 'decimal:2',
        'rooms' => 'array',
        'furnished' => 'boolean',
        'furnishing_details' => 'array',
        'price' => 'array',
        'features' => 'array',
        'amenities' => 'array',
        'locations' => 'array',
        'address_details' => 'array',
        'verified' => 'boolean',
        'verification_details' => 'array',
        'is_active' => 'boolean',
        'published' => 'boolean',
        'views' => 'integer',
        'view_analytics' => 'array',
        'favorites_count' => 'integer',
        'favorites_analytics' => 'array',
        'floor_number' => 'integer',
        'floor_details' => 'array',
        'year_built' => 'integer',
        'construction_details' => 'array',
        'energy_details' => 'array',
        'electricity' => 'boolean',
        'water' => 'boolean',
        'internet' => 'boolean',
        'nearby_amenities' => 'array',
        'virtual_tour_details' => 'array',
        'additional_media' => 'array',
        'legal_information' => 'array',
        'investment_analysis' => 'array',
        'seo_metadata' => 'array',
        'rating' => 'decimal:2',
        'is_boosted' => 'boolean',
        'boost_start_date' => 'datetime',
        'boost_end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Disable auto-incrementing since we use custom IDs
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Handle polymorphic relationship with simplified owner_type
     */
    public function owner()
    {
        return $this->morphTo(__FUNCTION__, 'owner_type', 'owner_id');
    }

    /**
     * Mutator to convert simple owner_type to full model class
     */
    protected function ownerType(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->getSimpleOwnerType($value),
            set: fn($value) => $this->getFullOwnerType($value),
        );
    }

    /**
     * Convert full model class to simple type
     */
    private function getSimpleOwnerType($value): string
    {
        return match ($value) {
            'App\Models\User' => 'user',
            'App\Models\Agent' => 'agent',
            'App\Models\RealEstateOffice' => 'real_estate_office',
            default => $value
        };
    }

    /**
     * Convert simple type to full model class
     */
    private function getFullOwnerType($value): string
    {
        return match (strtolower($value)) {
            'user' => 'App\Models\User',
            'agent' => 'App\Models\Agent',
            'real_estate_office' => 'App\Models\RealEstateOffice',
            default => $value
        };
    }

    // Status-related scopes and methods
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    public function scopeRented($query)
    {
        return $query->where('status', 'rented');
    }

    // Status check methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    public function isRented(): bool
    {
        return $this->status === 'rented';
    }

    public function scopeByListingType($query, string $listingType)
    {
        return $query->where('listing_type', $listingType);
    }

    public function scopeBoosted($query)
    {
        return $query->where('is_boosted', true)
            ->where('boost_start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('boost_end_date')
                    ->orWhere('boost_end_date', '>=', now());
            });
    }

    // Add utility methods
    public function isForRent(): bool
    {
        return $this->listing_type === 'rent';
    }

    public function isForSale(): bool
    {
        return $this->listing_type === 'sell';
    }

    public function isPublished(): bool
    {
        return $this->published === true;
    }

    public function isBoosted(): bool
    {
        if (!$this->is_boosted) return false;

        $now = now();
        if ($this->boost_start_date && $now < $this->boost_start_date) return false;
        if ($this->boost_end_date && $now > $this->boost_end_date) return false;

        return true;
    }

    // Keep all your existing methods...
    public function getName(string $language = 'en'): string
    {
        return $this->name[$language] ?? $this->name['en'] ?? '';
    }

    public function getDescription(string $language = 'en'): string
    {
        return $this->description[$language] ?? $this->description['en'] ?? '';
    }

    public function getFormattedPrice(string $currency = 'USD'): string
    {
        $amount = $currency === 'USD' ? $this->price['usd'] : $this->price['iqd'];
        $symbol = $currency === 'USD' ? '$' : 'IQD ';

        return $symbol . number_format($amount);
    }

    public function getPricePerSqm(string $currency = 'USD'): float
    {
        return $currency === 'USD' ? $this->price['price_per_sqm']['usd'] : $this->price['price_per_sqm']['iqd'];
    }

    public function getBedroomCount(): int
    {
        return $this->rooms['bedroom']['count'] ?? 0;
    }

    public function getBathroomCount(): int
    {
        return $this->rooms['bathroom']['count'] ?? 0;
    }

    public function getKitchenCount(): int
    {
        return $this->rooms['kitchen']['count'] ?? 0;
    }

    public function isVerified(): bool
    {
        return $this->verified === true;
    }

    public function getMainImage(): string
    {
        return $this->images[0] ?? '';
    }

    public function getAdditionalImages(): array
    {
        return array_slice($this->images, 1);
    }

    public function getTypeCategory(): string
    {
        return $this->type['category'] ?? '';
    }

    public function getTypeLabel(string $language = 'en'): string
    {
        return $this->type['labels'][$language] ?? $this->type['labels']['en'] ?? '';
    }

    public function getAddress(string $language = 'en'): string
    {
        $address = $this->address_details;

        return sprintf(
            '%s, %s, %s, %s',
            $address['street_address'][$language] ?? $address['street_address']['en'] ?? '',
            $address['neighborhood'][$language] ?? $address['neighborhood']['en'] ?? '',
            $address['city'][$language] ?? $address['city']['en'] ?? '',
            $address['country'][$language] ?? $address['country']['en'] ?? ''
        );
    }

    /**
     * Get nearby amenities by type
     */
    public function getNearbyAmenitiesByType(string $type): array
    {
        return $this->nearby_amenities[$type] ?? [];
    }

    /**
     * Get energy efficiency features
     */
    public function getEnergyFeatures(): array
    {
        return $this->energy_details['efficiency_features'] ?? [];
    }

    /**
     * Check if property has renewable energy
     */
    public function hasRenewableEnergy(): bool
    {
        return $this->energy_details['renewable_energy']['solar_panels'] ?? false;
    }

    /**
     * Get investment rental yield
     */
    public function getRentalYield(): float
    {
        return $this->investment_analysis['estimated_rental_yield'] ?? 0.0;
    }

    /**
     * Get monthly rental potential in specific currency
     */
    public function getMonthlyRent(string $currency = 'USD'): int
    {
        $rental = $this->investment_analysis['rental_potential'] ?? [];
        return $currency === 'USD' ? $rental['monthly_rent_usd'] ?? 0 : $rental['monthly_rent_iqd'] ?? 0;
    }

    /**
     * Get SEO metadata for specific language
     */
    public function getSeoTitle(string $language = 'en'): string
    {
        return $this->seo_metadata['meta_title'][$language] ?? $this->seo_metadata['meta_title']['en'] ?? '';
    }

    /**
     * Get SEO description for specific language
     */
    public function getSeoDescription(string $language = 'en'): string
    {
        return $this->seo_metadata['meta_description'][$language] ?? $this->seo_metadata['meta_description']['en'] ?? '';
    }

    /**
     * Get SEO keywords for specific language
     */
    public function getSeoKeywords(string $language = 'en'): array
    {
        return $this->seo_metadata['keywords'][$language] ?? $this->seo_metadata['keywords']['en'] ?? [];
    }

    /**
     * Scope: Active properties only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Verified properties only
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope: Properties for sale
     */
    public function scopeForSale($query)
    {
        return $query->whereJsonContains('availability->status', 'for_sale');
    }

    /**
     * Scope: Properties by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->whereJsonContains('type->category', $type);
    }

    /**
     * Scope: Properties in price range
     */
    public function scopeInPriceRange($query, int $minPrice, int $maxPrice, string $currency = 'usd')
    {
        return $query->whereBetween("price->{$currency}", [$minPrice, $maxPrice]);
    }

    /**
     * Scope: Properties by bedroom count
     */
    public function scopeByBedroomCount($query, int $count)
    {
        return $query->whereJsonContains('rooms->bedroom->count', $count);
    }

    /**
     * Scope: Properties by area range
     */
    public function scopeByAreaRange($query, float $minArea, float $maxArea)
    {
        return $query->whereBetween('area', [$minArea, $maxArea]);
    }

    /**
     * Scope: Properties in specific city
     */
    public function scopeInCity($query, string $city, string $language = 'en')
    {
        return $query->whereJsonContains("address_details->city->{$language}", $city);
    }

    /**
     * Scope: Properties with specific amenities
     */
    public function scopeWithAmenity($query, string $amenityType)
    {
        return $query->whereJsonContains("nearby_amenities->{$amenityType}", '!=', null);
    }

    /**
     * Scope: Furnished properties
     */
    public function scopeFurnished($query)
    {
        return $query->where('furnished', true);
    }

    /**
     * Get property coordinates
     */
    public function getCoordinates(): array
    {
        $buildingEntrance = collect($this->locations)->firstWhere('type', 'building_entrance');

        return [
            'lat' => $buildingEntrance['lat'] ?? null,
            'lng' => $buildingEntrance['lng'] ?? null,
        ];
    }

    /**
     * Calculate distance to a point (in kilometers)
     */
    public function distanceTo(float $lat, float $lng): ?float
    {
        $coordinates = $this->getCoordinates();

        if (!$coordinates['lat'] || !$coordinates['lng']) {
            return null;
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        $latDiff = deg2rad($lat - $coordinates['lat']);
        $lngDiff = deg2rad($lng - $coordinates['lng']);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($coordinates['lat'])) * cos(deg2rad($lat)) *
            sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get property views statistics
     */
    public function getViewsStats(): array
    {
        return [
            'total_views' => $this->views,
            'unique_views' => $this->view_analytics['unique_views'] ?? 0,
            'returning_views' => $this->view_analytics['returning_views'] ?? 0,
            'average_time' => $this->view_analytics['average_time_on_listing'] ?? 0,
            'bounce_rate' => $this->view_analytics['bounce_rate'] ?? 0,
        ];
    }

    /**
     * Get favorites statistics
     */
    public function getFavoritesStats(): array
    {
        return [
            'total_favorites' => $this->favorites_count,
            'last_30_days' => $this->favorites_analytics['last_30_days'] ?? 0,
            'user_demographics' => $this->favorites_analytics['user_demographics'] ?? [],
        ];
    }

    /**
     * Check if property has virtual tour
     */
    public function hasVirtualTour(): bool
    {
        return !empty($this->virtual_tour_url);
    }

    /**
     * Check if property has video tour
     */
    public function hasVideoTour(): bool
    {
        return !empty($this->additional_media['video_tour']['url'] ?? '');
    }

    /**
     * Check if property has drone footage
     */
    public function hasDroneFotage(): bool
    {
        return !empty($this->additional_media['drone_footage']['url'] ?? '');
    }

    /**
     * Get HOA monthly fees
     */
    public function getHoaFees(string $currency = 'USD'): int
    {
        $fees = $this->legal_information['hoa_fees'] ?? [];
        return $currency === 'USD' ? $fees['monthly_usd'] ?? 0 : $fees['monthly_iqd'] ?? 0;
    }

    /**
     * Get annual property taxes
     */
    public function getPropertyTaxes(string $currency = 'USD'): int
    {
        $taxes = $this->legal_information['property_taxes'] ?? [];
        return $currency === 'USD' ? $taxes['annual_amount_usd'] ?? 0 : $taxes['annual_amount_iqd'] ?? 0;
    }

    /**
     * Get additional costs for purchase
     */
    public function getAdditionalCosts(string $currency = 'USD'): array
    {
        $costs = $this->price['additional_costs'] ?? [];
        $currencyKey = $currency === 'USD' ? 'usd' : 'iqd';

        return [
            'transfer_tax' => $costs['transfer_tax'][$currencyKey] ?? 0,
            'legal_fees' => $costs['legal_fees'][$currencyKey] ?? 0,
            'registration_fees' => $costs['registration_fees'][$currencyKey] ?? 0,
        ];
    }

    /**
     * Get financing options
     */
    public function getFinancingOptions(): array
    {
        return $this->price['payment_terms']['installment_options'] ?? [];
    }

    /**
     * Check if cash discount is available
     */
    public function hasCashDiscount(): bool
    {
        return ($this->price['payment_terms']['cash_discount'] ?? 0) > 0;
    }

    /**
     * Get cash discount percentage
     */
    public function getCashDiscountPercent(): float
    {
        return $this->price['payment_terms']['cash_discount'] ?? 0.0;
    }

    /**
     * Convert to array with specific language
     */
    public function toLocalizedArray(string $language = 'en'): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getName($language),
            'description' => $this->getDescription($language),
            'type' => $this->getTypeLabel($language),
            'address' => $this->getAddress($language),
            'price_formatted' => $this->getFormattedPrice(),
            'area' => $this->area,
            'bedrooms' => $this->getBedroomCount(),
            'bathrooms' => $this->getBathroomCount(),
            'images' => $this->images,
            'main_image' => $this->getMainImage(),
            'is_furnished' => $this->furnished,
            'is_verified' => $this->isVerified(),
            'floor_number' => $this->floor_number,
            'year_built' => $this->year_built,
            'energy_rating' => $this->energy_rating,
            'virtual_tour_url' => $this->virtual_tour_url,
            'coordinates' => $this->getCoordinates(),
            'status' => $this->status,
            'owner_type' => $this->owner_type,
            'is_pending' => $this->isPending(),
            'is_approved' => $this->isApproved(),
            'is_available' => $this->isAvailable(),
        ];
    }
}