<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPropertyInteraction;
use App\Models\Property;
use App\Services\PropertyInteractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
                mode: $request->input('mode',        'how_long'),
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
    //  Stores what the user searched for, how many results came back,
    //  and which filters were active at the time.
    //  Uses updateOrCreate on (user_id, property_id='search_signal', type)
    //  so repeated searches update the same row — no table spam.
    //  But we also keep an INSERT-only log for trend analysis.
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearch(Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return response()->json(['success' => true]);

            $query        = trim($request->input('query', ''));
            $resultsCount = (int) $request->input('results_count', 0);
            $filters      = $request->input('active_filters', []);
            $language     = $request->input('language', 'en');

            if (empty($query)) return response()->json(['success' => true]);

            // ── INSERT a fresh row every search (for trend/keyword analysis) ──
            // We deliberately don't updateOrCreate here so we can see
            // which queries are repeated most often.
            UserPropertyInteraction::create([
                'user_id'          => $user->id,
                'property_id'      => 'search_signal',   // virtual ID
                'interaction_type' => 'search_query',
                'metadata'         => json_encode([
                    'query'          => $query,
                    'results_count'  => $resultsCount,
                    'active_filters' => $filters,
                    'language'       => $language,
                    'timestamp'      => now()->toISOString(),
                ]),
                'created_at'       => now(),
            ]);

            // ── Also update the "latest search" upsert row ────────────────────
            // This is what the recommendation engine reads — always fresh.
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

            // Bust recommendation cache
            \Illuminate\Support\Facades\Cache::forget("personalized_recs_{$user->id}");

            Log::info('🔍 Search signal stored', [
                'user_id'       => $user->id,
                'query'         => $query,
                'results_count' => $resultsCount,
                'language'      => $language,
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::warning('track-search failed (non-fatal)', ['error' => $e->getMessage()]);
            return response()->json(['success' => true]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  3. FILTER SIGNAL
    //  POST /api/v1/user/track-filter
    //
    //  Stores the full filter state the user applied.
    //  updateOrCreate — one row per user, always latest filters.
    //  This is the most reliable source for bedroom preference,
    //  price ceiling, and property type.
    // ══════════════════════════════════════════════════════════════════════════
    public function trackFilter(Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return response()->json(['success' => true]);

            $filters      = $request->input('filters', []);
            $resultsCount = (int) $request->input('results_count', 0);

            if (empty($filters)) return response()->json(['success' => true]);

            // Extract key signals for quick access by recommendation engine
            $extractedSignals = [
                'listing_type'  => $filters['listing_type']   ?? null,
                'property_type' => $filters['property_type']  ?? null,
                'city'          => $filters['city']           ?? null,
                'min_price_usd' => isset($filters['min_price']) ? (float)$filters['min_price'] : null,
                'max_price_usd' => isset($filters['max_price']) ? (float)$filters['max_price'] : null,
                'bedrooms'      => isset($filters['bedrooms'])  ? (int)$filters['bedrooms']    : null,
                'bathrooms'     => isset($filters['bathrooms']) ? (int)$filters['bathrooms']   : null,
                'furnished'     => $filters['furnished']        ?? null,
                'has_pool'      => $filters['has_pool']         ?? null,
                'has_parking'   => $filters['has_parking']      ?? null,
                'has_gym'       => $filters['has_gym']          ?? null,
                'has_garden'    => $filters['has_garden']       ?? null,
                'has_balcony'   => $filters['has_balcony']      ?? null,
                'results_count' => $resultsCount,
                'updated_at'    => now()->toISOString(),
            ];

            // Remove nulls — only store what was actually set
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

            // Bust recommendation cache
            \Illuminate\Support\Facades\Cache::forget("personalized_recs_{$user->id}");

            Log::info('🎛️ Filter signal stored', [
                'user_id'  => $user->id,
                'signals'  => $extractedSignals,
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::warning('track-filter failed (non-fatal)', ['error' => $e->getMessage()]);
            return response()->json(['success' => true]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  4. COMPARE SIGNAL
    //  POST /api/v1/user/track-compare
    //
    //  Stores each compared property as a separate interaction row
    //  (same pattern as favorites) so the recommendation engine can
    //  treat compared properties as high-intent signals (4× weight).
    //  We also store a cross-reference so we know which properties
    //  were compared together in the same session.
    // ══════════════════════════════════════════════════════════════════════════
    public function trackCompare(Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return response()->json(['success' => true]);

            $propertyIds = $request->input('property_ids', []);
            $properties  = $request->input('properties',  []);  // enriched data

            if (count($propertyIds) < 2) return response()->json(['success' => true]);

            // ── Build a lookup map from the enriched data Flutter sent ────────
            $propMap = [];
            foreach ($properties as $p) {
                if (!empty($p['id'])) $propMap[$p['id']] = $p;
            }

            // ── If Flutter didn't send enriched data, load from DB ────────────
            if (empty($propMap)) {
                $dbProps = Property::whereIn('id', $propertyIds)
                    ->select('id', 'type', 'price', 'address_details', 'listing_type')
                    ->get();
                foreach ($dbProps as $p) {
                    $propMap[$p->id] = [
                        'id'           => $p->id,
                        'type'         => $p->type['category']                          ?? null,
                        'price_usd'    => $p->price['usd']                              ?? null,
                        'city'         => $p->address_details['city']['en']             ?? null,
                        'listing_type' => $p->listing_type,
                    ];
                }
            }

            // ── Unique session ID links all compare rows together ─────────────
            $compareSession = (string) Str::uuid();
            $now = now();

            $insertRows = [];
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

            // Bust recommendation cache
            \Illuminate\Support\Facades\Cache::forget("personalized_recs_{$user->id}");

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
}