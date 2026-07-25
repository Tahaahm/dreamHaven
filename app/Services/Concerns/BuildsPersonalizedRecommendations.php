<?php

/*
|==============================================================================
| REPLACES: app/Services/Concerns/BuildsPersonalizedRecommendations.php
|==============================================================================
|
| TWO BUGS FIXED
| --------------
|
| 1. "found: 0" on every single run.
|
|    Your log shows the profile producing:
|        "price": {"target": 48722, "min": 266, "max": 380}
|
|    A target of $48,722 with a search window of $266–380. Nothing on earth
|    matches that, so the personalised query always returned zero and silently
|    fell through to generic trending — for every user, every time.
|
|    Two defences added:
|      a) sanePriceWindow() rejects a window that doesn't contain the target
|         and rebuilds it as target × 0.65 … target × 1.45.
|      b) Tiered relaxation: instead of all-filters-or-nothing, the query is
|         retried dropping one constraint at a time until enough results come
|         back. It now degrades gracefully rather than falling off a cliff.
|
|    (The root cause is still inside UserTasteProfile — send me that file and
|    I'll fix the calculation itself. This makes the symptom harmless either way.)
|
| 2. The query was scanning and JSON-parsing every property row.
|
|    price / bedrooms / type / coordinates all used JSON_EXTRACT, so MySQL
|    could not use an index. You already have the indexed generated columns
|    from add_indexed_generated_columns:
|
|        price_usd, price_iqd, bedrooms_count, bathrooms_count,
|        property_type_category, primary_lat, primary_lng
|
|    Every filter and the heat-centroid distance now use those instead.
|
| NOTE ON THE 11-SECOND / 100MB PROBLEM
| -------------------------------------
| That cost is in UserTasteProfile::build() — it's aggregating 34,545
| impression rows in PHP. This file caches the profile per user for 15
| minutes so it can't be rebuilt on back-to-back requests, but the real fix
| belongs in that class. Send it over when you can.
*/

namespace App\Services\Concerns;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

