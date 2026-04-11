<?php

namespace App\Services;

use App\Models\UserPropertyInteraction;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PropertyInteractionService
{
    /**
     * Track user viewing a property
     */
    public function trackView(string $userId, string $propertyId, array $metadata = []): bool
    {
        try {
            // Record detailed interaction
            UserPropertyInteraction::create([
                'user_id' => $userId,
                'property_id' => $propertyId,
                'interaction_type' => 'view',
                'metadata' => array_merge($metadata, [
                    'timestamp' => now()->toDateTimeString(),
                    'ip' => request()->ip(),
                ]),
                'created_at' => now(),
            ]);

            // Update user's recently viewed (keep last 50)
            $this->updateRecentlyViewed($userId, $propertyId);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to track property view', [
                'user_id' => $userId,
                'property_id' => $propertyId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Update user's recently viewed properties
     */
    private function updateRecentlyViewed(string $userId, string $propertyId): void
    {
        $user = User::find($userId);
        if (!$user) return;

        $recentlyViewed = $user->recently_viewed_properties ?? [];

        // Remove if already exists (to move to front)
        $recentlyViewed = array_filter($recentlyViewed, fn($id) => $id !== $propertyId);

        // Add to beginning
        array_unshift($recentlyViewed, $propertyId);

        // Keep only last 50
        $recentlyViewed = array_slice($recentlyViewed, 0, 50);

        $user->update([
            'recently_viewed_properties' => $recentlyViewed,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Get user's recently viewed properties
     */
    public function getRecentlyViewed(string $userId, int $limit = 20): Collection
    {
        $user = User::find($userId);
        if (!$user || empty($user->recently_viewed_properties)) {
            return collect();
        }

        $propertyIds = array_slice($user->recently_viewed_properties, 0, $limit);

        // Return properties in the order they were viewed
        $properties = Property::whereIn('id', $propertyIds)
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->get();

        // Sort by the order in recently_viewed_properties
        return $properties->sortBy(function ($property) use ($propertyIds) {
            return array_search($property->id, $propertyIds);
        })->values();
    }

    /**
     * Get personalized recommendations based on user behavior
     */
    public function getPersonalizedRecommendations(string $userId, int $limit = 20): Collection
    {
        $user = User::find($userId);
        if (!$user) {
            return $this->getGeneralRecommendations($limit);
        }

        // ─── 1. Get both views AND favorites ───────────────────────
        $interactions = DB::table('user_property_interactions')
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(60))
            ->whereIn('interaction_type', ['view', 'favorite'])
            ->select('property_id', 'interaction_type')
            ->get();

        $viewedIds    = $interactions->where('interaction_type', 'view')
            ->pluck('property_id')->toArray();
        $favoritedIds = $interactions->where('interaction_type', 'favorite')
            ->pluck('property_id')->toArray();

        // Nothing at all → fall back
        $allInteractedIds = array_unique(array_merge($viewedIds, $favoritedIds));
        if (empty($allInteractedIds)) {
            return $this->getGeneralRecommendations($limit);
        }

        // ─── 2. Learn preferences (favorites weighted 3x over views) ─
        $viewedData    = Property::whereIn('id', $viewedIds)->get();
        $favoritedData = Property::whereIn('id', $favoritedIds)->get();

        // Property types: favorites count 3x
        $preferredTypes = collect()
            ->merge($viewedData->pluck('type')->map(fn($t) => $t['category'] ?? null))
            ->merge($favoritedData->pluck('type')->map(fn($t) => $t['category'] ?? null))
            ->merge($favoritedData->pluck('type')->map(fn($t) => $t['category'] ?? null))
            ->merge($favoritedData->pluck('type')->map(fn($t) => $t['category'] ?? null))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(3)
            ->toArray();

        // Listing type: favorites count 3x
        $listingTypes = collect()
            ->merge($viewedData->pluck('listing_type'))
            ->merge($favoritedData->pluck('listing_type'))
            ->merge($favoritedData->pluck('listing_type'))
            ->merge($favoritedData->pluck('listing_type'))
            ->filter();
        $preferredListingType = $listingTypes->mode()[0] ?? null;

        // Price range: base on favorites if available, else views
        $priceSource  = $favoritedData->isNotEmpty() ? $favoritedData : $viewedData;
        $avgPrice     = $priceSource->avg(fn($p) => $p->price['usd'] ?? 0);

        // Cities: from favorites first, then views
        $preferredCities = collect()
            ->merge($favoritedData->map(fn($p) => $p->address_details['city']['en'] ?? null))
            ->merge($favoritedData->map(fn($p) => $p->address_details['city']['en'] ?? null))
            ->merge($favoritedData->map(fn($p) => $p->address_details['city']['en'] ?? null))
            ->merge($viewedData->map(fn($p) => $p->address_details['city']['en'] ?? null))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(3)
            ->toArray();

        // ─── 3. Build query ────────────────────────────────────────
        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $allInteractedIds); // exclude already seen AND favorited

        if (!empty($preferredTypes)) {
            $query->where(function ($q) use ($preferredTypes) {
                foreach ($preferredTypes as $type) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                        [strtolower($type)]
                    );
                }
            });
        }

        if ($preferredListingType) {
            $query->where('listing_type', $preferredListingType);
        }

        if ($avgPrice > 0) {
            $query->whereBetween(
                DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2))"),
                [$avgPrice * 0.65, $avgPrice * 1.35]
            );
        }

        if (!empty($preferredCities)) {
            $query->where(function ($q) use ($preferredCities) {
                foreach ($preferredCities as $city) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                        [strtolower($city)]
                    );
                }
            });
        }

        // ─── 4. Score (favorites-aware) ────────────────────────────
        $results = $query->selectRaw('
            *,
            (
                (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                (CASE WHEN verified = 1 THEN 20 ELSE 0 END) +
                (LEAST(views, 100) * 0.15) +
                (LEAST(favorites_count, 50) * 0.8) +
                (rating * 5) +
                (CASE
                    WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 15
                    WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10
                    ELSE 0
                END)
            ) as recommendation_score
        ')
            ->orderByDesc('recommendation_score')
            ->limit($limit)
            ->get();

        // ─── 5. If too few results, relax city filter and fill up ──
        if ($results->count() < $limit) {
            $needed     = $limit - $results->count();
            $existingIds = $allInteractedIds + $results->pluck('id')->toArray();

            $fallback = Property::query()
                ->where('is_active', true)
                ->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                ->whereNotIn('id', $existingIds)
                ->when($preferredListingType, fn($q) => $q->where('listing_type', $preferredListingType))
                ->selectRaw('*, (
                (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                (CASE WHEN verified = 1 THEN 20 ELSE 0 END) +
                (LEAST(favorites_count, 50) * 0.8) +
                (rating * 5)
            ) as recommendation_score')
                ->orderByDesc('recommendation_score')
                ->limit($needed)
                ->get();

            $results = $results->merge($fallback);
        }

        return $results;
    }

    /**
     * Get general recommendations for users without history
     */
    private function getGeneralRecommendations(int $limit): Collection
    {
        return Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->selectRaw('
                *,
                (
                    (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                    (CASE WHEN verified = 1 THEN 20 ELSE 0 END) +
                    (LEAST(views, 100) * 0.15) +
                    (LEAST(favorites_count, 50) * 0.5) +
                    (rating * 5) +
                    (CASE
                        WHEN DATEDIFF(NOW(), created_at) <= 7 THEN 20
                        WHEN DATEDIFF(NOW(), created_at) <= 14 THEN 15
                        WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10
                        ELSE 0
                    END)
                ) as recommendation_score
            ')
            ->orderByDesc('recommendation_score')
            ->limit($limit)
            ->get();
    }

    // In App\Services\PropertyInteractionService.php

    /**
     * Bulk track properties displayed in lists (Impressions)
     * Efficiently inserts multiple records in one query.
     */
    public function trackImpressions(string $userId, $properties, string $sourceEndpoint, array $extra = []): void
    {
        if (empty($properties) || (is_object($properties) && method_exists($properties, 'isEmpty') && $properties->isEmpty())) {
            return;
        }

        try {
            // ✅ Throttle: skip if same set tracked in last 5 minutes
            $propertyIds = collect($properties)->pluck('id')->sort()->implode(',');
            $cacheKey    = 'impressions_' . $userId . '_' . $sourceEndpoint . '_' . md5($propertyIds);

            if (Cache::has($cacheKey)) {
                return;
            }
            Cache::put($cacheKey, true, 300);

            $timestamp  = now();
            $insertData = [];
            $ip         = request()->ip();
            $isGuest    = str_starts_with($userId, 'guest_');
            $sessionId  = $isGuest ? str_replace('guest_', '', $userId) : session()->getId();

            foreach ($properties as $property) {
                $insertData[] = [
                    'user_id'          => $isGuest ? null : $userId,
                    'session_id'       => $sessionId,
                    'property_id'      => $property->id,
                    'interaction_type' => 'impression',
                    'metadata'         => json_encode(array_merge([
                        'source_endpoint' => $sourceEndpoint,
                        'ip'              => $ip,
                        'is_guest'        => $isGuest,
                    ], $extra)),
                    'created_at'       => $timestamp,
                ];
            }

            Log::info('🔵 trackImpressions called', [
                'userId'        => $userId,
                'isGuest'       => $isGuest,
                'sessionId'     => $sessionId,
                'propertyCount' => count($insertData),
                'endpoint'      => $sourceEndpoint,
            ]);

            UserPropertyInteraction::insert($insertData);
        } catch (\Exception $e) {
            Log::error('Failed to track impressions', [
                'user_id'  => $userId,
                'endpoint' => $sourceEndpoint,
                'error'    => $e->getMessage()
            ]);
        }
    }
}
