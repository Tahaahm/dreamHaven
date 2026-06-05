<?php

namespace App\Services;

use App\Models\Property;
use App\Models\UserPropertyInteraction;
use App\Models\User;
use App\Services\Intelligence\FeedBrain;
use App\Services\Intelligence\UserTasteProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SmartStripService (v2)
 * ----------------------
 * The single banner the user sees on the home screen.
 *
 * WHAT'S NEW IN v2:
 *  • Top 5 properties in every strip are RANKED BY FeedBrain (best match
 *    first), not just newest. This is the "wow this gets me" upgrade —
 *    inside the qualifying pool, the user sees properties that match their
 *    preferred type, city, bedrooms, price band first.
 *  • UserTasteProfile pulls signal weight with recency decay (14-day half-life).
 *  • All JSON-column bugs from v1 fixed (see notes below).
 *
 * BUGS FIXED FROM v1 (all silently failing via the outer try/catch):
 *  • `is_published` column does not exist on properties → use `published`.
 *  • `where('property_type', …)` — property type lives in JSON: type->category.
 *  • `where('bedrooms', …)`     — lives in JSON: rooms->bedroom->count.
 *  • `where('price', '>=', …)`  — price is JSON: price->usd.
 *  • Currency detection guessed IQD vs USD via AVG(price) on a JSON column —
 *    meaningless. Now reads price->usd directly. No guessing.
 *  • transformProperties read $property->currency / ->bedrooms / ->property_type
 *    which don't exist as columns. Now reads JSON correctly.
 *
 * PRESERVED FROM v1 (untouched logic):
 *  • All 5 strip types & their priority order.
 *  • All logging statements (searchable in your dashboard).
 *  • Cache TTL, session window, confidence thresholds.
 *  • Junk-filter detection (all-defaults filters carry zero intent).
 *  • Bare-search repeat detection (only resume if same query 2+ times in 48h).
 *  • Area-focus dominant city rule (3+ views in one city).
 *  • Returning visitor 2-day threshold.
 */
class SmartStripService
{
    private const CACHE_TTL      = 600;
    private const SESSION_WINDOW = 24;

