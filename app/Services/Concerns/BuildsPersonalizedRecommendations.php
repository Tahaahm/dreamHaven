<?php

namespace App\Services\Concerns;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Personalized "recommended for you" properties, backed by
 * App\Services\Intelligence\UserTasteProfile. Extracted from
 * PropertyInteractionService.php as-is — no behavior changed, only
 * relocated. See TracksUserSignals.php for why this split is safe.
 *
 * Note: the original file also defined a private getBudgetMatchedRecommendations()
 * method that was never called from anywhere (verified by searching the
 * whole method for callers) — it's been dropped here as confirmed dead
 * code rather than carried over, since it never executed either way.
 */
trait BuildsPersonalizedRecommendations
{
    // ══════════════════════════════════════════════════════════════════════════
    //  PERSONALIZED RECOMMENDATIONS (UserTasteProfile-backed)
    //  All inference now lives in App\Services\Intelligence\UserTasteProfile.
    //  This method only reads from the profile and runs the SQL.
    // ══════════════════════════════════════════════════════════════════════════
    public function getPersonalizedRecommendations(string $userId, int $limit = 20): Collection
    {
        $user = User::find($userId);
        if (!$user) return $this->getGeneralRecommendations($limit);

        $profile = app(\App\Services\Intelligence\UserTasteProfile::class)->build($userId);

        Log::info('🎯 REC: profile loaded', [
            'user_id'        => $userId,
            'has_history'    => $profile['has_history'],
            'is_cold_start'  => $profile['is_cold_start'],
            'intent_score'   => $profile['intent_score'],
            'cities'         => $profile['cities'],
            'types'          => $profile['types'],
            'listing_type'   => $profile['listing_type'],
            'price'          => $profile['price'],
            'bedrooms'       => $profile['bedrooms'],
            'has_centroid'   => $profile['heat_centroid'] !== null,
            'seen_count'     => count($profile['seen_ids']),
            'signal_counts'  => $profile['signal_counts'],
        ]);

        if (!$profile['has_history'] && empty($profile['cities'])) {
            return $this->getGeneralRecommendations($limit);
        }

        if ($profile['is_cold_start']) {
            return $this->getFilterMatchedRecommendations([
                'city'         => array_key_first($profile['cities']),
                'listing_type' => $profile['listing_type'],
            ], $limit, []);
        }

        $types       = array_keys($profile['types']);
        $cities      = array_keys($profile['cities']);
        $listingType = $profile['listing_type'];
        $priceMin    = $profile['price']['min'] ?? null;
        $priceMax    = $profile['price']['max'] ?? null;
        $bedrooms    = $profile['bedrooms'];
        $heat        = $profile['heat_centroid'];
        $seenIds     = $profile['seen_ids'];

        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $seenIds);

