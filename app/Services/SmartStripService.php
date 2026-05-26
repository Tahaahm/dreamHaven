<?php

namespace App\Services;

use App\Models\Property;
use App\Models\UserPropertyInteraction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// ═══════════════════════════════════════════════════════════════════════════════
//  SmartStripService
//
//  Powers GET /api/v1/properties/smart-strip
//
//  Reads all 6 user signals and returns ONE high-confidence strip object.
//  The strip contains:
//   - type:        what kind of strip (resume_search | budget_match |
//                  price_drop | area_focus | new_matches | returning_visitor)
//   - headline:    short label (EN, will be translated on Flutter side)
//   - subline:     one-line description
//   - filters:     the pre-filled filter context so Flutter can open
//                  SearchPage / SeeMore with correct filters applied
//   - count:       how many properties match right now
//   - properties:  top 5 matching properties (for preview thumbnails)
//   - confidence:  0.0–1.0 (Flutter hides strip if < 0.5)
//   - intent:      detected micro-segment for analytics
//
//  SIGNAL PRIORITY (highest confidence wins):
//   1. price_drop    — user viewed a property and it dropped in price (very high intent)
//   2. resume_search — user applied filters in last 24h (high session intent)
//   3. budget_match  — user used calculator (strong buy intent)
//   4. area_focus    — user repeatedly viewed same city/area (location-locked)
//   5. new_matches   — user has lifetime preferences, new listings match them
//   6. returning     — user came back after > 2 days (re-engagement)
// ═══════════════════════════════════════════════════════════════════════════════

class SmartStripService
{
    // Cache TTL — strip result is cached per user for 10 minutes
    private const CACHE_TTL      = 600;
    // Session window for "resume search" — 24 hours
    private const SESSION_WINDOW = 24;
    // Price drop threshold — show strip if price dropped >= 3%
    private const DROP_THRESHOLD = 0.03;

    // ──────────────────────────────────────────────────────────────────────────
    //  PUBLIC ENTRY POINT
    // ──────────────────────────────────────────────────────────────────────────