    /**
     * Brain + profile auto-resolved by Laravel container.
     * No service-provider binding required — both are concrete classes.
     */
    public function __construct(
        private UserTasteProfile $profiles,
        private FeedBrain $brain,
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    //  PUBLIC ENTRY POINT
    // ──────────────────────────────────────────────────────────────────────────

    public function getStrip(string $userId, string $language = 'en'): ?array
    {
        // v2 cache key — old broken caches from v1 die naturally.
        $cacheKey = "smart_strip_v2_{$userId}_{$language}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $language) {
            try {
                $signals = $this->loadSignals($userId);
                $profile = $this->profiles->build($userId);

                Log::info('SmartStrip v2: signals loaded', [
                    'user_id'               => $userId,
                    'has_filter_signal'     => !empty($signals['filterSignal']),
                    'has_search_signal'     => !empty($signals['searchSignal']),
                    'has_calc_signal'       => !empty($signals['calcSignal']),
                    'recently_viewed_count' => count($signals['recentlyViewedIds']),
                    'favorites_count'       => count($signals['favoriteIds']),
                    'compare_count'         => count($signals['compareIds']),
                    'days_since_visit'      => $signals['daysSinceVisit'],
                    'profile_intent_score'  => $profile['intent_score'] ?? 0,
                    'profile_top_city'      => array_key_first($profile['cities'] ?? []),
                    'profile_top_type'      => array_key_first($profile['types'] ?? []),
                    'profile_listing_type'  => $profile['listing_type'] ?? null,
                    'profile_bedrooms'      => $profile['bedrooms']     ?? null,
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
                    'budget_match'      => fn() => $this->tryBudgetMatch($userId, $signals, $profile, $language),
                    'resume_search'     => fn() => $this->tryResumeSearch($userId, $signals, $profile, $language),
                    'area_focus'        => fn() => $this->tryAreaFocus($userId, $signals, $profile, $language),
                    'new_matches'       => fn() => $this->tryNewMatches($userId, $signals, $profile, $language),
                    'returning_visitor' => fn() => $this->tryReturningVisitor($userId, $signals, $profile, $language),
                ];

                $strip = null;
                foreach ($stripTypes as $typeName => $resolver) {
                    $candidate = $resolver();

                    if ($candidate === null) {
                        Log::info('SmartStrip v2: strip type skipped', [
                            'user_id' => $userId,
                            'type'    => $typeName,
                            'reason'  => 'returned null (no qualifying data)',
                        ]);
                        continue;
                    }

                    Log::info('SmartStrip v2: strip type resolved', [
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
                    Log::info('SmartStrip v2: no strip returned', [
                        'user_id'    => $userId,
                        'reason'     => !$strip
                            ? 'all strip types returned null'
                            : 'confidence below threshold',
                        'confidence' => $strip['confidence'] ?? null,
                    ]);
                    return null;
                }

                Log::info('SmartStrip v2: strip selected', [
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
                Log::warning('SmartStrip v2: failed (non-fatal)', [
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
            Cache::forget("smart_strip_v2_{$userId}_{$lang}");
            // Also drop any lingering v1 keys
            Cache::forget("smart_strip_{$userId}_{$lang}");
        }
        $this->profiles->invalidate($userId);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  SIGNAL LOADER  (unchanged from v1 — already correct)
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
    //  STRIP 1: BUDGET MATCH
    //  "Within your budget — $52K–$78K"
    //  Triggered: user used calculator. Strongest buy-intent signal.
    //
    //  v2 changes:
    //   • Queries price->usd directly (no IQD guessing).
    //   • Defaults listing_type to user's preference (sell or rent), not just sell.
    //   • Top 5 brain-ranked: in the qualifying budget pool, shows the best
    //     match for their preferred city + type + bedrooms first.
    // ──────────────────────────────────────────────────────────────────────────

    private function tryBudgetMatch(string $userId, array $signals, array $profile, string $lang): ?array
    {
        $calc = $signals['calcSignal'];
        if (!$calc) {
            Log::info('SmartStrip[budget_match] v2: skip — no calculator signal', ['user_id' => $userId]);
            return null;
        }
        if (empty($calc['budget_min_usd']) || empty($calc['budget_max_usd'])) {
            Log::info('SmartStrip[budget_match] v2: skip — calc signal missing budget range', [
                'user_id'   => $userId,
                'calc_keys' => array_keys($calc),
            ]);
            return null;
        }

        $minUsd = (float) $calc['budget_min_usd'];
        $maxUsd = (float) $calc['budget_max_usd'];

        // Use profile listing preference if known (could be rent), else default to sell.
        // Calculator is overwhelmingly used for buying, so 'sell' is the safe default.
        $listingType = $profile['listing_type'] ?: 'sell';

        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true) // FIXED: was is_published (column doesn't exist)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('listing_type', $listingType)
            ->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) BETWEEN ? AND ?",
                [$minUsd, $maxUsd]
            );

        // ── Optional sharpening: if the user has a strong type preference
        //    (weight ≥ 0.7) and we still have ≥3 matches with that type, narrow.
        //    This is the "wow this gets me" effect — a user who keeps favoriting
        //    villas in their budget gets villas, not random apartments.
        $topType       = array_key_first($profile['types'] ?? []);
        $topTypeWeight = $profile['types'][$topType] ?? 0;
        if ($topType && $topTypeWeight >= 0.7) {
            $sharpQuery = (clone $query)->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                [strtolower($topType)]
            );
            if ($sharpQuery->count() >= 3) {
                $query = $sharpQuery;
                Log::info('SmartStrip[budget_match] v2: sharpened by type', [
                    'user_id' => $userId,
                    'type'    => $topType,
                ]);
            }
        }

        $count = $query->count();
        if ($count === 0) {
            Log::info('SmartStrip[budget_match] v2: skip — 0 properties in budget', [
                'user_id'      => $userId,
                'min_usd'      => $minUsd,
                'max_usd'      => $maxUsd,
                'listing_type' => $listingType,
            ]);
            return null;
        }

        // Pull a wide pool, then let the brain pick the best 5 for THIS user.
        $pool = $query->with('owner')->limit(40)->get();
        $topProperties = $this->brain->rank($pool, $profile, 5, (int) now()->format('Ymd'));

        $filters = [
            'listing_type' => $listingType,
            'min_price'    => (int) $minUsd, // always return USD to Flutter
            'max_price'    => (int) $maxUsd,
        ];
        $inferredType = $topType ?: $this->inferPropertyType($signals);
        if ($inferredType)        $filters['property_type'] = $inferredType;
        if ($profile['bedrooms']) $filters['bedrooms']      = $profile['bedrooms'];

        $signalStrength = (int) ($calc['signal_strength'] ?? 50);
        $confidence     = min(0.65 + ($signalStrength / 100 * 0.25), 0.92);

        return [
            'type'       => 'budget_match',
            'intent'     => 'active_buyer',
            'confidence' => $confidence,
            'icon'       => 'wallet',
            'headline'   => 'budget_match_headline',
            'subline'    => 'budget_match_subline',
            'params'     => [
                'min_price' => (int) $minUsd,
                'max_price' => (int) $maxUsd,
                'count'     => $count,
            ],
            'filters'    => $filters,
            'count'      => $count,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STRIP 2: RESUME SEARCH
    //  "Continue: Villa · For Sale · Erbil — 12 new"
    //
    //  Only fires for REAL structured filters, NOT bare text searches.
    //  Only fires when:
    //    (a) User applied structured filters (listing_type / property_type / price)
    //    (b) Search had active_filters set (not empty)
    //    (c) User searched the same query 2+ times in 48h (repeated intent)
    //
    //  v2 changes:
    //   • property_type / price / bedrooms now query JSON correctly.
    //   • Top 5 brain-ranked.
    // ──────────────────────────────────────────────────────────────────────────

    private function tryResumeSearch(string $userId, array $signals, array $profile, string $lang): ?array
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
                Log::info('SmartStrip[resume_search] v2: skip — filter signal is all-defaults', [
                    'user_id' => $userId,
                    'filter'  => $filter,
                ]);
                $filter = null; // fall through to check search signal instead
            }
        }

        if (!$filter) {
            $searchSignal = $signals['searchSignal'];
            if (!$searchSignal) {
                Log::info('SmartStrip[resume_search] v2: skip — no filter or search signal', ['user_id' => $userId]);
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
                    Log::info('SmartStrip[resume_search] v2: skip — bare search query only once', [
                        'user_id'      => $userId,
                        'query'        => $rawQuery,
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

        // ── Build property query (FIXED to use JSON columns) ──────────────────
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

        // FIXED: property_type lives in JSON type->category, not as a flat column
        if (
            !empty($filtersApplied['property_type'])
            && strtolower($filtersApplied['property_type']) !== 'all'
        ) {
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                [strtolower($filtersApplied['property_type'])]
            );
        }

        if (!empty($filtersApplied['city'])) {
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                [strtolower($filtersApplied['city'])]
            );
        }

        // FIXED: price is JSON price->usd, not a flat column
        if (!empty($filtersApplied['min_price'])) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) >= ?",
                [(float) $filtersApplied['min_price']]
            );
        }
        if (!empty($filtersApplied['max_price'])) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) <= ?",
                [(float) $filtersApplied['max_price']]
            );
        }

        // FIXED: bedrooms lives in JSON rooms->bedroom->count
        if (
            !empty($filtersApplied['bedrooms'])
            && $filtersApplied['bedrooms'] !== '0'
            && (int) $filtersApplied['bedrooms'] > 0
        ) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) AS UNSIGNED) >= ?",
                [(int) $filtersApplied['bedrooms']]
            );
        }

        $filterTimestamp   = $signals['filterSignal']
            ? now()->subHours(self::SESSION_WINDOW)
            : now()->subDays(2);
        $newSinceLastVisit = (clone $query)
            ->where('created_at', '>=', $filterTimestamp)
            ->count();

        $totalCount = $query->count();
        if ($totalCount === 0) {
            Log::info('SmartStrip[resume_search] v2: skip — 0 properties match filters', [
                'user_id'         => $userId,
                'filters_applied' => $filtersApplied,
            ]);
            return null;
        }

        // Top 5 brain-ranked from the filtered pool
        $pool = $query->with('owner')->limit(40)->get();
        $topProperties = $this->brain->rank($pool, $profile, 5, (int) now()->format('Ymd'));

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
    //  STRIP 3: AREA FOCUS
    //  "You keep looking in Erbil — 34 available"
    //
    //  v2 changes:
    //   • Top 5 brain-ranked (the best Erbil properties for this user, not random).
    // ──────────────────────────────────────────────────────────────────────────

    private function tryAreaFocus(string $userId, array $signals, array $profile, string $lang): ?array
    {
        if (count($signals['recentlyViewedIds']) < 3) {
            Log::info('SmartStrip[area_focus] v2: skip — fewer than 3 recently viewed', [
                'user_id'      => $userId,
                'viewed_count' => count($signals['recentlyViewedIds']),
            ]);
            return null;
        }

        $viewedProperties = Property::whereIn('id', $signals['recentlyViewedIds'])
            ->whereNotNull('address_details')
            ->get(['id', 'address_details', 'listing_type', 'type']);

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
            Log::info('SmartStrip[area_focus] v2: skip — no dominant city', [
                'user_id'        => $userId,
                'top_city'       => $topCity,
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
            ->whereNotIn('id', $signals['recentlyViewedIds']);

        $count = $query->count();
        if ($count === 0) {
            Log::info('SmartStrip[area_focus] v2: skip — 0 unseen properties in dominant city', [
                'user_id' => $userId,
                'city'    => $topCity,
            ]);
            return null;
        }

        // Brain-ranked top 5 inside the city pool
        $pool = $query->with('owner')->limit(40)->get();
        $topProperties = $this->brain->rank($pool, $profile, 5, (int) now()->format('Ymd'));

        $inferredListingType = $this->inferListingType($viewedProperties);
        $inferredPropType    = $this->inferPropertyType($signals);

        $filters = ['city' => $topCity];
        if ($inferredListingType) $filters['listing_type']  = $inferredListingType;
        if ($inferredPropType)    $filters['property_type'] = $inferredPropType;
        if ($profile['bedrooms']) $filters['bedrooms']      = $profile['bedrooms'];

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
    //  STRIP 4: NEW MATCHES
    //  "New listings matching your style"
    //
    //  v2 changes (this one was the most broken in v1):
    //   • property_type extracted from JSON type->category, not the missing column.
    //   • bedrooms read from JSON rooms->bedroom->count.
    //   • Median price computed from price->usd as a number, not an array cast.
    //   • Query filters use JSON paths.
    //   • Top 5 brain-ranked.
    // ──────────────────────────────────────────────────────────────────────────

    private function tryNewMatches(string $userId, array $signals, array $profile, string $lang): ?array
    {
        $allInteractedIds = array_unique(array_merge(
            $signals['favoriteIds'],
            $signals['compareIds'],
            $signals['recentlyViewedIds']
        ));

        if (count($allInteractedIds) < 2) {
            Log::info('SmartStrip[new_matches] v2: skip — fewer than 2 interacted', [
                'user_id'          => $userId,
                'interacted_count' => count($allInteractedIds),
            ]);
            return null;
        }

        $interactedProps = Property::whereIn('id', $allInteractedIds)
            ->get(['id', 'listing_type', 'type', 'address_details', 'price', 'rooms']);

        if ($interactedProps->isEmpty()) return null;

        // Dominant listing_type (sell vs rent)
        $dominantListingType = $interactedProps->groupBy('listing_type')
            ->map->count()->sortDesc()->keys()->first();

        // FIXED: property type from JSON, not a missing column
        $dominantPropType = $interactedProps
            ->map(fn($p) => is_array($p->type) ? ($p->type['category'] ?? null) : null)
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        // FIXED: median price from JSON USD as numbers, not array cast
        $prices = $interactedProps
            ->map(fn($p) => is_array($p->price) ? (float) ($p->price['usd'] ?? 0) : 0)
            ->filter(fn($p) => $p > 0)
            ->sort()
            ->values();
        $medianPx = $prices->count() > 0
            ? (float) $prices->get((int) floor($prices->count() / 2))
            : null;

        $lastActive = $signals['user']?->last_activity_at ?? now()->subDays(1);

        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('created_at', '>=', $lastActive)
            ->whereNotIn('id', $allInteractedIds);

        if ($dominantListingType) {
            $query->where('listing_type', $dominantListingType);
        }

        // FIXED: filter property_type via JSON
        if ($dominantPropType) {
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                [strtolower($dominantPropType)]
            );
        }

        // FIXED: filter price via JSON USD
        if ($medianPx) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) BETWEEN ? AND ?",
                [$medianPx * 0.65, $medianPx * 1.40]
            );
        }

        $count = $query->count();
        if ($count === 0) {
            Log::info('SmartStrip[new_matches] v2: skip — 0 new listings match profile', [
                'user_id'      => $userId,
                'listing_type' => $dominantListingType,
                'prop_type'    => $dominantPropType,
                'median_price' => $medianPx,
            ]);
            return null;
        }

        // Brain-ranked top 5
        $pool = $query->with('owner')->limit(40)->get();
        $topProperties = $this->brain->rank($pool, $profile, 5, (int) now()->format('Ymd'));

        $filters = [];
        if ($dominantListingType) $filters['listing_type']  = $dominantListingType;
        if ($dominantPropType)    $filters['property_type'] = $dominantPropType;
        if ($medianPx) {
            $filters['min_price'] = (int) ($medianPx * 0.65);
            $filters['max_price'] = (int) ($medianPx * 1.40);
        }
        if ($profile['bedrooms']) $filters['bedrooms'] = $profile['bedrooms'];

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
    //  STRIP 5: RETURNING VISITOR
    //  "Welcome back — 47 new listings"
    //
    //  v2 changes:
    //   • Top 5 brain-ranked (best new listings for this user, not random newest).
    // ──────────────────────────────────────────────────────────────────────────

    private function tryReturningVisitor(string $userId, array $signals, array $profile, string $lang): ?array
    {
        if ($signals['daysSinceVisit'] < 2) {
            Log::info('SmartStrip[returning_visitor] v2: skip — visited less than 2 days ago', [
                'user_id'          => $userId,
                'days_since_visit' => $signals['daysSinceVisit'],
            ]);
            return null;
        }

        $lastActive = $signals['user']?->last_activity_at
            ?? now()->subDays($signals['daysSinceVisit']);

        $newQuery = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('created_at', '>=', $lastActive);

        $newCount = (clone $newQuery)->count();
        if ($newCount === 0) {
            Log::info('SmartStrip[returning_visitor] v2: skip — no new listings since last visit', [
                'user_id'     => $userId,
                'last_active' => $lastActive,
            ]);
            return null;
        }

        // Brain-ranked top 5 — if user has any history, this matters; if not,
        // brain falls back to quality + freshness scoring (still sensible).
        $pool = $newQuery->orderByDesc('created_at')->with('owner')->limit(40)->get();
        $topProperties = $this->brain->rank($pool, $profile, 5, (int) now()->format('Ymd'));

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

        $result = Property::whereIn('id', $ids)
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(type, '$.category')) as prop_type, COUNT(*) as cnt")
            ->groupBy('prop_type')
            ->orderByDesc('cnt')
            ->first();

        return $result?->prop_type;
    }

    private function inferListingType(Collection $properties): ?string
    {
        if ($properties->isEmpty()) return null;
        return $properties->groupBy('listing_type')
            ->map->count()->sortDesc()->keys()->first();
    }

    /**
     * Build a flat filter map from a property collection (helper for any
     * future call sites that need it). FIXED: pulls type->category from JSON
     * instead of the missing property_type column.
     */
    private function buildFiltersFromProperties(Collection $properties): array
    {
        if ($properties->isEmpty()) return [];
        $first = $properties->first();
        $addr  = is_array($first->address_details)
            ? $first->address_details
            : json_decode($first->address_details, true);

        $filters = [];
        if ($first->listing_type) $filters['listing_type']  = $first->listing_type;
        if (is_array($first->type) && !empty($first->type['category'])) {
            $filters['property_type'] = $first->type['category'];
        }
        if (!empty($addr['city']['en'])) $filters['city'] = $addr['city']['en'];
        return $filters;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  TRANSFORM PROPERTIES FOR FLUTTER
    //
    //  v2 fixes (these were all reading non-existent columns):
    //   • property_type from JSON type->category
    //   • bedrooms / bathrooms from JSON rooms->{bedroom,bathroom}->count
    //   • price from JSON price->usd (with safe IQD fallback)
    //   • currency hardcoded 'USD' (no currency column on properties)
    //
    //  v2 additions for the "wow this gets me" effect:
    //   • bathrooms (users notice when this matters)
    //   • area (sqm)
    //   • match_score (0..100 from brain — lets Flutter show how good a match)
    //   • reasons[]   ([{key, headline, tone}] — sharp + warm labels)
    // ──────────────────────────────────────────────────────────────────────────

    private function transformProperties(Collection $properties, string $lang): array
    {
        return $properties->map(function ($property) use ($lang) {
            $addr = is_array($property->address_details)
                ? $property->address_details
                : json_decode($property->address_details ?? '{}', true);
            if (!is_array($addr)) $addr = [];

            $cityKey  = match ($lang) {
                'ar'    => 'ar',
                'ku'    => 'ku',
                default => 'en',
            };
            $cityName = $addr['city'][$cityKey] ?? $addr['city']['en'] ?? '';

            // ── Images ────────────────────────────────────────────────────────
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

            // ── Price (read price->usd directly, no guessing) ────────────────
            $priceUsd = 0.0;
            $priceIqd = 0.0;
            $rawPrice = $property->price;

            if (!empty($rawPrice)) {
                $priceData = is_string($rawPrice)
                    ? json_decode($rawPrice, true)
                    : (array) $rawPrice;

                if (isset($priceData['usd']) && $priceData['usd'] > 0) {
                    $priceUsd = (float) $priceData['usd'];
                } elseif (isset($priceData['iqd']) && $priceData['iqd'] > 0) {
                    // Listing in IQD only — convert at ~1300 IQD/USD
                    $rawIqd = (float) $priceData['iqd'];
                    $priceUsd = $rawIqd > 1_000_000 ? round($rawIqd / 1300) : $rawIqd;
                } elseif (is_numeric($rawPrice) && $rawPrice > 0) {
                    // Legacy flat-number rows
                    $rawNum = (float) $rawPrice;
                    $priceUsd = $rawNum > 1_000_000 ? round($rawNum / 1300) : $rawNum;
                }

                if (isset($priceData['iqd'])) {
                    $priceIqd = (float) $priceData['iqd'];
                }
            }

            // ── Property type from JSON ───────────────────────────────────────
            $propertyType = '';
            if (is_array($property->type)) {
                $propertyType = $property->type['category'] ?? '';
            }

            // ── Bedrooms / bathrooms from JSON ───────────────────────────────
            $bedrooms  = 0;
            $bathrooms = 0;
            if (is_array($property->rooms)) {
                $bedrooms  = (int) ($property->rooms['bedroom']['count']  ?? 0);
                $bathrooms = (int) ($property->rooms['bathroom']['count'] ?? 0);
            }

            return [
                'id'            => $property->id,
                'name'          => $property->name ?? '',
                'price'         => $priceUsd,
                'price_iqd'     => $priceIqd,
                'currency'      => 'USD',                       // FIXED
                'listing_type'  => $property->listing_type ?? '',
                'property_type' => $propertyType,               // FIXED
                'city'          => $cityName,
                'image'         => $images[0] ?? null,
                'is_verified'   => (bool) ($property->verified   ?? false),
                'is_boosted'    => (bool) ($property->is_boosted ?? false),
                'bedrooms'      => $bedrooms,                   // FIXED
                'bathrooms'     => $bathrooms,                  // NEW
                'area'          => $property->area,             // NEW (sqm)
                // ── The "why we show this" — brain reasons (sharp headline + warm tone)
                'match_score'   => isset($property->feed_score) ? round($property->feed_score, 1) : null,
                'reasons'       => $property->feed_reasons ?? [],
            ];
        })->toArray();
    }
}