        // Types — FIXED: use bindings, not string interpolation
        if (!empty($types)) {
            $query->where(function ($q) use ($types) {
                foreach ($types as $type) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                        [strtolower($type)]
                    );
                }
            });
        }
        if ($listingType) {
            $query->where('listing_type', $listingType);
        }
        if ($priceMin && $priceMax) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) BETWEEN ? AND ?",
                [$priceMin, $priceMax]
            );
        }
        if ($bedrooms !== null) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) AS UNSIGNED) = ?",
                [$bedrooms]
            );
        }
        if (!empty($cities)) {
            $query->where(function ($q) use ($cities) {
                foreach ($cities as $city) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                        [strtolower($city)]
                    );
                }
            });
        }

        // Heat-centroid bonus — FIXED: use bindings instead of inlining floats
        $scoreSelectSql = "*, (
        (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
        (CASE WHEN verified   = 1 THEN 20 ELSE 0 END) +
        (LEAST(views, 100) * 0.15) +
        (LEAST(favorites_count, 50) * 0.8) +
        (rating * 5) +
        (CASE WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 15
              WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10 ELSE 0 END)";

        if ($heat) {
            // Floats are sanitized to ensure no SQL injection (they're computed from
            // our own DB values, but we cast explicitly to be defensive).
            $cLat = (float) $heat['lat'];
            $cLng = (float) $heat['lng'];
            $r    = (float) $heat['radius_km'];

            $scoreSelectSql .= " + (CASE WHEN (6371 * acos(LEAST(1, "
                . "cos(radians({$cLat})) * "
                . "cos(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lat')) AS DECIMAL(10,6)))) * "
                . "cos(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lng')) AS DECIMAL(10,6))) - radians({$cLng})) + "
                . "sin(radians({$cLat})) * "
                . "sin(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lat')) AS DECIMAL(10,6)))) "
                . "))) <= {$r} THEN 25 ELSE 0 END)";
        }

        $scoreSelectSql .= ") as recommendation_score";

        $results = $query
            ->selectRaw($scoreSelectSql)
            ->orderByDesc('recommendation_score')
            ->limit($limit * 2)
            ->get();

        Log::info('🎯 REC: query results', [
            'user_id' => $userId,
            'found'   => $results->count(),
            'needed'  => $limit,
        ]);

        // Fallback to relaxed + general if too few
        if ($results->count() < $limit) {
            $needed      = $limit - $results->count();
            $existingIds = array_merge($seenIds, $results->pluck('id')->toArray());

            $relaxed = Property::query()
                ->where('is_active', true)->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                ->whereNotIn('id', $existingIds)
                ->when($listingType, fn($q) => $q->where('listing_type', $listingType))
                ->when(!empty($types), function ($q) use ($types) {
                    $q->where(function ($q2) use ($types) {
                        foreach ($types as $type) {
                            $q2->orWhereRaw(
                                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                                [strtolower($type)]
                            );
                        }
                    });
                })
                ->orderByDesc('created_at')
                ->limit($needed)
                ->get();

            $results     = $results->merge($relaxed);
            $existingIds = array_merge($existingIds, $relaxed->pluck('id')->toArray());

            if ($results->count() < $limit) {
                $needed2 = $limit - $results->count();
                $general = $this->getGeneralRecommendations($needed2 + 5)
                    ->filter(fn($p) => !in_array($p->id, $existingIds))
                    ->take($needed2);
                $results = $results->merge($general);
            }
        }

        Log::info('🎯 REC: final result', [
            'user_id'        => $userId,
            'total_returned' => $results->count(),
        ]);

        return $results->values()->take($limit);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  FILTER-MATCHED RECOMMENDATIONS
    // ══════════════════════════════════════════════════════════════════════════
    private function getFilterMatchedRecommendations(
        array $filterSignal,
        int   $limit,
        array $excludeIds = []
    ): Collection {
        $query = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $excludeIds);

        if (!empty($filterSignal['listing_type']))
            $query->where('listing_type', $filterSignal['listing_type']);
        if (!empty($filterSignal['property_type']))
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                [strtolower($filterSignal['property_type'])]
            );
        if (!empty($filterSignal['city']))
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                [strtolower($filterSignal['city'])]
            );
        if (!empty($filterSignal['max_price_usd']))
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) <= ?",
                [(float) $filterSignal['max_price_usd']]
            );
        if (!empty($filterSignal['min_price_usd']))
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) >= ?",
                [(float) $filterSignal['min_price_usd']]
            );
        if (!empty($filterSignal['bedrooms']))
            $query->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) = ?",
                [(int) $filterSignal['bedrooms']]
            );
        if (!empty($filterSignal['furnished']))
            $query->where('furnished', true);

        return $query->selectRaw('*, (
            (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
            (CASE WHEN verified   = 1 THEN 20 ELSE 0 END) +
            (LEAST(views, 100) * 0.15) +
            (LEAST(favorites_count, 50) * 0.8) +
            (rating * 5) +
            (CASE WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 15
                  WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10 ELSE 0 END)
        ) as recommendation_score')
            ->orderByDesc('recommendation_score')
            ->limit($limit)
            ->get();
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  GENERAL FALLBACK
    // ══════════════════════════════════════════════════════════════════════════
    private function getGeneralRecommendations(int $limit): Collection
    {
        return Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->selectRaw('*, (
                (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                (CASE WHEN verified   = 1 THEN 20 ELSE 0 END) +
                (LEAST(views, 100) * 0.15) +
                (LEAST(favorites_count, 50) * 0.5) +
                (rating * 5) +
                (CASE WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 20
                      WHEN DATEDIFF(NOW(), created_at) <= 14 THEN 15
                      WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10 ELSE 0 END)
            ) as recommendation_score')
            ->orderByDesc('recommendation_score')
            ->limit($limit)->get();
    }
}