    public function getStrip(string $userId, string $language = 'en'): ?array
    {
        $cacheKey = "smart_strip_{$userId}_{$language}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $language) {
            try {
                // Load all signals in parallel (single DB round-trip each)
                $signals = $this->loadSignals($userId);

                // Try each strip type in priority order
                // budget_match first — calculator signal = strongest buy intent
                // resume_search second — but only fires for real filters, not bare searches
                $strip = $this->tryPriceDrop($userId, $signals, $language)
                    ?? $this->tryBudgetMatch($userId, $signals, $language)
                    ?? $this->tryResumeSearch($userId, $signals, $language)
                    ?? $this->tryAreaFocus($userId, $signals, $language)
                    ?? $this->tryNewMatches($userId, $signals, $language)
                    ?? $this->tryReturningVisitor($userId, $signals, $language);

                // Below confidence threshold → return null (Flutter hides strip)
                if (!$strip || ($strip['confidence'] ?? 0) < 0.50) {
                    return null;
                }

                return $strip;
            } catch (\Throwable $e) {
                Log::warning('SmartStrip failed (non-fatal)', [
                    'user_id' => $userId,
                    'error'   => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    // Invalidate cache when user performs a new action
    public function invalidate(string $userId): void
    {
        foreach (['en', 'ar', 'ku'] as $lang) {
            Cache::forget("smart_strip_{$userId}_{$lang}");
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  SIGNAL LOADER — single method, all signals in one place
    // ──────────────────────────────────────────────────────────────────────────

    private function loadSignals(string $userId): array
    {
        $virtualIds = [
            'calculator_signal',
            'filter_signal',
            'search_signal',
            'search_signal_latest',
        ];

        // ── Recent interactions (last 30 days) ───────────────────────────────
        $recentRows = UserPropertyInteraction::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->get();

        // ── Filter signal (last 24h — session level) ─────────────────────────
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

        // ── Search signal (last 24h) ─────────────────────────────────────────
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

        // ── Calculator signal (last 90 days) ─────────────────────────────────
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

        // ── Recently viewed property IDs (last 7 days) ───────────────────────
        $recentlyViewedIds = $recentRows
            ->where('interaction_type', 'view')
            ->whereNotIn('property_id', $virtualIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->pluck('property_id')
            ->unique()
            ->values()
            ->toArray();

        // ── Favorites (all time) ─────────────────────────────────────────────
        $favoriteIds = $recentRows
            ->where('interaction_type', 'favorite')
            ->whereNotIn('property_id', $virtualIds)
            ->pluck('property_id')
            ->unique()
            ->values()
            ->toArray();

        // ── Compare (last 30 days) ────────────────────────────────────────────
        $compareIds = $recentRows
            ->where('interaction_type', 'compare')
            ->whereNotIn('property_id', $virtualIds)
            ->pluck('property_id')
            ->unique()
            ->values()
            ->toArray();

        // ── User last_seen_at ─────────────────────────────────────────────────
        $user          = User::find($userId);
        $lastSeenAt    = $user?->last_activity_at ?? $user?->updated_at;
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
    //  STRIP TYPE 1: PRICE DROP
    //  "2 properties you viewed dropped in price"
    //  Triggered: user viewed >= 1 property in last 7 days that has dropped
    // ──────────────────────────────────────────────────────────────────────────

    private function tryPriceDrop(string $userId, array $signals, string $lang): ?array
    {
        if (empty($signals['recentlyViewedIds'])) return null;

        $droppedProperties = Property::whereIn('id', $signals['recentlyViewedIds'])
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotNull('original_price')
            ->whereRaw('price < original_price * ?', [1 - self::DROP_THRESHOLD])
            ->orderByRaw('(original_price - price) / original_price DESC')
            ->with('owner')
            ->limit(10)
            ->get();

        if ($droppedProperties->isEmpty()) return null;

        $count      = $droppedProperties->count();
        $topDrop    = $droppedProperties->first();
        $dropPct    = $topDrop->original_price > 0
            ? round((($topDrop->original_price - $topDrop->price) / $topDrop->original_price) * 100)
            : 0;

        // Build filters for SearchPage pre-fill
        $filters = $this->buildFiltersFromProperties($droppedProperties);

        return [
            'type'       => 'price_drop',
            'intent'     => 'active_buyer',
            'confidence' => min(0.60 + ($count * 0.08), 0.95),
            'icon'       => 'trending_down',
            'headline'   => 'price_drop_headline',  // Flutter translates via DText
            'subline'    => 'price_drop_subline',
            'params'     => [
                'count'    => $count,
                'drop_pct' => $dropPct,
            ],
            'filters'    => $filters,
            'count'      => $count,
            'properties' => $this->transformProperties($droppedProperties->take(5), $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STRIP TYPE 2: RESUME SEARCH
    //  "Continue: Villa · Rent · Erbil — 12 new results"
    //  Triggered: user applied filters in last 24h
    // ──────────────────────────────────────────────────────────────────────────

    private function tryResumeSearch(string $userId, array $signals, string $lang): ?array
    {
        // ── Prefer a real filter signal over a bare search signal ────────────
        // A bare search (user typed "Erbil") is too weak — don't resume it.
        // Only resume if: (a) user applied structured filters, OR
        //                 (b) search signal had active_filters set, OR
        //                 (c) user searched the same query 2+ times (repeated intent)
        $filter = $signals['filterSignal'];

        if (!$filter) {
            $searchSignal = $signals['searchSignal'];
            if (!$searchSignal) return null;

            // Bare search with no active filters — only resume if repeated
            $activeFilters = $searchSignal['active_filters'] ?? [];
            $hasActiveFilters = !empty($activeFilters)
                && count($activeFilters) > 0;

            if (!$hasActiveFilters) {
                // Check if user searched the same query more than once
                $query = $searchSignal['query'] ?? '';
                if (empty($query)) return null;

                $repeatCount = UserPropertyInteraction::where('user_id', $userId)
                    ->where('interaction_type', 'search_query_latest')
                    ->where('created_at', '>=', now()->subHours(48))
                    ->whereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.query')) = ?",
                        [strtolower($query)]
                    )
                    ->count();

                // Only fire if searched 2+ times
                if ($repeatCount < 2) return null;
            }

            $filter = $searchSignal;
        }

        // ── Normalise signal shape ────────────────────────────────────────────
        $filtersApplied = $filter['filters'] ?? $filter;
        $rawQuery       = $filter['query']   ?? ($filtersApplied['query'] ?? '');

        $isSearchSignal = !empty($rawQuery)
            && empty($filtersApplied['listing_type'])
            && empty($filtersApplied['property_type'])
            && empty($filtersApplied['city']);

        if ($isSearchSignal) {
            $filtersApplied['city'] = ucfirst(strtolower($rawQuery));
        }

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
            $city = strtolower($filtersApplied['city']);
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                [$city]
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
            && $filtersApplied['bedrooms'] !== 0
        ) {
            $query->where('bedrooms', '>=', (int) $filtersApplied['bedrooms']);
        }

        $filterTimestamp = $signals['filterSignal']
            ? now()->subHours(self::SESSION_WINDOW)
            : now()->subDays(2);

        $newSinceLastVisit = (clone $query)
            ->where('created_at', '>=', $filterTimestamp)
            ->count();

        $totalCount = $query->count();
        if ($totalCount === 0) return null;

        $topProperties = $query->orderByDesc('created_at')->with('owner')->limit(5)->get();

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
    //  STRIP TYPE 3: BUDGET MATCH
    //  "Properties within your budget — $150K–$200K"
    //  Triggered: user used calculator, properties exist in that price range
    // ──────────────────────────────────────────────────────────────────────────

    private function tryBudgetMatch(string $userId, array $signals, string $lang): ?array
    {
        $calc = $signals['calcSignal'];
        if (!$calc || empty($calc['budget_min_usd']) || empty($calc['budget_max_usd'])) {
            return null;
        }

        $min = (float) $calc['budget_min_usd'];
        $max = (float) $calc['budget_max_usd'];

        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('listing_type', 'sell')  // Calculator = buy intent
            ->where('price', '>=', $min)
            ->where('price', '<=', $max)
            ->orderByDesc('created_at');

        $count = $query->count();
        if ($count === 0) return null;

        $topProperties = $query->with('owner')->limit(5)->get();

        // Build pre-fill filters
        $filters = [
            'listing_type' => 'sell',
            'min_price'    => (int) $min,
            'max_price'    => (int) $max,
        ];

        // Include property_type if we can infer it from viewed/compared
        $inferredType = $this->inferPropertyType($signals);
        if ($inferredType) $filters['property_type'] = $inferredType;

        $signalStrength = (int) ($calc['signal_strength'] ?? 50);
        $confidence     = 0.60 + ($signalStrength / 100 * 0.30);

        return [
            'type'       => 'budget_match',
            'intent'     => 'active_buyer',
            'confidence' => min($confidence, 0.90),
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
    //  STRIP TYPE 4: AREA FOCUS
    //  "You keep looking in Erbil — 34 properties available"
    //  Triggered: >= 3 viewed properties are in the same city
    // ──────────────────────────────────────────────────────────────────────────

    private function tryAreaFocus(string $userId, array $signals, string $lang): ?array
    {
        if (count($signals['recentlyViewedIds']) < 3) return null;

        // Get the city distribution of recently viewed properties
        $viewedProperties = Property::whereIn('id', $signals['recentlyViewedIds'])
            ->whereNotNull('address_details')
            ->get(['id', 'address_details', 'listing_type', 'property_type']);

        if ($viewedProperties->isEmpty()) return null;

        // Count by city
        $cityCounts = [];
        foreach ($viewedProperties as $prop) {
            $addr = is_array($prop->address_details)
                ? $prop->address_details
                : json_decode($prop->address_details, true);
            $city = $addr['city']['en'] ?? null;
            if ($city) {
                $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
            }
        }

        if (empty($cityCounts)) return null;
        arsort($cityCounts);
        $topCity      = array_key_first($cityCounts);
        $topCityCount = $cityCounts[$topCity];

        // Need at least 3 views in same city to trigger
        if ($topCityCount < 3) return null;

        // Find available properties in that city
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
        if ($count === 0) return null;

        $topProperties = $query->with('owner')->limit(5)->get();

        // Infer listing type + property type from viewed properties
        $inferredListingType = $this->inferListingType($viewedProperties);
        $inferredPropType    = $this->inferPropertyType($signals);

        $filters = ['city' => $topCity];
        if ($inferredListingType) $filters['listing_type'] = $inferredListingType;
        if ($inferredPropType)    $filters['property_type'] = $inferredPropType;

        $confidence = 0.55 + min($topCityCount * 0.05, 0.30);

        return [
            'type'       => 'area_focus',
            'intent'     => 'location_focused',
            'confidence' => min($confidence, 0.85),
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
    //  STRIP TYPE 5: NEW MATCHES
    //  "New listings matching your preferences"
    //  Triggered: user has favorites/compares → new properties appeared since
    //  their last visit that match their inferred profile
    // ──────────────────────────────────────────────────────────────────────────

    private function tryNewMatches(string $userId, array $signals, string $lang): ?array
    {
        $allInteractedIds = array_unique(array_merge(
            $signals['favoriteIds'],
            $signals['compareIds'],
            $signals['recentlyViewedIds']
        ));

        if (count($allInteractedIds) < 2) return null;

        // Infer preferences from interacted properties
        $interactedProps = Property::whereIn('id', $allInteractedIds)
            ->get([
                'id',
                'listing_type',
                'property_type',
                'address_details',
                'price',
                'bedrooms'
            ]);

        if ($interactedProps->isEmpty()) return null;

        // Dominant listing type
        $listingTypeCounts = $interactedProps->groupBy('listing_type')
            ->map->count()->sortDesc();
        $dominantListingType = $listingTypeCounts->keys()->first();

        // Dominant property type
        $propTypeCounts = $interactedProps->groupBy('property_type')
            ->map->count()->sortDesc();
        $dominantPropType = $propTypeCounts->keys()->first();

        // Price range from interacted (median ± 30%)
        $prices   = $interactedProps->pluck('price')->filter()->sort()->values();
        $medianPx = $prices->count() > 0
            ? $prices->get((int) floor($prices->count() / 2))
            : null;

        // When was user last active?
        $lastActive = $signals['user']?->last_activity_at
            ?? now()->subDays(1);

        // Find new properties since last visit matching profile
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
        if ($count === 0) return null;

        $topProperties = $query
            ->orderByDesc('created_at')
            ->with('owner')
            ->limit(5)
            ->get();

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
    //  STRIP TYPE 6: RETURNING VISITOR
    //  "Welcome back — 47 new listings since your last visit"
    //  Triggered: user hasn't visited in >= 2 days, new listings exist
    // ──────────────────────────────────────────────────────────────────────────

    private function tryReturningVisitor(string $userId, array $signals, string $lang): ?array
    {
        if ($signals['daysSinceVisit'] < 2) return null;

        $lastActive = $signals['user']?->last_activity_at
            ?? now()->subDays($signals['daysSinceVisit']);

        $newCount = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('created_at', '>=', $lastActive)
            ->count();

        if ($newCount === 0) return null;

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
    //  HELPERS
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
        $counts = $properties->groupBy('listing_type')->map->count()->sortDesc();
        return $counts->keys()->first();
    }

    private function buildFiltersFromProperties(Collection $properties): array
    {
        if ($properties->isEmpty()) return [];
        $first = $properties->first();
        $addr  = is_array($first->address_details)
            ? $first->address_details
            : json_decode($first->address_details, true);

        $filters = [];
        if ($first->listing_type) $filters['listing_type'] = $first->listing_type;
        if ($first->property_type) $filters['property_type'] = $first->property_type;
        if (!empty($addr['city']['en'])) $filters['city'] = $addr['city']['en'];
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
            $cityName = $addr['city'][$cityKey]
                ?? $addr['city']['en']
                ?? '';

            // Resolve images
            $images = [];
            if (!empty($property->images)) {
                $raw = is_array($property->images)
                    ? $property->images
                    : json_decode($property->images, true);
                if (is_array($raw)) {
                    $images = collect($raw)
                        ->map(fn($img) => is_array($img) ? ($img['url'] ?? null) : $img)
                        ->filter()
                        ->values()
                        ->toArray();
                }
            }

            return [
                'id'           => $property->id,
                'name'         => $property->name ?? '',
                'price'        => (float) ($property->price ?? 0),
                'listing_type' => $property->listing_type ?? '',
                'property_type' => $property->property_type ?? '',
                'city'         => $cityName,
                'image'        => $images[0] ?? null,
                'is_verified'  => (bool) ($property->verified ?? false),
                'bedrooms'     => (int) ($property->bedrooms ?? 0),
            ];
        })->toArray();
    }
}