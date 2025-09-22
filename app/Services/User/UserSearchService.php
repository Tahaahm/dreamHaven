<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserSearchService
{
    /**
     * Get user's search preferences
     */
    public function getSearchPreferences(User $user): array
    {
        return $user->search_preferences ?? $this->getDefaultSearchPreferences();
    }

    /**
     * Update user's search preferences
     */
    public function updateSearchPreferences(User $user, array $preferences): array
    {
        DB::beginTransaction();
        try {
            $user->update(['search_preferences' => $preferences]);
            DB::commit();
            return $preferences;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reset search preferences to default
     */
    public function resetSearchPreferences(User $user): array
    {
        $defaultPreferences = $this->getDefaultSearchPreferences();

        DB::beginTransaction();
        try {
            $user->update(['search_preferences' => $defaultPreferences]);
            DB::commit();
            return $defaultPreferences;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get search filters based on preferences
     */
    public function getSearchFilters(User $user): array
    {
        $searchPreferences = $this->getSearchPreferences($user);
        $filters = $searchPreferences['filters'] ?? [];
        $sorting = $searchPreferences['sorting'] ?? [];

        $searchFilters = [];

        // Price filters
        if (($filters['price_enabled'] ?? false) && ($filters['min_price'] || $filters['max_price'])) {
            if ($filters['min_price']) {
                $searchFilters['min_price'] = $filters['min_price'];
            }
            if ($filters['max_price']) {
                $searchFilters['max_price'] = $filters['max_price'];
            }
        }

        // Location filters
        if ($filters['location_enabled'] ?? false) {
            $searchFilters['location_radius'] = $filters['location_radius'] ?? 10;
            if ($user->lat && $user->lng) {
                $searchFilters['user_lat'] = $user->lat;
                $searchFilters['user_lng'] = $user->lng;
            }
        }

        // Property type filters
        if (!empty($filters['property_types'])) {
            $searchFilters['property_types'] = $filters['property_types'];
        }

        // Other filters
        if (isset($filters['min_bedrooms'])) {
            $searchFilters['min_bedrooms'] = $filters['min_bedrooms'];
        }
        if (isset($filters['max_bedrooms'])) {
            $searchFilters['max_bedrooms'] = $filters['max_bedrooms'];
        }

        // Sorting criteria
        $sortCriteria = [];
        if ($sorting['price_enabled'] ?? false) {
            $sortCriteria[] = 'price_' . ($sorting['price_order'] ?? 'low_to_high');
        }
        if ($sorting['popularity_enabled'] ?? false) {
            $sortCriteria[] = 'popularity';
        }
        if ($sorting['date_enabled'] ?? false) {
            $sortCriteria[] = 'date_' . ($sorting['date_order'] ?? 'newest');
        }
        if ($sorting['distance_enabled'] ?? false && $user->lat && $user->lng) {
            $sortCriteria[] = 'distance';
        }

        $searchFilters['sort_by'] = empty($sortCriteria) ? ['relevance'] : $sortCriteria;

        return [
            'filters' => $searchFilters,
            'user_preferences' => $searchPreferences
        ];
    }

    /**
     * Search properties (mock implementation)
     */
    public function searchProperties(User $user, array $params): array
    {
        // This would integrate with PropertyService or make direct database queries
        // For now, return mock data
        return [
            'properties' => [],
            'pagination' => [
                'current_page' => $params['page'] ?? 1,
                'per_page' => $params['per_page'] ?? 20,
                'total' => 0,
            ],
            'search_info' => [
                'query' => $params['query'] ?? '',
                'filters_applied' => $this->getSearchFilters($user)['filters'],
            ]
        ];
    }

    /**
     * Get recommendations (mock implementation)
     */
    public function getRecommendations(User $user, string $type, int $limit): array
    {
        return [
            'recommendations' => [],
            'type' => $type,
            'based_on' => [
                'user_location' => $user->place,
                'search_preferences' => !empty($user->search_preferences),
                'user_language' => $user->language
            ]
        ];
    }

    private function getDefaultSearchPreferences(): array
    {
        return [
            'filters' => [
                'price_enabled' => false,
                'min_price' => null,
                'max_price' => null,
                'location_enabled' => false,
                'location_radius' => 10.0,
                'property_types' => [],
                'min_bedrooms' => null,
                'max_bedrooms' => null,
            ],
            'sorting' => [
                'price_enabled' => false,
                'price_order' => 'low_to_high',
                'popularity_enabled' => false,
                'date_enabled' => false,
                'date_order' => 'newest',
                'distance_enabled' => false,
            ],
            'behavior' => [
                'enable_notifications' => true,
                'save_search_history' => true,
                'auto_suggestions' => true,
                'recent_searches' => true,
                'max_history_items' => 50,
            ]
        ];
    }
}
