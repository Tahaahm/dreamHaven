<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPropertyInteraction;
use App\Models\Property;
use App\Services\PropertyInteractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
    // ══════════════════════════════════════════════════════════════════════════
    public function storeCalculatorSignal(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) return response()->json(['success' => true]);

        $payload = [
            'userId'       => $user->id,
            'targetPrice'  => (float) $request->input('target_price_usd', 0),
            'savedSoFar'   => (float) $request->input('saved_so_far_usd', 0),
            'monthly'      => (float) $request->input('monthly_usd', 0),
            'targetYears'  => (int)   $request->input('target_years', 0),
            'mode'         => $request->input('mode', 'how_long'),
        ];

        // Respond immediately, write after
        app()->terminating(function () use ($payload) {
            try {
                $this->interactionService->storeCalculatorSignal(
                    userId: $payload['userId'],
                    targetPriceUsd: $payload['targetPrice'],
                    savedSoFarUsd: $payload['savedSoFar'],
                    monthlyUsd: $payload['monthly'],
                    targetYears: $payload['targetYears'],
                    mode: $payload['mode'],
                );
            } catch (\Throwable $e) {
                Log::warning('calculator-signal failed', ['error' => $e->getMessage()]);
            }
        });

        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  2. SEARCH SIGNAL
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearch(Request $request): JsonResponse
    {
        $user     = auth('sanctum')->user();
        $userId   = $user ? $user->id : 'guest_' . session()->getId();
        $query    = trim($request->input('query', ''));
        $results  = (int) $request->input('results_count', 0);
        $filters  = $request->input('active_filters', []);
        $language = $request->input('language', 'en');
        $propIds  = $request->input('property_ids', []);

        if (empty($query) && empty($filters)) {
            return response()->json(['success' => true]);
        }

        // Capture for closure — don't pass $request into terminating()
        $authUserId = $user?->id;
        $now        = now();

        app()->terminating(function () use (
            $authUserId,
            $userId,
            $query,
            $results,
            $filters,
            $language,
            $propIds,
            $now
        ) {
            try {
                if ($authUserId) {
                    // (a) append-only log
                    UserPropertyInteraction::create([
                        'user_id'          => $authUserId,
                        'property_id'      => 'search_signal',
                        'interaction_type' => 'search_query',
                        'metadata'         => json_encode([
                            'query'          => $query,
                            'results_count'  => $results,
                            'active_filters' => $filters,
                            'language'       => $language,
                            'timestamp'      => $now->toISOString(),
                        ]),
                        'created_at' => $now,
                    ]);

                    // (b) latest upsert for rec engine
                    UserPropertyInteraction::updateOrCreate(
                        [
                            'user_id'          => $authUserId,
                            'property_id'      => 'search_signal_latest',
                            'interaction_type' => 'search_query_latest',
                        ],
                        [
                            'metadata'   => json_encode([
                                'query'          => $query,
                                'results_count'  => $results,
                                'active_filters' => $filters,
                                'language'       => $language,
                                'updated_at'     => $now->toISOString(),
                            ]),
                            'created_at' => $now,
                        ]
                    );

                    Cache::forget("personalized_recs_{$authUserId}");
                }

                if (!empty($propIds)) {
                    $this->interactionService->trackSearchImpressions(
                        userId: $userId,
                        propertyIds: $propIds,
                        searchQuery: $query,
                        activeFilters: $filters
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('track-search failed', ['error' => $e->getMessage()]);
            }
        });

        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  3. SEARCH CLICK SIGNAL
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearchClick(Request $request): JsonResponse
    {
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
            return response()->json(['success' => true]);
        }

        $propertyId    = $request->input('property_id');
        $searchQuery   = trim($request->input('search_query', ''));
        $position      = (int) $request->input('result_position', 0);
        $activeFilters = $request->input('active_filters', []);
        $searchSession = $request->input('search_session');
        $userId        = $user->id;
        $userAgent     = $request->header('User-Agent');

        // ── Only DB read stays synchronous (it's a lightweight EXISTS check) ──
        $exists = Cache::remember(
            "prop_active_{$propertyId}",
            300, // cache for 5 min — property active status rarely changes
            fn() => Property::where('id', $propertyId)
                ->where('is_active', true)
                ->where('published', true)
                ->exists()
        );

        if (!$exists) {
            return response()->json(['success' => true]);
        }

        // Both writes go after response
        app()->terminating(function () use (
            $userId,
            $propertyId,
            $searchQuery,
            $position,
            $activeFilters,
            $searchSession,
            $userAgent
        ) {
            try {
                $this->interactionService->trackSearchClick(
                    userId: $userId,
                    propertyId: $propertyId,
                    searchQuery: $searchQuery,
                    resultPosition: $position,
                    activeFilters: array_merge($activeFilters, [
                        'search_session' => $searchSession,
                    ])
                );

                $this->interactionService->trackView($userId, $propertyId, [
                    'source'          => 'search_click',
                    'search_query'    => $searchQuery,
                    'result_position' => $position,
                    'user_agent'      => $userAgent,
                ]);
            } catch (\Throwable $e) {
                Log::warning('track-search-click failed', ['error' => $e->getMessage()]);
            }
        });

        return response()->json(['success' => true, 'property_id' => $propertyId]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  4. BULK SEARCH IMPRESSIONS
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearchImpressions(Request $request): JsonResponse
    {
        $user        = auth('sanctum')->user();
        $userId      = $user ? $user->id : 'guest_' . session()->getId();
        $propertyIds = array_slice($request->input('property_ids', []), 0, 100);
        $searchQuery = trim($request->input('search_query', ''));
        $filters     = $request->input('active_filters', []);

        if (empty($propertyIds)) {
            return response()->json(['success' => true]);
        }

        app()->terminating(function () use ($userId, $propertyIds, $searchQuery, $filters) {
            try {
                $this->interactionService->trackSearchImpressions(
                    userId: $userId,
                    propertyIds: $propertyIds,
                    searchQuery: $searchQuery,
                    activeFilters: $filters
                );
            } catch (\Throwable $e) {
                Log::warning('track-search-impressions failed', ['error' => $e->getMessage()]);
            }
        });

        return response()->json(['success' => true, 'tracked' => count($propertyIds)]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  5. FILTER SIGNAL
    // ══════════════════════════════════════════════════════════════════════════
    public function trackFilter(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) return response()->json(['success' => true]);

        $filters      = $request->input('filters', []);
        $resultsCount = (int) $request->input('results_count', 0);

        if (empty($filters)) return response()->json(['success' => true]);

        $userId  = $user->id;
        $signals = array_filter([
            'listing_type'  => $filters['listing_type']  ?? null,
            'property_type' => $filters['property_type'] ?? null,
            'city'          => $filters['city']          ?? null,
            'min_price_usd' => isset($filters['min_price']) ? (float) $filters['min_price'] : null,
            'max_price_usd' => isset($filters['max_price']) ? (float) $filters['max_price'] : null,
            'bedrooms'      => isset($filters['bedrooms'])  ? (int) $filters['bedrooms']    : null,
            'bathrooms'     => isset($filters['bathrooms']) ? (int) $filters['bathrooms']   : null,
            'furnished'     => $filters['furnished']  ?? null,
            'has_pool'      => $filters['has_pool']    ?? null,
            'has_parking'   => $filters['has_parking'] ?? null,
            'has_gym'       => $filters['has_gym']     ?? null,
            'has_garden'    => $filters['has_garden']  ?? null,
            'has_balcony'   => $filters['has_balcony'] ?? null,
            'results_count' => $resultsCount,
            'updated_at'    => now()->toISOString(),
        ], fn($v) => $v !== null && $v !== '' && $v !== false);

        app()->terminating(function () use ($userId, $signals) {
            try {
                UserPropertyInteraction::updateOrCreate(
                    [
                        'user_id'          => $userId,
                        'property_id'      => 'filter_signal',
                        'interaction_type' => 'filter_applied',
                    ],
                    [
                        'metadata'   => json_encode($signals),
                        'created_at' => now(),
                    ]
                );

                Cache::forget("personalized_recs_{$userId}");
            } catch (\Throwable $e) {
                Log::warning('track-filter failed', ['error' => $e->getMessage()]);
            }
        });

        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  6. COMPARE SIGNAL
    // ══════════════════════════════════════════════════════════════════════════
    public function trackCompare(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) return response()->json(['success' => true]);

        $propertyIds = $request->input('property_ids', []);
        $properties  = $request->input('properties', []);

        if (count($propertyIds) < 2) return response()->json(['success' => true]);

        $userId         = $user->id;
        $compareSession = (string) Str::uuid();
        $now            = now();

        // Build propMap from request data only — no DB call
        $propMap = [];
        foreach ($properties as $p) {
            if (!empty($p['id'])) $propMap[$p['id']] = $p;
        }

        app()->terminating(function () use (
            $userId,
            $propertyIds,
            $propMap,
            $compareSession,
            $now
        ) {
            try {
                // If Flutter didn't send property details, load from DB here
                // (inside terminating = after response, so it's fine)
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

                $insertRows = [];
                foreach ($propertyIds as $pid) {
                    $meta = $propMap[$pid] ?? [];
                    $insertRows[] = [
                        'user_id'          => $userId,
                        'property_id'      => $pid,
                        'interaction_type' => 'compare',
                        'metadata'         => json_encode([
                            'compare_session' => $compareSession,
                            'compared_with'   => array_values(
                                array_filter($propertyIds, fn($id) => $id !== $pid)
                            ),
                            'property_type' => $meta['type']         ?? null,
                            'price_usd'     => $meta['price_usd']    ?? null,
                            'city'          => $meta['city']         ?? null,
                            'listing_type'  => $meta['listing_type'] ?? null,
                            'timestamp'     => $now->toISOString(),
                        ]),
                        'created_at' => $now,
                    ];
                }

                UserPropertyInteraction::insert($insertRows);
                Cache::forget("personalized_recs_{$userId}");
                $this->interactionService->bustPopularityCache();
            } catch (\Throwable $e) {
                Log::warning('track-compare failed', ['error' => $e->getMessage()]);
            }
        });

        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  7. POPULARITY BREAKDOWN (admin/debug)
    // ══════════════════════════════════════════════════════════════════════════
    public function getPropertyPopularityBreakdown(string $propertyId): JsonResponse
    {
        try {
            $property = Property::find($propertyId);
            if (!$property) {
                return response()->json(['error' => 'Property not found'], 404);
            }

            $since30 = now()->subDays(30);

            [$searchClicks, $searchImpressions, $compareCount, $velocity48h] =
                collect([
                    ['search_click'],
                    ['search_impression'],
                    ['compare'],
                    ['view', 'search_click', 'favorite', 'compare'],
                ])->map(
                    fn($types) =>
                    UserPropertyInteraction::where('property_id', $propertyId)
                        ->whereIn('interaction_type', $types)
                        ->where(
                            'created_at',
                            '>=',
                            count($types) > 1 && in_array('view', $types)
                                ? now()->subHours(48)
                                : $since30
                        )
                        ->count()
                )->all();

            $clicks = UserPropertyInteraction::where('property_id', $propertyId)
                ->where('interaction_type', 'search_click')
                ->where('created_at', '>=', $since30)
                ->get()
                ->map(
                    fn($row) => is_array($row->metadata)
                        ? $row->metadata
                        : json_decode($row->metadata, true)
                );

            $clickPositions = $clicks->pluck('result_position')->filter()->values();
            $topQueries     = $clicks->pluck('query')->filter()->countBy()->sortDesc()->take(10);
            $ctr            = $searchImpressions > 0
                ? round(($searchClicks / $searchImpressions) * 100, 2)
                : 0;

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
                'status'                      => [
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