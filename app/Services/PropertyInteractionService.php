<?php

namespace App\Services;

use App\Models\UserPropertyInteraction;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

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

        // Get user's viewing history from last 30 days
        $viewedProperties = DB::table('user_property_interactions')
            ->where('user_id', $userId)
            ->where('interaction_type', 'view')
            ->where('created_at', '>=', now()->subDays(30))
            ->pluck('property_id')
            ->toArray();

        if (empty($viewedProperties)) {
            return $this->getGeneralRecommendations($limit);
        }

        // Analyze user preferences
        $viewedPropertiesData = Property::whereIn('id', $viewedProperties)->get();

        $preferredTypes = $viewedPropertiesData->pluck('type')
            ->map(fn($type) => $type['category'] ?? null)
            ->filter()
            ->unique()
            ->toArray();

        $avgPrice = $viewedPropertiesData->avg(function ($p) {
            return $p->price['usd'] ?? 0;
        });

        $preferredListingType = $viewedPropertiesData->pluck('listing_type')
            ->mode()[0] ?? null;

        // Build recommendation query
        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $viewedProperties);

        // Apply preferences
        if (!empty($preferredTypes)) {
            $query->where(function ($q) use ($preferredTypes) {
                foreach ($preferredTypes as $type) {
                    $q->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?", [strtolower($type)]);
                }
            });
        }

        if ($preferredListingType) {
            $query->where('listing_type', $preferredListingType);
        }

        if ($avgPrice > 0) {
            $minPrice = $avgPrice * 0.7;
            $maxPrice = $avgPrice * 1.3;
            $query->whereBetween(
                DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2))"),
                [$minPrice, $maxPrice]
            );
        }

        // Score and sort
        return $query->selectRaw('
                *,
                (
                    (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                    (CASE WHEN verified = 1 THEN 20 ELSE 0 END) +
                    (LEAST(views, 100) * 0.15) +
                    (LEAST(favorites_count, 50) * 0.5) +
                    (rating * 5) +
                    (CASE
                        WHEN DATEDIFF(NOW(), created_at) <= 7 THEN 15
                        WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10
                        ELSE 0
                    END)
                ) as recommendation_score
            ')
            ->orderByDesc('recommendation_score')
            ->limit($limit)
            ->get();
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
    public function trackImpressions(string $userId, $properties, string $sourceEndpoint): void
    {
        if ($properties->isEmpty()) return;

        try {
            $timestamp = now();
            $insertData = [];
            $ip = request()->ip();

            foreach ($properties as $property) {
                $insertData[] = [
                    'user_id' => $userId,
                    'property_id' => $property->id,
                    'interaction_type' => 'impression',
                    'metadata' => json_encode([
                        'source_endpoint' => $sourceEndpoint,
                        'ip' => $ip
                    ]),
                    'created_at' => $timestamp,
                    // 'updated_at' => $timestamp, // ❌ DELETE OR COMMENT OUT THIS LINE
                ];
            }

            // Perform the bulk insert
            UserPropertyInteraction::insert($insertData);
        } catch (\Exception $e) {
            Log::error('Failed to track impressions', [
                'user_id' => $userId,
                'endpoint' => $sourceEndpoint,
                'error' => $e->getMessage()
            ]);
        }
    }
}
