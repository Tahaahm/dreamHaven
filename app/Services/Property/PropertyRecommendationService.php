<?php

namespace App\Services\Property;

use App\Models\Property;
use App\Models\User;
use App\Services\PropertyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class PropertyRecommendationService
{
    protected PropertyService $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    /**
     * Get recommended properties based on user preferences or trending for anonymous users
     */
    public function getRecommendedProperties(?User $user, int $limit = 20, string $language = 'en'): array
    {
        if ($user) {
            return $this->getPersonalizedRecommendations($user, $limit, $language);
        }

        return $this->getTrendingProperties($limit, $language);
    }

    /**
     * Get personalized recommendations for authenticated users
     */
    public function getPersonalizedRecommendations(User $user, int $limit = 20, string $language = 'en'): array
    {
        $recommendations = collect();

        // 1. Based on user's search preferences (40% weight)
        $preferenceBasedProperties = $this->getPreferenceBasedRecommendations($user, intval($limit * 0.4));
        $recommendations = $recommendations->merge($preferenceBasedProperties);

        // 2. Based on user's location if available (30% weight)
        if ($user->lat && $user->lng) {
            $locationBasedProperties = $this->getLocationBasedRecommendations($user, intval($limit * 0.3));
            $recommendations = $recommendations->merge($locationBasedProperties);
        }

        // 3. Based on user's viewed properties (20% weight)
        $behaviorBasedProperties = $this->getBehaviorBasedRecommendations($user, intval($limit * 0.2));
        $recommendations = $recommendations->merge($behaviorBasedProperties);

        // 4. Fill remaining with trending properties (10% weight + any remaining)
        $remaining = $limit - $recommendations->count();
        if ($remaining > 0) {
            $trendingProperties = $this->getTrendingProperties($remaining, $language);
            $recommendations = $recommendations->merge($trendingProperties);
        }

        // Remove duplicates and limit
        $uniqueRecommendations = $recommendations->unique('id')->take($limit);

        return $uniqueRecommendations->map(function ($property) use ($language) {
            if ($property instanceof Property) {
                return $this->propertyService->transformForListing($property, $language);
            }
            return $property; // Already transformed
        })->values()->toArray();
    }

    /**
     * Get recommendations based on user's saved preferences
     */
    private function getPreferenceBasedRecommendations(User $user, int $limit): Collection
    {
        $preferences = $user->search_preferences ?? [];
        $filters = $preferences['filters'] ?? [];

        $query = Property::query()
            ->active()
            ->published()
            ->whereNotIn('status', ['cancelled', 'pending']);

        // Apply user's preferred filters
        if (!empty($filters['property_types'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['property_types'] as $type) {
                    $q->orWhereRaw("JSON_EXTRACT(type, '$.category') = ?", [strtolower($type)]);
                }
            });
        }

        if (isset($filters['min_price']) && isset($filters['max_price'])) {
            $query->whereRaw("JSON_EXTRACT(price, '$.usd') BETWEEN ? AND ?", [$filters['min_price'], $filters['max_price']]);
        }

        if (isset($filters['min_bedrooms'])) {
            $query->whereRaw("JSON_EXTRACT(rooms, '$.bedroom.count') >= ?", [$filters['min_bedrooms']]);
        }

        if (isset($filters['furnished'])) {
            $query->where('furnished', $filters['furnished']);
        }

        return $query->orderByDesc('verified')
            ->orderByDesc(DB::raw('(views + favorites_count * 2)'))
            ->limit($limit)
            ->get();
    }

    /**
     * Get location-based recommendations
     */
    private function getLocationBasedRecommendations(User $user, int $limit): Collection
    {
        $radius = 50; // 50km radius

        return Property::whereRaw(
            "(6371 * acos(cos(radians(?)) * cos(radians(JSON_EXTRACT(locations, '$[0].lat'))) * cos(radians(JSON_EXTRACT(locations, '$[0].lng')) - radians(?)) + sin(radians(?)) * sin(radians(JSON_EXTRACT(locations, '$[0].lat'))))) <= ?",
            [$user->lat, $user->lng, $user->lat, $radius]
        )
            ->active()
            ->published()
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->orderByDesc('verified')
            ->orderByDesc('rating')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recommendations based on user's viewing behavior
     */
    private function getBehaviorBasedRecommendations(User $user, int $limit): Collection
    {
        // Get properties user has viewed (you'll need to implement view tracking)
        $viewedProperties = $this->getUserViewedProperties($user, 10);

        if ($viewedProperties->isEmpty()) {
            return collect();
        }

        // Extract characteristics from viewed properties
        $commonTypes = $viewedProperties->pluck('type.category')->filter()->countBy();
        $avgPriceRange = $this->calculatePriceRange($viewedProperties);
        $commonCities = $viewedProperties->pluck('address_details.city.en')->filter()->countBy();

        $query = Property::query()
            ->active()
            ->published()
            ->whereNotIn('status', ['cancelled', 'pending']);

        // Find similar properties
        if ($commonTypes->isNotEmpty()) {
            $mostCommonType = $commonTypes->keys()->first();
            $query->whereRaw("JSON_EXTRACT(type, '$.category') = ?", [$mostCommonType]);
        }

        if ($avgPriceRange['min'] && $avgPriceRange['max']) {
            $query->whereRaw("JSON_EXTRACT(price, '$.usd') BETWEEN ? AND ?", [
                $avgPriceRange['min'] * 0.8, // 20% buffer
                $avgPriceRange['max'] * 1.2
            ]);
        }

        if ($commonCities->isNotEmpty()) {
            $mostCommonCity = $commonCities->keys()->first();
            $query->whereRaw("JSON_EXTRACT(address_details, '$.city.en') = ?", [$mostCommonCity]);
        }

        return $query->orderByDesc('rating')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending properties for anonymous users or as fallback
     */
    public function getTrendingProperties(int $limit = 20, string $language = 'en'): array
    {
        // Trending properties logic:
        // 1. Recently created with high engagement
        // 2. Properties with growing view counts
        // 3. Properties with high favorites-to-views ratio
        // 4. Verified properties with recent activity

        $trending = Property::where(function ($query) {
            // Recently created with good initial performance
            $query->where('created_at', '>=', now()->subDays(30))
                ->where('views', '>', 50)
                ->where('favorites_count', '>', 5);
        })
            ->orWhere(function ($query) {
                // High engagement properties
                $query->whereRaw('(favorites_count / GREATEST(views, 1)) > 0.1') // Good favorites ratio
                    ->where('views', '>', 100);
            })
            ->orWhere(function ($query) {
                // Recently updated verified properties
                $query->where('verified', true)
                    ->where('updated_at', '>=', now()->subDays(7))
                    ->where('views', '>', 20);
            })
            ->active()
            ->published()
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->orderByDesc(DB::raw('(views + favorites_count * 3)')) // Weighted engagement score
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $trending->map(function ($property) use ($language) {
            return $this->propertyService->transformForListing($property, $language);
        })->toArray();
    }

    /**
     * Get similar properties based on an existing property
     */
    public function getSimilarProperties(Property $property, int $limit = 5, string $language = 'en'): array
    {
        $query = Property::where('id', '!=', $property->id)
            ->active()
            ->published()
            ->whereNotIn('status', ['cancelled', 'pending']);

        // Match by property type
        if (isset($property->type['category'])) {
            $query->whereRaw("JSON_EXTRACT(type, '$.category') = ?", [$property->type['category']]);
        }

        // Similar price range (±30%)
        if (isset($property->price['usd'])) {
            $minPrice = $property->price['usd'] * 0.7;
            $maxPrice = $property->price['usd'] * 1.3;
            $query->whereRaw("JSON_EXTRACT(price, '$.usd') BETWEEN ? AND ?", [$minPrice, $maxPrice]);
        }

        // Similar area (±20%)
        if ($property->area) {
            $minArea = $property->area * 0.8;
            $maxArea = $property->area * 1.2;
            $query->whereBetween('area', [$minArea, $maxArea]);
        }

        // Same city if available
        if (isset($property->address_details['city']['en'])) {
            $city = $property->address_details['city']['en'];
            $query->whereRaw("JSON_EXTRACT(address_details, '$.city.en') = ?", [$city]);
        }

        // Similar bedroom count
        if (isset($property->rooms['bedroom']['count'])) {
            $bedroomCount = $property->rooms['bedroom']['count'];
            $query->whereRaw("JSON_EXTRACT(rooms, '$.bedroom.count') IN (?, ?, ?)", [
                $bedroomCount - 1,
                $bedroomCount,
                $bedroomCount + 1
            ]);
        }

        $similar = $query->orderByDesc('verified')
            ->orderByDesc('rating')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();

        return $similar->map(function ($property) use ($language) {
            return $this->propertyService->transformForListing($property, $language);
        })->toArray();
    }

    /**
     * Get user's recently viewed properties (implement based on your tracking system)
     */
    private function getUserViewedProperties(User $user, int $limit): Collection
    {
        // This assumes you have a property_views table or similar tracking
        // If not implemented yet, you can return an empty collection

        try {
            // Example implementation - adjust based on your actual tracking system
            return Property::whereIn('id', function ($query) use ($user) {
                $query->select('property_id')
                    ->from('property_views')
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(20);
            })->limit($limit)->get();
        } catch (\Exception $e) {
            // If property_views table doesn't exist yet, return empty collection
            Log::info('Property views tracking not implemented yet');
            return collect();
        }
    }

    /**
     * Calculate price range from viewed properties
     */
    private function calculatePriceRange(Collection $properties): array
    {
        $prices = $properties->map(function ($property) {
            return $property->price['usd'] ?? 0;
        })->filter(function ($price) {
            return $price > 0;
        });

        if ($prices->isEmpty()) {
            return ['min' => null, 'max' => null];
        }

        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
            'avg' => $prices->avg()
        ];
    }

    /**
     * Get recommendations for property owners (what similar properties are performing well)
     */
    public function getOwnerRecommendations(Property $property): array
    {
        $recommendations = [];

        // Price optimization suggestion
        $similarProperties = $this->getSimilarPropertiesPricing($property);
        if ($similarProperties->isNotEmpty()) {
            $avgPrice = $similarProperties->avg('price.usd');
            $currentPrice = $property->price['usd'] ?? 0;

            if ($currentPrice > $avgPrice * 1.2) {
                $recommendations[] = [
                    'type' => 'pricing',
                    'message' => 'Consider reducing price. Similar properties are priced ' . number_format(($currentPrice - $avgPrice) / $avgPrice * 100, 1) . '% lower.',
                    'suggested_price' => $avgPrice
                ];
            }
        }

        // Performance recommendations
        if ($property->views < 10) {
            $recommendations[] = [
                'type' => 'visibility',
                'message' => 'Add more photos and improve description to increase views.',
                'action' => 'improve_listing'
            ];
        }

        if (!$property->verified) {
            $recommendations[] = [
                'type' => 'verification',
                'message' => 'Get your property verified to increase trust and visibility.',
                'action' => 'verify_property'
            ];
        }

        return $recommendations;
    }

    /**
     * Get similar properties for pricing analysis
     */
    private function getSimilarPropertiesPricing(Property $property): Collection
    {
        return Property::where('id', '!=', $property->id)
            ->whereRaw("JSON_EXTRACT(type, '$.category') = ?", [$property->type['category'] ?? ''])
            ->whereBetween('area', [$property->area * 0.9, $property->area * 1.1])
            ->whereRaw("JSON_EXTRACT(address_details, '$.city.en') = ?", [$property->address_details['city']['en'] ?? ''])
            ->where('status', 'available')
            ->limit(10)
            ->get();
    }
}