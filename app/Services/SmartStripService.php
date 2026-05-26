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
    private const DROP_THRESHOLD = 0.03;

    // ──────────────────────────────────────────────────────────────────────────
    //  PUBLIC ENTRY POINT
    // ──────────────────────────────────────────────────────────────────────────

    public function getStrip(string $userId, string $language = 'en'): ?array
    {
        $cacheKey = "smart_strip_{$userId}_{$language}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $language) {
            try {
                $signals = $this->loadSignals($userId);

                // ── Log: signals summary ──────────────────────────────────────
                Log::info('SmartStrip: signals loaded', [
                    'user_id'                  => $userId,
                    'has_filter_signal'        => !empty($signals['filterSignal']),
                    'has_search_signal'        => !empty($signals['searchSignal']),
                    'has_calc_signal'          => !empty($signals['calcSignal']),
                    'recently_viewed_count'    => count($signals['recentlyViewedIds']),
                    'favorites_count'          => count($signals['favoriteIds']),
                    'compare_count'            => count($signals['compareIds']),
                    'days_since_visit'         => $signals['daysSinceVisit'],
                    'filter_signal_city'       => $signals['filterSignal']['city']        ?? null,
                    'filter_signal_type'       => $signals['filterSignal']['listing_type'] ?? null,
                    'calc_budget_min'          => $signals['calcSignal']['budget_min_usd'] ?? null,
                    'calc_budget_max'          => $signals['calcSignal']['budget_max_usd'] ?? null,
                ]);

                // Try each strip type in priority order, logging skips & wins
                $stripTypes = [
                    'price_drop'        => fn() => $this->tryPriceDrop($userId, $signals, $language),
                    'resume_search'     => fn() => $this->tryResumeSearch($userId, $signals, $language),
                    'budget_match'      => fn() => $this->tryBudgetMatch($userId, $signals, $language),
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

                    // ── Log: query result for this candidate ──────────────────
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
                    break; // highest-priority winner, stop here
                }

                // ── Log: final selection ──────────────────────────────────────
                if (!$strip || ($strip['confidence'] ?? 0) < 0.50) {
                    Log::info('SmartStrip: no strip returned', [
                        'user_id'            => $userId,
                        'language'           => $language,
                        'reason'             => !$strip
                            ? 'all strip types returned null'
                            : 'confidence below threshold',
                        'confidence'         => $strip['confidence'] ?? null,
                        'confidence_threshold' => 0.50,
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

    // Invalidate cache when user performs a new action
    public function invalidate(string $userId): void
    {
        foreach (['en', 'ar', 'ku'] as $lang) {
            Cache::forget("smart_strip_{$userId}_{$lang}");
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  SIGNAL LOADER
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
    //  STRIP TYPE 1: PRICE DROP
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

        $count   = $droppedProperties->count();
        $topDrop = $droppedProperties->first();
        $dropPct = $topDrop->original_price > 0
            ? round((($topDrop->original_price - $topDrop->price) / $topDrop->original_price) * 100)
            : 0;

        $filters = $this->buildFiltersFromProperties($droppedProperties);

        return [
            'type'       => 'price_drop',
            'intent'     => 'active_buyer',
            'confidence' => min(0.60 + ($count * 0.08), 0.95),
            'icon'       => 'trending_down',
            'headline'   => 'price_drop_headline',
            'subline'    => 'price_drop_subline',
            'params'     => ['count' => $count, 'drop_pct' => $dropPct],
            'filters'    => $filters,
            'count'      => $count,
            'properties' => $this->transformProperties($droppedProperties->take(5), $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STRIP TYPE 2: RESUME SEARCH
    // ──────────────────────────────────────────────────────────────────────────

    private function tryResumeSearch(string $userId, array $signals, string $lang): ?array
    {
        $filter = $signals['filterSignal'] ?? $signals['searchSignal'];
        if (!$filter) return null;

        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented']);

        $filtersApplied = $filter['filters'] ?? $filter;

        if (!empty($filtersApplied['listing_type'])) {
            $query->where('listing_type', $filtersApplied['listing_type']);
        }
        if (!empty($filtersApplied['property_type'])) {
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
        if (!empty($filtersApplied['bedrooms'])) {
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

        $labelParts = array_filter([
            $filtersApplied['property_type'] ?? null,
            isset($filtersApplied['listing_type']) ? ucfirst($filtersApplied['listing_type']) : null,
            $filtersApplied['city'] ?? null,
        ]);

        return [
            'type'       => 'resume_search',
            'intent'     => 'active_searcher',
            'confidence' => 0.85,
            'icon'       => 'search',
            'headline'   => 'resume_search_headline',
            'subline'    => 'resume_search_subline',
            'params'     => [
                'label_parts'     => array_values($labelParts),
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
            ->where('listing_type', 'sell')
            ->where('price', '>=', $min)
            ->where('price', '<=', $max)
            ->orderByDesc('created_at');

        $count = $query->count();
        if ($count === 0) return null;

        $topProperties = $query->with('owner')->limit(5)->get();

        $filters = ['listing_type' => 'sell', 'min_price' => (int) $min, 'max_price' => (int) $max];
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
            'params'     => ['min_price' => (int) $min, 'max_price' => (int) $max, 'count' => $count],
            'filters'    => $filters,
            'count'      => $count,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STRIP TYPE 4: AREA FOCUS
    // ──────────────────────────────────────────────────────────────────────────

    private function tryAreaFocus(string $userId, array $signals, string $lang): ?array
    {
        if (count($signals['recentlyViewedIds']) < 3) return null;

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
            if ($city) {
                $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
            }
        }

        if (empty($cityCounts)) return null;
        arsort($cityCounts);
        $topCity      = array_key_first($cityCounts);
        $topCityCount = $cityCounts[$topCity];

        if ($topCityCount < 3) return null;

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
            'params'     => ['city' => $topCity, 'view_count' => $topCityCount, 'count' => $count],
            'filters'    => $filters,
            'count'      => $count,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STRIP TYPE 5: NEW MATCHES
    // ──────────────────────────────────────────────────────────────────────────

    private function tryNewMatches(string $userId, array $signals, string $lang): ?array
    {
        $allInteractedIds = array_unique(array_merge(
            $signals['favoriteIds'],
            $signals['compareIds'],
            $signals['recentlyViewedIds']
        ));

        if (count($allInteractedIds) < 2) return null;

        $interactedProps = Property::whereIn('id', $allInteractedIds)
            ->get(['id', 'listing_type', 'property_type', 'address_details', 'price', 'bedrooms']);

        if ($interactedProps->isEmpty()) return null;

        $dominantListingType = $interactedProps->groupBy('listing_type')
            ->map->count()->sortDesc()->keys()->first();

        $dominantPropType = $interactedProps->groupBy('property_type')
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
        if ($count === 0) return null;

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
    //  STRIP TYPE 6: RETURNING VISITOR
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
            'params'     => ['days_away' => $signals['daysSinceVisit'], 'new_count' => $newCount],
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
        return $properties->groupBy('listing_type')->map->count()->sortDesc()->keys()->first();
    }

    private function buildFiltersFromProperties(Collection $properties): array
    {
        if ($properties->isEmpty()) return [];
        $first = $properties->first();
        $addr  = is_array($first->address_details)
            ? $first->address_details
            : json_decode($first->address_details, true);

        $filters = [];
        if ($first->listing_type)         $filters['listing_type']  = $first->listing_type;
        if ($first->property_type)        $filters['property_type'] = $first->property_type;
        if (!empty($addr['city']['en']))   $filters['city']          = $addr['city']['en'];
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
                        ->filter()
                        ->values()
                        ->toArray();
                }
            }

            return [
                'id'            => $property->id,
                'name'          => $property->name ?? '',
                'price'         => (float) ($property->price ?? 0),
                'listing_type'  => $property->listing_type ?? '',
                'property_type' => $property->property_type ?? '',
                'city'          => $cityName,
                'image'         => $images[0] ?? null,
                'is_verified'   => (bool) ($property->verified ?? false),
                'bedrooms'      => (int) ($property->bedrooms ?? 0),
            ];
        })->toArray();
    }
}