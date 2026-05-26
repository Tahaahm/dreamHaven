<?php

namespace App\Services;

use App\Models\Property;
use App\Models\UserPropertyInteraction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmartStripService
{
    private const CACHE_TTL      = 600;
    private const SESSION_WINDOW = 24;

    // ──────────────────────────────────────────────────────────────────────────
    //  PUBLIC ENTRY POINT
    // ──────────────────────────────────────────────────────────────────────────

    public function getStrip(string $userId, string $language = 'en'): ?array
    {
        $cacheKey = "smart_strip_{$userId}_{$language}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $language) {
            try {
                $signals = $this->loadSignals($userId);

                Log::info('SmartStrip: signals loaded', [
                    'user_id'               => $userId,
                    'has_filter_signal'     => !empty($signals['filterSignal']),
                    'has_search_signal'     => !empty($signals['searchSignal']),
                    'has_calc_signal'       => !empty($signals['calcSignal']),
                    'recently_viewed_count' => count($signals['recentlyViewedIds']),
                    'favorites_count'       => count($signals['favoriteIds']),
                    'compare_count'         => count($signals['compareIds']),
                    'days_since_visit'      => $signals['daysSinceVisit'],
                    'calc_budget_min'       => $signals['calcSignal']['budget_min_usd'] ?? null,
                    'calc_budget_max'       => $signals['calcSignal']['budget_max_usd'] ?? null,
                ]);

                // ── Priority order ────────────────────────────────────────────
                // 1. budget_match   — calculator signal = strongest buy intent
                // 2. resume_search  — only real filters, NOT bare text searches
                // 3. area_focus     — repeated views in same city
                // 4. new_matches    — new listings matching lifetime profile
                // 5. returning      — came back after 2+ days
                $stripTypes = [
                    'budget_match'      => fn() => $this->tryBudgetMatch($userId, $signals, $language),
                    'resume_search'     => fn() => $this->tryResumeSearch($userId, $signals, $language),
                    'area_focus'        => fn() => $this->tryAreaFocus($userId, $signals, $language),
                    'new_matches'       => fn() => $this->tryNewMatches($userId, $signals, $language),
                    'returning_visitor' => fn() => $this->tryReturningVisitor($userId, $signals, $language),
                ];

                $strip = null;
                foreach ($stripTypes as $typeName => $resolver) {
                    $candidate = $resolver();

                    if ($candidate === null) {
                        Log::info('SmartStrip: strip type skipped', [
                            'user_id' => $userId,
                            'type'    => $typeName,
                            'reason'  => 'returned null (no qualifying data)',
                        ]);
                        continue;
                    }

                    Log::info('SmartStrip: strip type resolved', [
                        'user_id'    => $userId,
                        'type'       => $typeName,
                        'count'      => $candidate['count']      ?? null,
                        'confidence' => $candidate['confidence'] ?? null,
                        'intent'     => $candidate['intent']     ?? null,
                        'filters'    => $candidate['filters']    ?? [],
                        'params'     => $candidate['params']     ?? [],
                    ]);

                    $strip = $candidate;
                    break;
                }

                if (!$strip || ($strip['confidence'] ?? 0) < 0.50) {
                    Log::info('SmartStrip: no strip returned', [
                        'user_id'    => $userId,
                        'reason'     => !$strip
                            ? 'all strip types returned null'
                            : 'confidence below threshold',
                        'confidence' => $strip['confidence'] ?? null,
                    ]);
                    return null;
                }

                Log::info('SmartStrip: strip selected', [
                    'user_id'    => $userId,
                    'language'   => $language,
                    'type'       => $strip['type'],
                    'intent'     => $strip['intent'],
                    'confidence' => $strip['confidence'],
                    'count'      => $strip['count'],
                    'filters'    => $strip['filters'],
                    'params'     => $strip['params'],
                ]);

                return $strip;
            } catch (\Throwable $e) {
                Log::warning('SmartStrip: failed (non-fatal)', [
                    'user_id' => $userId,
                    'error'   => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);
                return null;
            }
        });
    }

    public function invalidate(string $userId): void
    {
        foreach (['en', 'ar', 'ku'] as $lang) {
            Cache::forget("smart_strip_{$userId}_{$lang}");
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  SIGNAL LOADER — unchanged from working version
    // ──────────────────────────────────────────────────────────────────────────

    private function loadSignals(string $userId): array
    {
        $virtualIds = [
            'calculator_signal',
            'filter_signal',
            'search_signal',
            'search_signal_latest',
        ];

        $recentRows = UserPropertyInteraction::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->get();

        $filterRow = $recentRows
            ->where('interaction_type', 'filter_applied')
            ->where('property_id', 'filter_signal')
            ->where('created_at', '>=', now()->subHours(self::SESSION_WINDOW))
            ->first();

        $filterSignal = null;
        if ($filterRow && $filterRow->metadata) {
            $meta = is_array($filterRow->metadata)
                ? $filterRow->metadata
                : json_decode($filterRow->metadata, true);
            $filterSignal = $meta;
        }

        $searchRow = $recentRows
            ->where('interaction_type', 'search_query_latest')
            ->where('created_at', '>=', now()->subHours(self::SESSION_WINDOW))
            ->first();

        $searchSignal = null;
        if ($searchRow && $searchRow->metadata) {
            $meta = is_array($searchRow->metadata)
                ? $searchRow->metadata
                : json_decode($searchRow->metadata, true);
            $searchSignal = $meta;
        }

        $calcRow = UserPropertyInteraction::where('user_id', $userId)
            ->where('interaction_type', 'calculator_search')
            ->where('property_id', 'calculator_signal')
            ->where('created_at', '>=', now()->subDays(90))
            ->latest()
            ->first();

        $calcSignal = null;
        if ($calcRow && $calcRow->metadata) {
            $meta = is_array($calcRow->metadata)
                ? $calcRow->metadata
                : json_decode($calcRow->metadata, true);
            $calcSignal = $meta;
        }

        $recentlyViewedIds = $recentRows
            ->where('interaction_type', 'view')
            ->whereNotIn('property_id', $virtualIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->pluck('property_id')
            ->unique()
            ->values()
            ->toArray();

        $favoriteIds = $recentRows
            ->where('interaction_type', 'favorite')
            ->whereNotIn('property_id', $virtualIds)
            ->pluck('property_id')
            ->unique()
            ->values()
            ->toArray();

        $compareIds = $recentRows
            ->where('interaction_type', 'compare')
            ->whereNotIn('property_id', $virtualIds)
            ->pluck('property_id')
            ->unique()
            ->values()
            ->toArray();

        $user           = User::find($userId);
        $lastSeenAt     = $user?->last_activity_at ?? $user?->updated_at;
        $daysSinceVisit = $lastSeenAt
            ? (int) now()->diffInDays($lastSeenAt)
            : 999;

        return compact(
            'filterSignal',
            'searchSignal',
            'calcSignal',
            'recentlyViewedIds',
            'favoriteIds',
            'compareIds',
            'daysSinceVisit',
            'user'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STRIP TYPE 1: BUDGET MATCH
    //  "Within your budget — $52K–$78K"
    //  Triggered: user used calculator. Strongest buy-intent signal.
    // ──────────────────────────────────────────────────────────────────────────

    private function tryBudgetMatch(string $userId, array $signals, string $lang): ?array
    {
        $calc = $signals['calcSignal'];
        if (!$calc) {
            Log::info('SmartStrip[budget_match]: skip — no calculator signal', ['user_id' => $userId]);
            return null;
        }
        if (empty($calc['budget_min_usd']) || empty($calc['budget_max_usd'])) {
            Log::info('SmartStrip[budget_match]: skip — calc signal missing budget range', [
                'user_id' => $userId,
                'calc_keys' => array_keys($calc),
            ]);
            return null;
        }

        $minUsd = (float) $calc['budget_min_usd'];
        $maxUsd = (float) $calc['budget_max_usd'];

        // ── Currency detection ────────────────────────────────────────────────
        // Properties in Dream Mulk are priced in IQD (Iraqi Dinar).
        // The calculator stores budget in USD. We need to check both:
        //  (a) Direct match: property.price is already in USD scale (< 10,000,000)
        //  (b) IQD match:    property.price is in IQD (multiply USD by ~1300)
        // We detect which by checking the median property price in the DB.
        $sampleMedian = \DB::table('properties')
            ->where('is_active', true)
            ->where('published', true)
            ->whereIn('listing_type', ['sell'])
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->selectRaw('AVG(price) as avg_price')
            ->value('avg_price');

        // If median price > 1,000,000 — properties are stored in IQD
        // Convert USD budget → IQD for comparison (1 USD ≈ 1300 IQD)
        $iqd_rate = 1300;
        $isIqd    = ($sampleMedian !== null && $sampleMedian > 1_000_000);

        if ($isIqd) {
            $min = $minUsd * $iqd_rate;
            $max = $maxUsd * $iqd_rate;
        } else {
            $min = $minUsd;
            $max = $maxUsd;
        }

        Log::info('SmartStrip[budget_match]: currency detection', [
            'user_id'       => $userId,
            'budget_usd'    => ['min' => $minUsd, 'max' => $maxUsd],
            'is_iqd'        => $isIqd,
            'sample_median' => $sampleMedian,
            'query_range'   => ['min' => $min, 'max' => $max],
        ]);

        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('listing_type', 'sell')
            ->where('price', '>=', $min)
            ->where('price', '<=', $max)
            ->orderByDesc('created_at');

        $count = $query->count();
        if ($count === 0) {
            Log::info('SmartStrip[budget_match]: skip — 0 properties in budget range', [
                'user_id' => $userId,
                'min' => $min,
                'max' => $max,
                'is_iqd' => $isIqd,
            ]);
            return null;
        }

        $topProperties = $query->with('owner')->limit(5)->get();

        $filters = [
            'listing_type' => 'sell',
            'min_price'    => (int) $minUsd, // always return USD to Flutter
            'max_price'    => (int) $maxUsd,
        ];

        // Infer property type from user's viewed/compared/favorited history
        $inferredType = $this->inferPropertyType($signals);
        if ($inferredType) $filters['property_type'] = $inferredType;

        $signalStrength = (int) ($calc['signal_strength'] ?? 50);
        $confidence     = min(0.60 + ($signalStrength / 100 * 0.30), 0.90);

        return [
            'type'       => 'budget_match',
            'intent'     => 'active_buyer',
            'confidence' => $confidence,
            'icon'       => 'wallet',
            'headline'   => 'budget_match_headline',
            'subline'    => 'budget_match_subline',
            'params'     => [
                'min_price' => (int) $min,
                'max_price' => (int) $max,
                'count'     => $count,
            ],
            'filters'    => $filters,
            'count'      => $count,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STRIP TYPE 2: RESUME SEARCH
    //  "Continue: Villa · For Sale · Erbil — 12 new"
    //
    //  IMPORTANT: Only fires for REAL structured filters, NOT bare text searches.
    //  A bare search ("user typed Erbil") is too weak — we skip it.
    //  Only fires when:
    //    (a) User applied structured filters (listing_type / property_type / price)
    //    (b) Search had active_filters set (not empty)
    //    (c) User searched the same query 2+ times in 48h (repeated intent)
    // ──────────────────────────────────────────────────────────────────────────

    private function tryResumeSearch(string $userId, array $signals, string $lang): ?array
    {
        // Prefer structured filter signal over bare search signal
        $filter = $signals['filterSignal'];

        if ($filter) {
            // ── Guard: skip filter signals that are all defaults (user opened
            //    modal but changed nothing — listing_type="All", property_type="All",
            //    bedrooms=0, no price range). These carry zero intent.
            $isJunkFilter = (
                (empty($filter['listing_type'])  || strtolower($filter['listing_type'])  === 'all') &&
                (empty($filter['property_type']) || strtolower($filter['property_type']) === 'all') &&
                empty($filter['city'])           &&
                empty($filter['min_price'])      &&
                empty($filter['max_price'])      &&
                (empty($filter['bedrooms'])      || (int) $filter['bedrooms'] === 0)
            );

            if ($isJunkFilter) {
                Log::info('SmartStrip[resume_search]: skip — filter signal is all-defaults (no real intent)', [
                    'user_id' => $userId,
                    'filter'  => $filter,
                ]);
                $filter = null; // fall through to check search signal instead
            }
        }

        if (!$filter) {
            $searchSignal = $signals['searchSignal'];
            if (!$searchSignal) {
                Log::info('SmartStrip[resume_search]: skip — no filter or search signal', ['user_id' => $userId]);
                return null;
            }

            // Check if search had structured filters attached
            $activeFilters    = $searchSignal['active_filters'] ?? [];
            $hasActiveFilters = is_array($activeFilters) && count($activeFilters) > 0;

            if (!$hasActiveFilters) {
                // Bare search — only resume if user searched same query 2+ times
                $rawQuery = $searchSignal['query'] ?? '';
                if (empty($rawQuery)) return null;

                $repeatCount = UserPropertyInteraction::where('user_id', $userId)
                    ->where('interaction_type', 'search_query_latest')
                    ->where('created_at', '>=', now()->subHours(48))
                    ->whereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.query'))) = ?",
                        [strtolower($rawQuery)]
                    )
                    ->count();

                if ($repeatCount < 2) {
                    Log::info('SmartStrip[resume_search]: skip — bare search query only once', [
                        'user_id' => $userId,
                        'query' => $rawQuery,
                        'repeat_count' => $repeatCount,
                    ]);
                    return null;
                }
            }

            $filter = $searchSignal;
        }

        // ── Normalise signal into flat filter map ─────────────────────────────
        $filtersApplied = $filter['filters'] ?? $filter;
        $rawQuery       = $filter['query']   ?? ($filtersApplied['query'] ?? '');

        // If city not set but we have a query string, promote it to city
        if (empty($filtersApplied['city']) && !empty($rawQuery)) {
            $filtersApplied['city'] = ucfirst(strtolower($rawQuery));
        }

        // ── Build property query ──────────────────────────────────────────────
        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented']);

        if (
            !empty($filtersApplied['listing_type'])
            && strtolower($filtersApplied['listing_type']) !== 'all'
        ) {
            $query->where('listing_type', $filtersApplied['listing_type']);
        }
        if (
            !empty($filtersApplied['property_type'])
            && strtolower($filtersApplied['property_type']) !== 'all'
        ) {
            $query->where('property_type', $filtersApplied['property_type']);
        }
        if (!empty($filtersApplied['city'])) {
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                [strtolower($filtersApplied['city'])]
            );
        }
        if (!empty($filtersApplied['min_price'])) {
            $query->where('price', '>=', $filtersApplied['min_price']);
        }
        if (!empty($filtersApplied['max_price'])) {
            $query->where('price', '<=', $filtersApplied['max_price']);
        }
        if (
            !empty($filtersApplied['bedrooms'])
            && $filtersApplied['bedrooms'] !== '0'
            && (int) $filtersApplied['bedrooms'] > 0
        ) {
            $query->where('bedrooms', '>=', (int) $filtersApplied['bedrooms']);
        }

        $filterTimestamp   = $signals['filterSignal']
            ? now()->subHours(self::SESSION_WINDOW)
            : now()->subDays(2);
        $newSinceLastVisit = (clone $query)
            ->where('created_at', '>=', $filterTimestamp)
            ->count();

        $totalCount = $query->count();
        if ($totalCount === 0) {
            Log::info('SmartStrip[resume_search]: skip — 0 properties match filters', [
                'user_id' => $userId,
                'filters_applied' => $filtersApplied,
            ]);
            return null;
        }

        $topProperties = $query->orderByDesc('created_at')->with('owner')->limit(5)->get();

        // Build label parts — only non-empty, non-"All" values
        $labelParts = array_values(array_filter([
            (!empty($filtersApplied['property_type'])
                && strtolower($filtersApplied['property_type']) !== 'all')
                ? ucfirst($filtersApplied['property_type']) : null,
            (!empty($filtersApplied['listing_type'])
                && strtolower($filtersApplied['listing_type']) !== 'all')
                ? ucfirst($filtersApplied['listing_type']) : null,
            !empty($filtersApplied['city'])
                ? ucfirst($filtersApplied['city']) : null,
        ]));

        if (empty($labelParts) && !empty($rawQuery)) {
            $labelParts = [ucfirst($rawQuery)];
        }

        return [
            'type'       => 'resume_search',
            'intent'     => 'active_searcher',
            'confidence' => 0.85,
            'icon'       => 'search',
            'headline'   => 'resume_search_headline',
            'subline'    => 'resume_search_subline',
            'params'     => [
                'label_parts'     => $labelParts,
                'total_count'     => $totalCount,
                'new_since_visit' => $newSinceLastVisit,
            ],
            'filters'    => $filtersApplied,
            'count'      => $totalCount,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STRIP TYPE 3: AREA FOCUS
    //  "You keep looking in Erbil — 34 available"
    // ──────────────────────────────────────────────────────────────────────────

    private function tryAreaFocus(string $userId, array $signals, string $lang): ?array
    {
        if (count($signals['recentlyViewedIds']) < 3) {
            Log::info('SmartStrip[area_focus]: skip — fewer than 3 recently viewed properties', [
                'user_id' => $userId,
                'viewed_count' => count($signals['recentlyViewedIds']),
            ]);
            return null;
        }

        $viewedProperties = Property::whereIn('id', $signals['recentlyViewedIds'])
            ->whereNotNull('address_details')
            ->get(['id', 'address_details', 'listing_type', 'property_type']);

        if ($viewedProperties->isEmpty()) return null;

        $cityCounts = [];
        foreach ($viewedProperties as $prop) {
            $addr = is_array($prop->address_details)
                ? $prop->address_details
                : json_decode($prop->address_details, true);
            $city = $addr['city']['en'] ?? null;
            if ($city) $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
        }

        if (empty($cityCounts)) return null;
        arsort($cityCounts);
        $topCity      = array_key_first($cityCounts);
        $topCityCount = $cityCounts[$topCity];

        if ($topCityCount < 3) {
            Log::info('SmartStrip[area_focus]: skip — no dominant city (max views in one city < 3)', [
                'user_id' => $userId,
                'top_city' => $topCity,
                'top_city_count' => $topCityCount,
            ]);
            return null;
        }

        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                [strtolower($topCity)]
            )
            ->whereNotIn('id', $signals['recentlyViewedIds'])
            ->orderByDesc('created_at');

        $count = $query->count();
        if ($count === 0) {
            Log::info('SmartStrip[area_focus]: skip — 0 unseen properties in dominant city', [
                'user_id' => $userId,
                'city' => $topCity,
            ]);
            return null;
        }

        $topProperties       = $query->with('owner')->limit(5)->get();
        $inferredListingType = $this->inferListingType($viewedProperties);
        $inferredPropType    = $this->inferPropertyType($signals);

        $filters = ['city' => $topCity];
        if ($inferredListingType) $filters['listing_type'] = $inferredListingType;
        if ($inferredPropType)    $filters['property_type'] = $inferredPropType;

        $confidence = min(0.55 + ($topCityCount * 0.05), 0.85);

        return [
            'type'       => 'area_focus',
            'intent'     => 'location_focused',
            'confidence' => $confidence,
            'icon'       => 'location',
            'headline'   => 'area_focus_headline',
            'subline'    => 'area_focus_subline',
            'params'     => [
                'city'       => $topCity,
                'view_count' => $topCityCount,
                'count'      => $count,
            ],
            'filters'    => $filters,
            'count'      => $count,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STRIP TYPE 4: NEW MATCHES
    //  "New listings matching your style"
    // ──────────────────────────────────────────────────────────────────────────

    private function tryNewMatches(string $userId, array $signals, string $lang): ?array
    {
        $allInteractedIds = array_unique(array_merge(
            $signals['favoriteIds'],
            $signals['compareIds'],
            $signals['recentlyViewedIds']
        ));

        if (count($allInteractedIds) < 2) {
            Log::info('SmartStrip[new_matches]: skip — fewer than 2 interacted properties', [
                'user_id' => $userId,
                'interacted_count' => count($allInteractedIds),
            ]);
            return null;
        }

        $interactedProps = Property::whereIn('id', $allInteractedIds)
            ->get(['id', 'listing_type', 'property_type', 'address_details', 'price', 'bedrooms']);

        if ($interactedProps->isEmpty()) return null;

        $dominantListingType = $interactedProps->groupBy('listing_type')
            ->map->count()->sortDesc()->keys()->first();
        $dominantPropType    = $interactedProps->groupBy('property_type')
            ->map->count()->sortDesc()->keys()->first();

        $prices   = $interactedProps->pluck('price')->filter()->sort()->values();
        $medianPx = $prices->count() > 0
            ? $prices->get((int) floor($prices->count() / 2))
            : null;

        $lastActive = $signals['user']?->last_activity_at ?? now()->subDays(1);

        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('created_at', '>=', $lastActive)
            ->whereNotIn('id', $allInteractedIds);

        if ($dominantListingType) $query->where('listing_type', $dominantListingType);
        if ($dominantPropType)    $query->where('property_type', $dominantPropType);
        if ($medianPx) {
            $query->where('price', '>=', $medianPx * 0.65)
                ->where('price', '<=', $medianPx * 1.40);
        }

        $count = $query->count();
        if ($count === 0) {
            Log::info('SmartStrip[new_matches]: skip — 0 new listings match profile', [
                'user_id'      => $userId,
                'listing_type' => $dominantListingType,
                'prop_type'    => $dominantPropType,
                'median_price' => $medianPx,
            ]);
            return null;
        }

        $topProperties = $query->orderByDesc('created_at')->with('owner')->limit(5)->get();

        $filters = [];
        if ($dominantListingType) $filters['listing_type'] = $dominantListingType;
        if ($dominantPropType)    $filters['property_type'] = $dominantPropType;
        if ($medianPx) {
            $filters['min_price'] = (int) ($medianPx * 0.65);
            $filters['max_price'] = (int) ($medianPx * 1.40);
        }

        return [
            'type'       => 'new_matches',
            'intent'     => 'casual_browser',
            'confidence' => min(0.55 + ($count * 0.02), 0.80),
            'icon'       => 'sparkles',
            'headline'   => 'new_matches_headline',
            'subline'    => 'new_matches_subline',
            'params'     => [
                'count'        => $count,
                'listing_type' => $dominantListingType,
                'prop_type'    => $dominantPropType,
            ],
            'filters'    => $filters,
            'count'      => $count,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STRIP TYPE 5: RETURNING VISITOR
    //  "Welcome back — 47 new listings"
    // ──────────────────────────────────────────────────────────────────────────

    private function tryReturningVisitor(string $userId, array $signals, string $lang): ?array
    {
        if ($signals['daysSinceVisit'] < 2) {
            Log::info('SmartStrip[returning_visitor]: skip — visited less than 2 days ago', [
                'user_id' => $userId,
                'days_since_visit' => $signals['daysSinceVisit'],
            ]);
            return null;
        }

        $lastActive = $signals['user']?->last_activity_at
            ?? now()->subDays($signals['daysSinceVisit']);

        $newCount = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('created_at', '>=', $lastActive)
            ->count();

        if ($newCount === 0) {
            Log::info('SmartStrip[returning_visitor]: skip — no new listings since last visit', [
                'user_id' => $userId,
                'last_active' => $lastActive,
            ]);
            return null;
        }

        $topProperties = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('created_at', '>=', $lastActive)
            ->orderByDesc('created_at')
            ->with('owner')
            ->limit(5)
            ->get();

        return [
            'type'       => 'returning_visitor',
            'intent'     => 'returning',
            'confidence' => 0.55,
            'icon'       => 'wave',
            'headline'   => 'returning_visitor_headline',
            'subline'    => 'returning_visitor_subline',
            'params'     => [
                'days_away' => $signals['daysSinceVisit'],
                'new_count' => $newCount,
            ],
            'filters'    => [],
            'count'      => $newCount,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  HELPERS — unchanged
    // ──────────────────────────────────────────────────────────────────────────

    private function inferPropertyType(array $signals): ?string
    {
        $ids = array_unique(array_merge(
            $signals['favoriteIds'],
            $signals['compareIds'],
            $signals['recentlyViewedIds']
        ));
        if (empty($ids)) return null;

        $counts = Property::whereIn('id', $ids)
            ->selectRaw('property_type, COUNT(*) as cnt')
            ->groupBy('property_type')
            ->orderByDesc('cnt')
            ->first();

        return $counts?->property_type;
    }

    private function inferListingType(Collection $properties): ?string
    {
        if ($properties->isEmpty()) return null;
        return $properties->groupBy('listing_type')
            ->map->count()->sortDesc()->keys()->first();
    }

    private function buildFiltersFromProperties(Collection $properties): array
    {
        if ($properties->isEmpty()) return [];
        $first = $properties->first();
        $addr  = is_array($first->address_details)
            ? $first->address_details
            : json_decode($first->address_details, true);

        $filters = [];
        if ($first->listing_type)       $filters['listing_type']  = $first->listing_type;
        if ($first->property_type)      $filters['property_type'] = $first->property_type;
        if (!empty($addr['city']['en'])) $filters['city']          = $addr['city']['en'];
        return $filters;
    }

    private function transformProperties(Collection $properties, string $lang): array
    {
        return $properties->map(function ($property) use ($lang) {
            $addr = is_array($property->address_details)
                ? $property->address_details
                : json_decode($property->address_details ?? '{}', true);

            $cityKey  = match ($lang) {
                'ar' => 'ar',
                'ku' => 'ku',
                default => 'en'
            };
            $cityName = $addr['city'][$cityKey] ?? $addr['city']['en'] ?? '';

            $images = [];
            if (!empty($property->images)) {
                $raw = is_array($property->images)
                    ? $property->images
                    : json_decode($property->images, true);
                if (is_array($raw)) {
                    $images = collect($raw)
                        ->map(fn($img) => is_array($img) ? ($img['url'] ?? null) : $img)
                        ->filter()->values()->toArray();
                }
            }

            return [
                'id'            => $property->id,
                'name'          => $property->name          ?? '',
                'price'         => (float) ($property->price ?? 0),
                'listing_type'  => $property->listing_type  ?? '',
                'property_type' => $property->property_type ?? '',
                'city'          => $cityName,
                'image'         => $images[0] ?? null,
                'is_verified'   => (bool) ($property->verified ?? false),
                'bedrooms'      => (int) ($property->bedrooms  ?? 0),
            ];
        })->toArray();
    }
}