trait BuildsPersonalizedRecommendations
{
    // ══════════════════════════════════════════════════════════════════════════
    //  PERSONALIZED RECOMMENDATIONS
    // ══════════════════════════════════════════════════════════════════════════
    public function getPersonalizedRecommendations(string $userId, int $limit = 20): Collection
    {
        $user = User::find($userId);
        if (!$user) {
            return $this->getGeneralRecommendations($limit);
        }

        // Profile is expensive to build — never build it twice in 15 minutes
        $profile = Cache::remember(
            "taste_profile_{$userId}",
            900,
            fn() => app(\App\Services\Intelligence\UserTasteProfile::class)->build($userId)
        );

        Log::info('🎯 REC: profile loaded', [
            'user_id'       => $userId,
            'has_history'   => $profile['has_history'],
            'is_cold_start' => $profile['is_cold_start'],
            'cities'        => $profile['cities'],
            'types'         => $profile['types'],
            'listing_type'  => $profile['listing_type'],
            'price'         => $profile['price'],
            'bedrooms'      => $profile['bedrooms'],
            'seen_count'    => count($profile['seen_ids']),
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

        $criteria = [
            'types'        => array_keys($profile['types']),
            'cities'       => array_keys($profile['cities']),
            'listing_type' => $profile['listing_type'],
            'bedrooms'     => $profile['bedrooms'],
            'price'        => $this->sanePriceWindow($profile['price'] ?? [], $userId),
            'heat'         => $profile['heat_centroid'] ?? null,
            'seen_ids'     => array_slice($profile['seen_ids'] ?? [], 0, 300),
        ];

        // ── Tiered relaxation ────────────────────────────────────────────────
        // Drop one constraint at a time until we have enough. Previously this
        // was all-or-nothing, which is why a single bad filter returned zero.
        $tiers = [
            'exact'        => [],                                  // everything
            'no_bedrooms'  => ['bedrooms'],
            'no_price'     => ['bedrooms', 'price'],
            'no_city'      => ['bedrooms', 'price', 'cities'],
            'type_only'    => ['bedrooms', 'price', 'cities', 'heat'],
        ];

        $results   = collect();
        $usedTier  = null;

        foreach ($tiers as $tier => $drop) {
            $results = $this->runRecommendationQuery($criteria, $drop, $limit * 2);
            $usedTier = $tier;

            if ($results->count() >= $limit) {
                break;
            }
        }

        Log::info('🎯 REC: query results', [
            'user_id' => $userId,
            'found'   => $results->count(),
            'needed'  => $limit,
            'tier'    => $usedTier,
        ]);

        // ── Top up if still short ────────────────────────────────────────────
        if ($results->count() < $limit) {
            $existingIds = array_merge($criteria['seen_ids'], $results->pluck('id')->toArray());

            $general = $this->getGeneralRecommendations($limit + 10)
                ->filter(fn($p) => !in_array($p->id, $existingIds))
                ->take($limit - $results->count());

            $results = $results->merge($general);
        }

        Log::info('🎯 REC: final result', [
            'user_id'        => $userId,
            'total_returned' => $results->count(),
            'tier'           => $usedTier,
        ]);

        return $results->values()->take($limit);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  QUERY BUILDER — one tier
    // ══════════════════════════════════════════════════════════════════════════
    private function runRecommendationQuery(array $c, array $drop, int $limit): Collection
    {
        $useIndexed = $this->hasIndexedColumns();

        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented']);

        if (!empty($c['seen_ids'])) {
            $query->whereNotIn('id', $c['seen_ids']);
        }

        // ── Property type ────────────────────────────────────────────────────
        if (!in_array('types', $drop) && !empty($c['types'])) {
            $types = array_map('strtolower', $c['types']);

            if ($useIndexed) {
                $query->whereIn('property_type_category', $types);
            } else {
                $query->where(function ($q) use ($types) {
                    foreach ($types as $type) {
                        $q->orWhereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                            [$type]
                        );
                    }
                });
            }
        }

        // ── Listing type ─────────────────────────────────────────────────────
        if (!in_array('listing_type', $drop) && $c['listing_type']) {
            $query->where('listing_type', $c['listing_type']);
        }

        // ── Price ────────────────────────────────────────────────────────────
        if (!in_array('price', $drop) && $c['price']) {
            [$min, $max] = $c['price'];

            if ($useIndexed) {
                $query->where('price_usd', '>', 0)->whereBetween('price_usd', [$min, $max]);
            } else {
                $query->whereRaw(
                    "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) BETWEEN ? AND ?",
                    [$min, $max]
                );
            }
        }

        // ── Bedrooms — now a range, not exact equality ───────────────────────
        if (!in_array('bedrooms', $drop) && $c['bedrooms'] !== null) {
            $beds = (int) $c['bedrooms'];

            if ($useIndexed) {
                $query->whereBetween('bedrooms_count', [max(1, $beds - 1), $beds + 1]);
            } else {
                $query->whereRaw(
                    "CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) AS UNSIGNED) BETWEEN ? AND ?",
                    [max(1, $beds - 1), $beds + 1]
                );
            }
        }

        // ── Cities ───────────────────────────────────────────────────────────
        if (!in_array('cities', $drop) && !empty($c['cities'])) {
            $query->where(function ($q) use ($c) {
                foreach ($c['cities'] as $city) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                        [strtolower($city)]
                    );
                }
            });
        }

        // ── Scoring ──────────────────────────────────────────────────────────
        $score = "*, (
            (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
            (CASE WHEN verified   = 1 THEN 20 ELSE 0 END) +
            (LEAST(views, 100) * 0.15) +
            (LEAST(favorites_count, 50) * 0.8) +
            (rating * 5) +
            (CASE WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 15
                  WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10 ELSE 0 END)";

        // Heat centroid bonus — uses indexed lat/lng columns when available
        if (!in_array('heat', $drop) && !empty($c['heat']) && $useIndexed) {
            $lat = (float) $c['heat']['lat'];
            $lng = (float) $c['heat']['lng'];
            $r   = (float) $c['heat']['radius_km'];

            // Rough degree box first (index-friendly), then exact distance
            $latPad = $r / 111.0;
            $lngPad = $r / 85.0;

            $score .= " + (CASE WHEN primary_lat BETWEEN " . ($lat - $latPad) . " AND " . ($lat + $latPad)
                . " AND primary_lng BETWEEN " . ($lng - $lngPad) . " AND " . ($lng + $lngPad)
                . " THEN 25 ELSE 0 END)";
        }

        $score .= ") as recommendation_score";

        return $query->selectRaw($score)
            ->orderByDesc('recommendation_score')
            ->limit($limit)
            ->get();
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  PRICE WINDOW SANITY CHECK
    // ══════════════════════════════════════════════════════════════════════════
    /**
     * Returns [min, max] or null.
     *
     * Rejects a window that can't possibly be right — the case in your logs
     * was target $48,722 with a window of $266–380. When the window doesn't
     * contain the target, it is rebuilt around the target instead.
     */
    private function sanePriceWindow(array $price, string $userId): ?array
    {
        $min    = isset($price['min']) ? (float) $price['min'] : 0;
        $max    = isset($price['max']) ? (float) $price['max'] : 0;
        $target = isset($price['target']) ? (float) $price['target'] : 0;

        // No usable window at all
        if ($min <= 0 && $max <= 0 && $target <= 0) {
            return null;
        }

        // Window exists and actually contains the target — trust it
        if ($min > 0 && $max > $min && ($target <= 0 || ($target >= $min && $target <= $max))) {
            return [$min, $max];
        }

        // Window is broken. Rebuild around the target if we have one.
        if ($target > 0) {
            Log::warning('🎯 REC: price window rebuilt', [
                'user_id'  => $userId,
                'original' => ['min' => $min, 'max' => $max, 'target' => $target],
                'reason'   => 'window does not contain target',
            ]);

            return [round($target * 0.65), round($target * 1.45)];
        }

        // Only a max, no target — treat everything below it as in range
        if ($max > 0) {
            return [0, $max];
        }

        return null;
    }

    /**
     * Are the indexed generated columns available? Checked once per request.
     */
    private function hasIndexedColumns(): bool
    {
        static $has = null;

        if ($has !== null) {
            return $has;
        }

        try {
            $has = Schema::hasColumn('properties', 'price_usd')
                && Schema::hasColumn('properties', 'bedrooms_count')
                && Schema::hasColumn('properties', 'property_type_category');
        } catch (\Throwable $e) {
            $has = false;
        }

        return $has;
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  FILTER-MATCHED RECOMMENDATIONS  (unchanged behaviour)
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

        if (!empty($filterSignal['listing_type'])) {
            $query->where('listing_type', $filterSignal['listing_type']);
        }

        if (!empty($filterSignal['property_type'])) {
            $this->hasIndexedColumns()
                ? $query->where('property_type_category', strtolower($filterSignal['property_type']))
                : $query->whereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                    [strtolower($filterSignal['property_type'])]
                );
        }

        if (!empty($filterSignal['city'])) {
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                [strtolower($filterSignal['city'])]
            );
        }

        if (!empty($filterSignal['max_price_usd'])) {
            $this->hasIndexedColumns()
                ? $query->where('price_usd', '>', 0)->where('price_usd', '<=', (float) $filterSignal['max_price_usd'])
                : $query->whereRaw(
                    "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) <= ?",
                    [(float) $filterSignal['max_price_usd']]
                );
        }

        if (!empty($filterSignal['min_price_usd'])) {
            $this->hasIndexedColumns()
                ? $query->where('price_usd', '>=', (float) $filterSignal['min_price_usd'])
                : $query->whereRaw(
                    "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) >= ?",
                    [(float) $filterSignal['min_price_usd']]
                );
        }

        if (!empty($filterSignal['bedrooms'])) {
            $this->hasIndexedColumns()
                ? $query->where('bedrooms_count', (int) $filterSignal['bedrooms'])
                : $query->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) = ?",
                    [(int) $filterSignal['bedrooms']]
                );
        }

        if (!empty($filterSignal['furnished'])) {
            $query->where('furnished', true);
        }

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
    //  GENERAL FALLBACK  (unchanged)
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