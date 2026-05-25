<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPropertyInteraction;
use App\Models\Property;
use App\Services\PropertyInteractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserBehaviorController extends Controller
{
    private PropertyInteractionService $interactionService;

    public function __construct(PropertyInteractionService $interactionService)
    {
        $this->interactionService = $interactionService;
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  1. CALCULATOR SIGNAL
    //  POST /api/v1/user/calculator-signal
    // ══════════════════════════════════════════════════════════════════════════
    public function storeCalculatorSignal(Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return response()->json(['success' => true]);

            $this->interactionService->storeCalculatorSignal(
                userId: $user->id,
                targetPriceUsd: (float) $request->input('target_price_usd', 0),
                savedSoFarUsd: (float) $request->input('saved_so_far_usd',  0),
                monthlyUsd: (float) $request->input('monthly_usd',        0),
                targetYears: (int)   $request->input('target_years',        0),
                mode: $request->input('mode', 'how_long'),
            );

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::warning('calculator-signal failed (non-fatal)', ['error' => $e->getMessage()]);
            return response()->json(['success' => true]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  2. SEARCH QUERY SIGNAL
    //  POST /api/v1/user/track-search
    //
    //  Stores what the user searched for, result count, and active filters.
    //  Two writes:
    //   (a) Fresh INSERT every search — for keyword trend analysis
    //   (b) updateOrCreate on the "latest" row — what the rec engine reads
    //
    //  Also triggers search impression tracking if property_ids are provided.
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearch(Request $request): JsonResponse
    {
        try {
            $user  = auth('sanctum')->user();
            $userId = $user
                ? $user->id
                : 'guest_' . session()->getId();

            $query        = trim($request->input('query', ''));
            $resultsCount = (int) $request->input('results_count', 0);
            $filters      = $request->input('active_filters', []);
            $language     = $request->input('language', 'en');
            $propertyIds  = $request->input('property_ids', []); // IDs of results returned

            if (empty($query) && empty($filters)) {
                return response()->json(['success' => true]);
            }

            if ($user) {
                // (a) Append-only log for trend analysis
                UserPropertyInteraction::create([
                    'user_id'          => $user->id,
                    'property_id'      => 'search_signal',
                    'interaction_type' => 'search_query',
                    'metadata'         => json_encode([
                        'query'          => $query,
                        'results_count'  => $resultsCount,
                        'active_filters' => $filters,
                        'language'       => $language,
                        'timestamp'      => now()->toISOString(),
                    ]),
                    'created_at' => now(),
                ]);

                // (b) Latest search upsert — what rec engine reads
                UserPropertyInteraction::updateOrCreate(
                    [
                        'user_id'          => $user->id,
                        'property_id'      => 'search_signal_latest',
                        'interaction_type' => 'search_query_latest',
                    ],
                    [
                        'metadata' => json_encode([
                            'query'          => $query,
                            'results_count'  => $resultsCount,
                            'active_filters' => $filters,
                            'language'       => $language,
                            'updated_at'     => now()->toISOString(),
                        ]),
                        'created_at' => now(),
                    ]
                );

                \Illuminate\Support\Facades\Cache::forget("personalized_recs_{$user->id}");
            }

            // Track search impressions (works for guests too)
            if (!empty($propertyIds)) {
                $this->interactionService->trackSearchImpressions(
                    userId: $userId,
                    propertyIds: $propertyIds,
                    searchQuery: $query,
                    activeFilters: $filters
                );
            }

            Log::info('🔍 Search signal stored', [
                'user_id'       => $user?->id ?? 'guest',
                'query'         => $query,
                'results_count' => $resultsCount,
                'impressions'   => count($propertyIds),
                'language'      => $language,
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::warning('track-search failed (non-fatal)', ['error' => $e->getMessage()]);
            return response()->json(['success' => true]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  3. SEARCH CLICK SIGNAL  ← NEW
    //  POST /api/v1/user/track-search-click
    //
    //  Called by Flutter when a user taps a property card that appeared
    //  in search results. This is the highest-quality signal for popularity
    //  because it proves the user actively chose this property from a list.
    //
    //  PAYLOAD:
    //  {
    //    "property_id":     "prop_2025_01_00123",
    //    "search_query":    "apartment erbil 3 bedroom",
    //    "result_position": 4,          ← 0-indexed position in results list
    //    "active_filters":  { ... },
    //    "search_session":  "uuid"      ← optional, to group clicks from same search
    //  }
    //
    //  DESIGN NOTES:
    //  - result_position matters: position 0 click = user confirmed top result
    //    was good. Position 8 click = user scrolled deep → stronger engagement.
    //  - We store this separately from regular 'view' so we can compute CTR.
    //  - After storing, we also fire a regular view track so the property's
    //    view counter increments naturally.
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearchClick(Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return response()->json(['success' => true]);

            $validator = Validator::make($request->all(), [
                'property_id'     => 'required|string',
                'search_query'    => 'nullable|string|max:500',
                'result_position' => 'nullable|integer|min:0',
                'active_filters'  => 'nullable|array',
                'search_session'  => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                // Non-fatal: don't break the app for analytics failures
                Log::warning('track-search-click validation failed', [
                    'errors' => $validator->errors()->toArray(),
                ]);
                return response()->json(['success' => true]);
            }

            $propertyId     = $request->input('property_id');
            $searchQuery    = trim($request->input('search_query', ''));
            $position       = (int) $request->input('result_position', 0);
            $activeFilters  = $request->input('active_filters', []);
            $searchSession  = $request->input('search_session', null);

            // Validate the property actually exists and is active
            $propertyExists = Property::where('id', $propertyId)
                ->where('is_active', true)
                ->where('published', true)
                ->exists();

            if (!$propertyExists) {
                return response()->json(['success' => true]);
            }

            // Store the search click interaction
            $this->interactionService->trackSearchClick(
                userId: $user->id,
                propertyId: $propertyId,
                searchQuery: $searchQuery,
                resultPosition: $position,
                activeFilters: array_merge($activeFilters, [
                    'search_session' => $searchSession,
                ])
            );

            // Also fire a standard view track so views counter stays accurate.
            // We pass source=search_click so it can be distinguished in metadata.
            $this->interactionService->trackView($user->id, $propertyId, [
                'source'          => 'search_click',
                'search_query'    => $searchQuery,
                'result_position' => $position,
                'user_agent'      => $request->header('User-Agent'),
            ]);

            Log::info('🖱️ Search click tracked', [
                'user_id'     => $user->id,
                'property_id' => $propertyId,
                'query'       => $searchQuery,
                'position'    => $position,
            ]);

            return response()->json([
                'success'  => true,
                'property_id' => $propertyId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('track-search-click failed (non-fatal)', ['error' => $e->getMessage()]);
            return response()->json(['success' => true]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  4. BULK SEARCH IMPRESSIONS  ← NEW
    //  POST /api/v1/user/track-search-impressions
    //
    //  Called when a search results page loads — sends ALL property IDs
    //  that appeared in the results, without the user having clicked any.
    //  This is the denominator in CTR = search_clicks / search_impressions.
    //
    //  PAYLOAD:
    //  {
    //    "property_ids":   ["prop_001", "prop_002", ...],
    //    "search_query":   "villa erbil",
    //    "active_filters": { ... }
    //  }
    //
    //  DESIGN NOTES:
    //  - Works for guests too (session-based dedup)
    //  - Deduped within 5-minute windows so pagination doesn't inflate counts
    //  - Flutter should call this ONCE per search, not once per page scroll
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearchImpressions(Request $request): JsonResponse
    {
        try {
            $user   = auth('sanctum')->user();
            $userId = $user
                ? $user->id
                : 'guest_' . session()->getId();

            $propertyIds  = $request->input('property_ids', []);
            $searchQuery  = trim($request->input('search_query', ''));
            $activeFilters = $request->input('active_filters', []);

            if (empty($propertyIds)) {
                return response()->json(['success' => true]);
            }

            // Cap at 100 properties per call to avoid abuse
            $propertyIds = array_slice($propertyIds, 0, 100);

            $this->interactionService->trackSearchImpressions(
                userId: $userId,
                propertyIds: $propertyIds,
                searchQuery: $searchQuery,
                activeFilters: $activeFilters
            );

            return response()->json([
                'success'    => true,
                'tracked'    => count($propertyIds),
            ]);
        } catch (\Throwable $e) {
            Log::warning('track-search-impressions failed (non-fatal)', ['error' => $e->getMessage()]);
            return response()->json(['success' => true]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  5. FILTER SIGNAL
    //  POST /api/v1/user/track-filter
    // ══════════════════════════════════════════════════════════════════════════
    public function trackFilter(Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return response()->json(['success' => true]);

            $filters      = $request->input('filters', []);
            $resultsCount = (int) $request->input('results_count', 0);

            if (empty($filters)) return response()->json(['success' => true]);

            $extractedSignals = [
                'listing_type'  => $filters['listing_type']  ?? null,
                'property_type' => $filters['property_type'] ?? null,
                'city'          => $filters['city']          ?? null,
                'min_price_usd' => isset($filters['min_price']) ? (float) $filters['min_price'] : null,
                'max_price_usd' => isset($filters['max_price']) ? (float) $filters['max_price'] : null,
                'bedrooms'      => isset($filters['bedrooms'])  ? (int) $filters['bedrooms']    : null,
                'bathrooms'     => isset($filters['bathrooms']) ? (int) $filters['bathrooms']   : null,
                'furnished'     => $filters['furnished']        ?? null,
                'has_pool'      => $filters['has_pool']         ?? null,
                'has_parking'   => $filters['has_parking']      ?? null,
                'has_gym'       => $filters['has_gym']          ?? null,
                'has_garden'    => $filters['has_garden']       ?? null,
                'has_balcony'   => $filters['has_balcony']      ?? null,
                'results_count' => $resultsCount,
                'updated_at'    => now()->toISOString(),
            ];

            $extractedSignals = array_filter(
                $extractedSignals,
                fn($v) => $v !== null && $v !== '' && $v !== false
            );

            UserPropertyInteraction::updateOrCreate(
                [
                    'user_id'          => $user->id,
                    'property_id'      => 'filter_signal',
                    'interaction_type' => 'filter_applied',
                ],
                [
                    'metadata'   => json_encode($extractedSignals),
                    'created_at' => now(),
                ]
            );

            \Illuminate\Support\Facades\Cache::forget("personalized_recs_{$user->id}");

            Log::info('🎛️ Filter signal stored', [
                'user_id' => $user->id,
                'signals' => $extractedSignals,
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::warning('track-filter failed (non-fatal)', ['error' => $e->getMessage()]);
            return response()->json(['success' => true]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  6. COMPARE SIGNAL
    //  POST /api/v1/user/track-compare
    // ══════════════════════════════════════════════════════════════════════════
    public function trackCompare(Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return response()->json(['success' => true]);

            $propertyIds = $request->input('property_ids', []);
            $properties  = $request->input('properties',  []);

            if (count($propertyIds) < 2) return response()->json(['success' => true]);

            $propMap = [];
            foreach ($properties as $p) {
                if (!empty($p['id'])) $propMap[$p['id']] = $p;
            }

            if (empty($propMap)) {
                $dbProps = Property::whereIn('id', $propertyIds)
                    ->select('id', 'type', 'price', 'address_details', 'listing_type')
                    ->get();
                foreach ($dbProps as $p) {
                    $propMap[$p->id] = [
                        'id'           => $p->id,
                        'type'         => $p->type['category']              ?? null,
                        'price_usd'    => $p->price['usd']                  ?? null,
                        'city'         => $p->address_details['city']['en'] ?? null,
                        'listing_type' => $p->listing_type,
                    ];
                }
            }

            $compareSession = (string) Str::uuid();
            $now            = now();
            $insertRows     = [];

            foreach ($propertyIds as $pid) {
                $meta = $propMap[$pid] ?? [];

                $insertRows[] = [
                    'user_id'          => $user->id,
                    'property_id'      => $pid,
                    'interaction_type' => 'compare',
                    'metadata'         => json_encode([
                        'compare_session' => $compareSession,
                        'compared_with'   => array_values(array_filter(
                            $propertyIds,
                            fn($id) => $id !== $pid
                        )),
                        'property_type'   => $meta['type']         ?? null,
                        'price_usd'       => $meta['price_usd']    ?? null,
                        'city'            => $meta['city']         ?? null,
                        'listing_type'    => $meta['listing_type'] ?? null,
                        'timestamp'       => $now->toISOString(),
                    ]),
                    'created_at' => $now,
                ];
            }

            UserPropertyInteraction::insert($insertRows);

            \Illuminate\Support\Facades\Cache::forget("personalized_recs_{$user->id}");

            // Also bust popularity cache since compare affects scores
            $this->interactionService->bustPopularityCache();

            Log::info('📊 Compare signal stored', [
                'user_id'         => $user->id,
                'property_ids'    => $propertyIds,
                'compare_session' => $compareSession,
                'count'           => count($insertRows),
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::warning('track-compare failed (non-fatal)', ['error' => $e->getMessage()]);
            return response()->json(['success' => true]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  7. GET POPULARITY BREAKDOWN  ← NEW (Debug / Admin endpoint)
    //  GET /api/v1/admin/property-popularity/{id}
    //
    //  Returns the full popularity score breakdown for a single property.
    //  Useful for admins and for debugging why a property ranks where it does.
    // ══════════════════════════════════════════════════════════════════════════
    public function getPropertyPopularityBreakdown(string $propertyId): JsonResponse
    {
        try {
            $property = Property::find($propertyId);
            if (!$property) {
                return response()->json(['error' => 'Property not found'], 404);
            }

            // Get search click count
            $searchClicks = UserPropertyInteraction::where('property_id', $propertyId)
                ->where('interaction_type', 'search_click')
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            // Get search impression count
            $searchImpressions = UserPropertyInteraction::where('property_id', $propertyId)
                ->where('interaction_type', 'search_impression')
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            // Get compare count
            $compareCount = UserPropertyInteraction::where('property_id', $propertyId)
                ->where('interaction_type', 'compare')
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            // Get velocity (last 48h)
            $velocity48h = UserPropertyInteraction::where('property_id', $propertyId)
                ->whereIn('interaction_type', ['view', 'search_click', 'favorite', 'compare'])
                ->where('created_at', '>=', now()->subHours(48))
                ->count();

            // Get click position distribution
            $clickPositions = UserPropertyInteraction::where('property_id', $propertyId)
                ->where('interaction_type', 'search_click')
                ->where('created_at', '>=', now()->subDays(30))
                ->get()
                ->map(function ($row) {
                    $meta = is_array($row->metadata)
                        ? $row->metadata
                        : json_decode($row->metadata, true);
                    return $meta['result_position'] ?? null;
                })
                ->filter()
                ->values();

            $ctr = $searchImpressions > 0
                ? round(($searchClicks / $searchImpressions) * 100, 2)
                : 0;

            // Top search queries that led to clicks on this property
            $topQueries = UserPropertyInteraction::where('property_id', $propertyId)
                ->where('interaction_type', 'search_click')
                ->where('created_at', '>=', now()->subDays(30))
                ->get()
                ->map(function ($row) {
                    $meta = is_array($row->metadata)
                        ? $row->metadata
                        : json_decode($row->metadata, true);
                    return $meta['query'] ?? null;
                })
                ->filter()
                ->countBy()
                ->sortDesc()
                ->take(10);

            return response()->json([
                'property_id' => $propertyId,
                'period_days' => 30,
                'metrics'     => [
                    'search_clicks'      => $searchClicks,
                    'search_impressions' => $searchImpressions,
                    'ctr_percent'        => $ctr,
                    'compare_count'      => $compareCount,
                    'velocity_48h'       => $velocity48h,
                    'total_views'        => $property->views,
                    'favorites_count'    => $property->favorites_count,
                    'rating'             => $property->rating,
                ],
                'click_position_distribution' => $clickPositions->countBy()->sortKeys(),
                'top_search_queries'          => $topQueries,
                'status' => [
                    'is_boosted' => (bool) $property->is_boosted,
                    'verified'   => (bool) $property->verified,
                    'created_at' => $property->created_at,
                    'days_old'   => $property->created_at->diffInDays(now()),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('getPropertyPopularityBreakdown failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to get breakdown'], 500);
        }
    }
}