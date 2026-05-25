<?php

namespace App\Services;

use App\Models\UserPropertyInteraction;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PropertyInteractionService
{
    // ══════════════════════════════════════════════════════════════════════════
    //  TRACK VIEW
    // ══════════════════════════════════════════════════════════════════════════
    public function trackView(string $userId, string $propertyId, array $metadata = []): bool
    {
        try {
            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'property_id'      => $propertyId,
                'interaction_type' => 'view',
                'metadata'         => array_merge($metadata, [
                    'timestamp' => now()->toDateTimeString(),
                    'ip'        => request()->ip(),
                ]),
                'created_at' => now(),
            ]);
            $this->updateRecentlyViewed($userId, $propertyId);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to track view', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function updateRecentlyViewed(string $userId, string $propertyId): void
    {
        $user = User::find($userId);
        if (!$user) return;
        $rv = $user->recently_viewed_properties ?? [];
        $rv = array_filter($rv, fn($id) => $id !== $propertyId);
        array_unshift($rv, $propertyId);
        $rv = array_slice($rv, 0, 50);
        $user->update(['recently_viewed_properties' => $rv, 'last_activity_at' => now()]);
    }

    public function getRecentlyViewed(string $userId, int $limit = 20): Collection
    {
        $user = User::find($userId);
        if (!$user || empty($user->recently_viewed_properties)) return collect();
        $ids   = array_slice($user->recently_viewed_properties, 0, $limit);
        $props = Property::whereIn('id', $ids)
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending'])->get();
        return $props->sortBy(fn($p) => array_search($p->id, $ids))->values();
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  TRACK SEARCH CLICK
    //  Called when a user taps a property card from search results.
    //  This is the key signal that separates "appeared in search" from
    //  "user actually chose to click this from search results."
    //
    //  Also updates the property's search_clicks counter (JSON column or
    //  separate table — we store it in metadata on the interaction row
    //  and aggregate via query when needed).
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearchClick(
        string $userId,
        string $propertyId,
        string $searchQuery = '',
        int    $resultPosition = 0,
        array  $activeFilters = []
    ): bool {
        try {
            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'property_id'      => $propertyId,
                'interaction_type' => 'search_click',
                'metadata'         => json_encode([
                    'query'           => $searchQuery,
                    'result_position' => $resultPosition,
                    'active_filters'  => $activeFilters,
                    'timestamp'       => now()->toDateTimeString(),
                    'ip'              => request()->ip(),
                ]),
                'created_at' => now(),
            ]);

            // Bust popularity cache for this property
            Cache::forget("property_pop_score_{$propertyId}");
            Cache::forget('popular_properties_global');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to track search click', [
                'user_id'     => $userId,
                'property_id' => $propertyId,
                'error'       => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  TRACK SEARCH IMPRESSION
    //  Called when a property appears in search results (not clicked).
    //  Stored in bulk — one row per property per search session.
    //  This lets us compute CTR = search_clicks / search_impressions.
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearchImpressions(
        string $userId,
        array  $propertyIds,
        string $searchQuery = '',
        array  $activeFilters = []
    ): void {
        try {
            if (empty($propertyIds)) return;

            // Deduplicate per user+query within a 5-minute window
            $cacheKey = 'search_imp_' . $userId . '_' . md5($searchQuery . implode(',', $propertyIds));
            if (Cache::has($cacheKey)) return;
            Cache::put($cacheKey, true, 300);

            $now        = now();
            $insertData = [];

            foreach ($propertyIds as $position => $pid) {
                $insertData[] = [
                    'user_id'          => str_starts_with($userId, 'guest_') ? null : $userId,
                    'session_id'       => str_starts_with($userId, 'guest_')
                        ? str_replace('guest_', '', $userId)
                        : session()->getId(),
                    'property_id'      => $pid,
                    'interaction_type' => 'search_impression',
                    'metadata'         => json_encode([
                        'query'          => $searchQuery,
                        'position'       => $position,
                        'active_filters' => $activeFilters,
                    ]),
                    'created_at' => $now,
                ];
            }

            UserPropertyInteraction::insert($insertData);
        } catch (\Exception $e) {
            Log::warning('Failed to track search impressions', ['error' => $e->getMessage()]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  CALCULATOR SIGNAL
    // ══════════════════════════════════════════════════════════════════════════
    public function storeCalculatorSignal(
        string $userId,
        float  $targetPriceUsd,
        float  $savedSoFarUsd  = 0,
        float  $monthlyUsd     = 0,
        int    $targetYears    = 0,
        string $mode           = 'how_long'
    ): void {
        try {
            if ($targetPriceUsd <= 0) return;
            UserPropertyInteraction::updateOrCreate(
                ['user_id' => $userId, 'property_id' => 'calculator_signal', 'interaction_type' => 'calculator_search'],
                [
                    'metadata' => json_encode([
                        'target_price_usd' => $targetPriceUsd,
                        'saved_so_far_usd' => $savedSoFarUsd,
                        'monthly_usd'      => $monthlyUsd,
                        'target_years'     => $targetYears,
                        'mode'             => $mode,
                        'budget_min_usd'   => round($targetPriceUsd * 0.80),
                        'budget_max_usd'   => round($targetPriceUsd * 1.20),
                        'signal_strength'  => $this->calcSignalStrength($targetPriceUsd, $savedSoFarUsd, $monthlyUsd, $targetYears),
                        'updated_at'       => now()->toISOString(),
                    ]),
                    'created_at' => now(),
                ]
            );
            Cache::forget("personalized_recs_{$userId}");
        } catch (\Throwable $e) {
            Log::warning('Calculator signal failed', ['error' => $e->getMessage()]);
        }
    }

    private function calcSignalStrength(float $price, float $saved, float $monthly, int $years): int
    {
        $score = 20;
        if ($monthly > 0) {
            $score += 20;
            if ($price > 0 && ($monthly * 12 / $price) >= 0.05) $score += 20;
        }
        if ($years > 0)  $score += 10;
        if ($saved > 0) {
            $score += 20;
            if ($price > 0 && ($saved / $price) >= 0.10) $score += 10;
        }
        return min($score, 100);
    }

    private function getCalculatorSignal(string $userId): ?array
    {
        try {
            $row = UserPropertyInteraction::where('user_id', $userId)
                ->where('interaction_type', 'calculator_search')
                ->where('property_id', 'calculator_signal')
                ->where('created_at', '>=', now()->subDays(90))
                ->latest()->first();
            if (!$row || !$row->metadata) return null;
            $meta = is_array($row->metadata) ? $row->metadata : json_decode($row->metadata, true);
            return $meta ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  FILTER SIGNAL
    // ══════════════════════════════════════════════════════════════════════════
    private function getFilterSignal(string $userId): ?array
    {
        try {
            $row = UserPropertyInteraction::where('user_id', $userId)
                ->where('interaction_type', 'filter_applied')
                ->where('property_id', 'filter_signal')
                ->where('created_at', '>=', now()->subDays(60))
                ->latest()->first();
            if (!$row || !$row->metadata) return null;
            $meta = is_array($row->metadata) ? $row->metadata : json_decode($row->metadata, true);
            return $meta ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  COMPARE SIGNAL
    // ══════════════════════════════════════════════════════════════════════════
    private function getComparedProperties(string $userId): Collection
    {
        try {
            $ids = UserPropertyInteraction::where('user_id', $userId)
                ->where('interaction_type', 'compare')
                ->where('created_at', '>=', now()->subDays(30))
                ->whereNotIn('property_id', ['search_signal', 'filter_signal', 'calculator_signal'])
                ->pluck('property_id')->unique()->values();

            if ($ids->isEmpty()) return collect();
            return Property::whereIn('id', $ids)->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  SEARCH SIGNAL (latest query)
    // ══════════════════════════════════════════════════════════════════════════
    private function getLatestSearchSignal(string $userId): ?array
    {
        try {
            $row = UserPropertyInteraction::where('user_id', $userId)
                ->where('interaction_type', 'search_query_latest')
                ->where('property_id', 'search_signal_latest')
                ->where('created_at', '>=', now()->subDays(7))
                ->latest()->first();
            if (!$row || !$row->metadata) return null;
            $meta = is_array($row->metadata) ? $row->metadata : json_decode($row->metadata, true);
            return $meta ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  POPULARITY METRICS ENGINE
    //
    //  Computes a rich popularity score per property using multiple signals.
    //
    //  SIGNAL BREAKDOWN:
    //  ┌─────────────────────────────┬──────┬────────────────────────────────┐
    //  │ Signal                      │ Max  │ Why it matters                 │
    //  ├─────────────────────────────┼──────┼────────────────────────────────┤
    //  │ search_click_throughs       │  35  │ Highest active intent          │
    //  │ search_ctr (ratio)          │  20  │ Quality signal: people choose  │
    //  │ compare_count               │  20  │ Near-decision interest         │
    //  │ favorites_count             │  15  │ Explicit save intent           │
    //  │ views (passive)             │   5  │ Exposure (reduced weight)      │
    //  │ rating                      │   5  │ Quality confirmation           │
    //  │ velocity_bonus              │  15  │ Trending fast in last 48h      │
    //  │ recency_bonus               │  10  │ Fresh listings get a boost     │
    //  │ verified_bonus              │   5  │ Trust signal                   │
    //  └─────────────────────────────┴──────┴────────────────────────────────┘
    //
    //  Total max: 130 pts (uncapped intentionally — exceptional properties
    //  can score very high and should rank well above average ones).
    // ══════════════════════════════════════════════════════════════════════════
    public function computePopularityScores(
        ?string $listingType = null,
        ?string $city        = null,
        int     $days        = 30,
        int     $limit       = 50
    ): Collection {
        $cacheKey = "popularity_scores_{$listingType}_{$city}_{$days}_{$limit}";

        return Cache::remember($cacheKey, 600, function () use ($listingType, $city, $days, $limit) {

            // ── Step 1: Get search click counts per property (last N days) ────
            $searchClicks = DB::table('user_property_interactions')
                ->select('property_id', DB::raw('COUNT(*) as click_count'))
                ->where('interaction_type', 'search_click')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereNotIn('property_id', [
                    'calculator_signal',
                    'filter_signal',
                    'search_signal',
                    'search_signal_latest',
                ])
                ->groupBy('property_id')
                ->pluck('click_count', 'property_id');

            // ── Step 2: Get search impression counts per property ──────────────
            $searchImpressions = DB::table('user_property_interactions')
                ->select('property_id', DB::raw('COUNT(*) as impression_count'))
                ->where('interaction_type', 'search_impression')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereNotIn('property_id', [
                    'calculator_signal',
                    'filter_signal',
                    'search_signal',
                    'search_signal_latest',
                ])
                ->groupBy('property_id')
                ->pluck('impression_count', 'property_id');

            // ── Step 3: Get compare counts per property ────────────────────────
            $compareCounts = DB::table('user_property_interactions')
                ->select('property_id', DB::raw('COUNT(*) as compare_count'))
                ->where('interaction_type', 'compare')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereNotIn('property_id', [
                    'calculator_signal',
                    'filter_signal',
                    'search_signal',
                    'search_signal_latest',
                ])
                ->groupBy('property_id')
                ->pluck('compare_count', 'property_id');

            // ── Step 4: Get velocity — interactions in last 48h ───────────────
            $velocityData = DB::table('user_property_interactions')
                ->select('property_id', DB::raw('COUNT(*) as recent_count'))
                ->whereIn('interaction_type', ['view', 'search_click', 'favorite', 'compare'])
                ->where('created_at', '>=', now()->subHours(48))
                ->whereNotIn('property_id', [
                    'calculator_signal',
                    'filter_signal',
                    'search_signal',
                    'search_signal_latest',
                ])
                ->groupBy('property_id')
                ->pluck('recent_count', 'property_id');

            // ── Step 5: Build property query ──────────────────────────────────
            $query = Property::query()
                ->where('is_active', true)
                ->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented']);

            if ($listingType) {
                $query->where('listing_type', $listingType);
            }

            if ($city) {
                $query->whereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                    [strtolower($city)]
                );
            }

            $properties = $query->get();

            // ── Step 6: Score each property ───────────────────────────────────
            return $properties->map(function ($property) use (
                $searchClicks,
                $searchImpressions,
                $compareCounts,
                $velocityData
            ) {
                $pid = $property->id;

                $clicks      = (int) ($searchClicks[$pid]      ?? 0);
                $impressions = (int) ($searchImpressions[$pid] ?? 0);
                $compares    = (int) ($compareCounts[$pid]     ?? 0);
                $velocity    = (int) ($velocityData[$pid]      ?? 0);

                // CTR score — only meaningful if property has impressions
                $ctr = $impressions > 0 ? ($clicks / $impressions) : 0;
                $ctrScore = min($ctr * 100, 20); // 20% CTR = full 20pts; rare but possible

                // Search click score — logarithmic so 1 click = 3pts, 10 = 11pts, 100 = 22pts
                $clickScore = $clicks > 0 ? min(log($clicks + 1, 2) * 5, 35) : 0;

                // Compare score — logarithmic
                $compareScore = $compares > 0 ? min(log($compares + 1, 2) * 4, 20) : 0;

                // Favorites score (reduced from old formula — views take over)
                $favScore = min($property->favorites_count * 0.5, 15);

                // Views score — now a weaker signal (was dominant before)
                $viewScore = min(log(($property->views ?? 0) + 1, 10) * 2, 5);

                // Rating score
                $ratingScore = ($property->rating ?? 0) * 1;

                // Velocity bonus — trending in last 48h
                $velocityScore = $velocity > 0 ? min(log($velocity + 1, 2) * 3, 15) : 0;

                // Recency bonus
                $daysSinceCreated = $property->created_at->diffInDays(now());
                $recencyScore = match (true) {
                    $daysSinceCreated <= 3  => 10,
                    $daysSinceCreated <= 7  => 7,
                    $daysSinceCreated <= 14 => 4,
                    $daysSinceCreated <= 30 => 2,
                    default                 => 0,
                };

                // Verified bonus
                $verifiedScore = $property->verified ? 5 : 0;

                $totalScore = $clickScore + $ctrScore + $compareScore + $favScore
                    + $viewScore + $ratingScore + $velocityScore
                    + $recencyScore + $verifiedScore;

                $property->popularity_score = round($totalScore, 2);
                $property->popularity_breakdown = [
                    'search_clicks'    => $clicks,
                    'search_ctr'       => round($ctr * 100, 1) . '%',
                    'compare_count'    => $compares,
                    'velocity_48h'     => $velocity,
                    'scores' => [
                        'click_score'    => round($clickScore, 1),
                        'ctr_score'      => round($ctrScore, 1),
                        'compare_score'  => round($compareScore, 1),
                        'fav_score'      => round($favScore, 1),
                        'view_score'     => round($viewScore, 1),
                        'velocity_score' => round($velocityScore, 1),
                        'recency_score'  => $recencyScore,
                        'verified_score' => $verifiedScore,
                        'total'          => round($totalScore, 2),
                    ],
                ];

                return $property;
            })
                ->sortByDesc('popularity_score')
                ->values();
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  GET POPULAR PROPERTIES (public API method)
    //
    //  Returns the top N popular properties with full scoring breakdown.
    //  Supports filtering by listing type and city so the result is
    //  contextually relevant (not just globally popular).
    // ══════════════════════════════════════════════════════════════════════════
    public function getPopularProperties(
        int     $limit       = 20,
        ?string $listingType = null,
        ?string $city        = null,
        int     $days        = 30
    ): Collection {
        return $this->computePopularityScores($listingType, $city, $days, $limit * 3)
            ->take($limit);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  FEATURED PROPERTIES ENGINE (Advanced, 2-Layer)
    //
    //  LAYER 1 — Guaranteed Featured (paid / platform-verified):
    //    • is_boosted = true AND boost is active
    //    • Always included regardless of user context
    //    • Scored by: boost recency + verification + popularity score
    //    • Capped at 40% of total requested limit
    //
    //  LAYER 2 — Contextually Featured (personalized):
    //    • Derived from user's city, listing type, price range (signals)
    //    • Scored by: popularity score + relevance to user context + freshness
    //    • Falls back to global high-performers if no user context
    //    • Fills remaining 60% of the limit
    //
    //  SIGNALS USED FOR CONTEXT:
    //    • filterSignal  → city, listing_type, price ceiling, property_type
    //    • searchSignal  → listing_type hint, query keywords
    //    • calcSignal    → budget range
    //    • recentViews   → inferred city and type preference
    //
    //  DIVERSITY ENFORCEMENT:
    //    • Max 2 properties from same city in Layer 2
    //    • Max 2 properties of same type in Layer 2
    //    • Layer 1 (boosted) is exempt from diversity cap
    // ══════════════════════════════════════════════════════════════════════════
    public function getFeaturedProperties(
        int     $limit  = 10,
        ?string $userId = null
    ): Collection {
        // ── Resolve user context ──────────────────────────────────────────────
        $context = $this->resolveUserContext($userId);

        // ── LAYER 1: Guaranteed boosted properties (max 40% of limit) ────────
        $boostedLimit = (int) ceil($limit * 0.40);

        $boostedQuery = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('is_boosted', true)
            ->where('boost_start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('boost_end_date')
                    ->orWhere('boost_end_date', '>=', now());
            });

        // Preferentially serve boosted properties that match user context
        if ($context['listing_type']) {
            // Prefer matching but don't exclude non-matching
            $boostedQuery->orderByRaw(
                "CASE WHEN listing_type = ? THEN 1 ELSE 2 END ASC",
                [$context['listing_type']]
            );
        }

        if ($context['city']) {
            $boostedQuery->orderByRaw(
                "CASE WHEN LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ? THEN 1 ELSE 2 END ASC",
                [strtolower($context['city'])]
            );
        }

        $boostedProperties = $boostedQuery
            ->selectRaw("
                *,
                (
                    (CASE WHEN verified = 1 THEN 15 ELSE 0 END) +
                    (LEAST(favorites_count, 50) * 0.5) +
                    (rating * 5) +
                    (CASE
                        WHEN DATEDIFF(NOW(), boost_start_date) <= 1  THEN 15
                        WHEN DATEDIFF(NOW(), boost_start_date) <= 7  THEN 10
                        WHEN DATEDIFF(NOW(), boost_start_date) <= 30 THEN 5
                        ELSE 0
                    END)
                ) as layer1_score
            ")
            ->orderByDesc('layer1_score')
            ->limit($boostedLimit)
            ->get();

        $boostedIds = $boostedProperties->pluck('id')->toArray();

        // ── LAYER 2: Contextually featured (60% of limit) ─────────────────────
        $contextualLimit = $limit - $boostedProperties->count();

        $contextualProperties = $this->getContextualFeatured(
            limit: $contextualLimit,
            context: $context,
            excludeIds: $boostedIds,
            userId: $userId
        );

        // ── Merge layers ──────────────────────────────────────────────────────
        $merged = $boostedProperties->merge($contextualProperties);

        // Tag each property with its featured layer and reason
        $merged = $merged->map(function ($property) use ($boostedIds) {
            $property->featured_layer  = in_array($property->id, $boostedIds) ? 1 : 2;
            $property->featured_reason = $this->resolveFeaturedReason($property);
            return $property;
        });

        return $merged->values();
    }

    // ── Resolve user context from all available signals ───────────────────────
    private function resolveUserContext(?string $userId): array
    {
        $context = [
            'city'          => null,
            'listing_type'  => null,
            'property_type' => null,
            'min_price'     => null,
            'max_price'     => null,
            'bedrooms'      => null,
        ];

        if (!$userId) return $context;

        try {
            // Priority 1: Filter signal (most explicit)
            $filterSignal = $this->getFilterSignal($userId);
            if ($filterSignal) {
                $context['city']          = $filterSignal['city']          ?? null;
                $context['listing_type']  = $filterSignal['listing_type']  ?? null;
                $context['property_type'] = $filterSignal['property_type'] ?? null;
                $context['max_price']     = $filterSignal['max_price_usd'] ?? null;
                $context['min_price']     = $filterSignal['min_price_usd'] ?? null;
                $context['bedrooms']      = $filterSignal['bedrooms']      ?? null;
            }

            // Priority 2: Search signal fills gaps
            $searchSignal = $this->getLatestSearchSignal($userId);
            if ($searchSignal) {
                $filters = $searchSignal['active_filters'] ?? [];
                if (!$context['listing_type'] && !empty($filters['listing_type'])) {
                    $context['listing_type'] = $filters['listing_type'];
                }
                if (!$context['city'] && !empty($filters['city'])) {
                    $context['city'] = $filters['city'];
                }
            }

            // Priority 3: Calculator signal fills price gaps
            $calcSignal = $this->getCalculatorSignal($userId);
            if ($calcSignal && !$context['max_price']) {
                $context['max_price'] = $calcSignal['budget_max_usd'] ?? null;
                $context['min_price'] = $calcSignal['budget_min_usd'] ?? null;
            }

            // Priority 4: Infer from recent behavior (views + favorites)
            if (!$context['city'] || !$context['listing_type']) {
                $recentInteractions = DB::table('user_property_interactions')
                    ->where('user_id', $userId)
                    ->whereIn('interaction_type', ['view', 'favorite'])
                    ->where('created_at', '>=', now()->subDays(30))
                    ->whereNotIn('property_id', [
                        'calculator_signal',
                        'filter_signal',
                        'search_signal',
                        'search_signal_latest',
                    ])
                    ->pluck('property_id')
                    ->unique()
                    ->take(20);

                if ($recentInteractions->isNotEmpty()) {
                    $recentProps = Property::whereIn('id', $recentInteractions)
                        ->select('id', 'address_details', 'listing_type')
                        ->get();

                    if (!$context['city']) {
                        $cityMode = $recentProps
                            ->map(fn($p) => $p->address_details['city']['en'] ?? null)
                            ->filter()
                            ->mode();
                        $context['city'] = $cityMode[0] ?? null;
                    }

                    if (!$context['listing_type']) {
                        $typeMode = $recentProps->pluck('listing_type')->filter()->mode();
                        $context['listing_type'] = $typeMode[0] ?? null;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('resolveUserContext failed', ['error' => $e->getMessage()]);
        }

        return $context;
    }

    // ── Layer 2: Contextually featured with diversity enforcement ─────────────
    private function getContextualFeatured(
        int     $limit,
        array   $context,
        array   $excludeIds,
        ?string $userId
    ): Collection {
        // Get popularity scores for candidate pool
        $popularityPool = $this->computePopularityScores(
            listingType: $context['listing_type'],
            city: null, // Don't pre-filter by city — we'll score it below
            days: 30,
            limit: $limit * 5
        );

        // Score each candidate for contextual relevance
        $scored = $popularityPool
            ->whereNotIn('id', $excludeIds)
            ->filter(fn($p) => !in_array($p->status, ['sold', 'rented', 'cancelled', 'pending']))
            ->map(function ($property) use ($context) {
                $relevanceScore = 0;

                // City match (+30 pts)
                if ($context['city']) {
                    $propCity = strtolower($property->address_details['city']['en'] ?? '');
                    if ($propCity === strtolower($context['city'])) {
                        $relevanceScore += 30;
                    }
                }

                // Listing type match (+25 pts)
                if ($context['listing_type'] && $property->listing_type === $context['listing_type']) {
                    $relevanceScore += 25;
                }

                // Property type match (+15 pts)
                if ($context['property_type']) {
                    $propType = strtolower($property->type['category'] ?? '');
                    if ($propType === strtolower($context['property_type'])) {
                        $relevanceScore += 15;
                    }
                }

                // Price range fit (+20 pts)
                $propPrice = $property->price['usd'] ?? 0;
                if ($context['min_price'] && $context['max_price'] && $propPrice > 0) {
                    if ($propPrice >= $context['min_price'] && $propPrice <= $context['max_price']) {
                        $relevanceScore += 20;
                    } elseif ($propPrice <= $context['max_price']) {
                        $relevanceScore += 10; // At least within budget
                    }
                }

                // Bedrooms match (+10 pts)
                if ($context['bedrooms']) {
                    $propBeds = (int) ($property->rooms['bedroom']['count'] ?? 0);
                    if ($propBeds === (int) $context['bedrooms']) {
                        $relevanceScore += 10;
                    }
                }

                // Combine popularity + relevance
                $property->layer2_score = ($property->popularity_score ?? 0) + $relevanceScore;
                $property->relevance_score = $relevanceScore;

                return $property;
            })
            ->sortByDesc('layer2_score')
            ->values();

        // ── Diversity enforcement ─────────────────────────────────────────────
        $selected  = collect();
        $cityCount = [];
        $typeCount = [];

        $cityMax = max(2, (int) ceil($limit * 0.35));
        $typeMax = max(2, (int) ceil($limit * 0.45));

        foreach ($scored as $property) {
            if ($selected->count() >= $limit) break;

            $city = strtolower($property->address_details['city']['en'] ?? 'unknown');
            $type = strtolower($property->type['category'] ?? 'unknown');

            $cityCount[$city] = $cityCount[$city] ?? 0;
            $typeCount[$type] = $typeCount[$type] ?? 0;

            // Skip if we've hit the cap for this city or type
            if ($cityCount[$city] >= $cityMax || $typeCount[$type] >= $typeMax) {
                continue;
            }

            $selected->push($property);
            $cityCount[$city]++;
            $typeCount[$type]++;
        }

        // If diversity enforcement left us short, fill with whatever's left
        if ($selected->count() < $limit) {
            $remaining = $scored
                ->whereNotIn('id', $selected->pluck('id')->toArray())
                ->take($limit - $selected->count());
            $selected = $selected->merge($remaining);
        }

        // If still short (not enough contextual matches), fall back globally
        if ($selected->count() < $limit) {
            $needed  = $limit - $selected->count();
            $fallback = $this->getGlobalFeaturedFallback(
                $needed,
                array_merge($excludeIds, $selected->pluck('id')->toArray())
            );
            $selected = $selected->merge($fallback);
        }

        return $selected->values();
    }

    // ── Global fallback for featured when context is missing ──────────────────
    private function getGlobalFeaturedFallback(int $limit, array $excludeIds): Collection
    {
        return Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $excludeIds)
            ->selectRaw("
                *,
                (
                    (CASE WHEN verified   = 1 THEN 20 ELSE 0 END) +
                    (LEAST(favorites_count, 50) * 0.8) +
                    (rating * 5) +
                    (LEAST(views, 200) * 0.1) +
                    (CASE
                        WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 15
                        WHEN DATEDIFF(NOW(), created_at) <= 14 THEN 10
                        WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 5
                        ELSE 0
                    END)
                ) as fallback_score
            ")
            ->orderByDesc('fallback_score')
            ->limit($limit)
            ->get();
    }

    // ── Resolve human-readable featured reason ────────────────────────────────
    private function resolveFeaturedReason(Property $property): array
    {
        $reasons = [];

        if ($property->is_boosted) {
            $reasons[] = ['key' => 'promoted',    'label' => 'Promoted listing'];
        }
        if ($property->verified) {
            $reasons[] = ['key' => 'verified',    'label' => 'Verified property'];
        }
        if (($property->popularity_breakdown['scores']['velocity_score'] ?? 0) > 8) {
            $reasons[] = ['key' => 'trending',    'label' => 'Trending now'];
        }
        if (($property->popularity_breakdown['scores']['click_score'] ?? 0) > 15) {
            $reasons[] = ['key' => 'high_demand', 'label' => 'High search demand'];
        }
        if (($property->popularity_breakdown['scores']['ctr_score'] ?? 0) > 10) {
            $reasons[] = ['key' => 'popular',     'label' => 'Frequently chosen from search'];
        }
        if (($property->relevance_score ?? 0) >= 30) {
            $reasons[] = ['key' => 'relevant',    'label' => 'Matches your preferences'];
        }
        if ($property->created_at->diffInDays(now()) <= 7) {
            $reasons[] = ['key' => 'new',         'label' => 'New listing'];
        }
        if ($property->favorites_count > 10) {
            $reasons[] = ['key' => 'saved',       'label' => 'Frequently saved'];
        }
        if (($property->popularity_breakdown['compare_count'] ?? 0) > 5) {
            $reasons[] = ['key' => 'compared',    'label' => 'Often compared by buyers'];
        }

        return $reasons ?: [['key' => 'quality', 'label' => 'Top quality listing']];
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  PERSONALIZED RECOMMENDATIONS — all 6 signals
    //
    //  SIGNAL HIERARCHY (highest → lowest weight):
    //  ┌─────────────────────────┬──────┬────────────────────────────────────┐
    //  │ Signal                  │ Wt   │ What it contributes                │
    //  ├─────────────────────────┼──────┼────────────────────────────────────┤
    //  │ favorite                │  5×  │ type, city, price                  │
    //  │ compare                 │  4×  │ type, city, price (near-decision)  │
    //  │ filter_applied          │  3×  │ bedrooms, price ceiling, type      │
    //  │ search_query            │  2×  │ listing_type hint from filters     │
    //  │ view                    │  1×  │ type, city, price                  │
    //  │ calculator_search       │  —   │ budget range blending (0.0–1.0)    │
    //  └─────────────────────────┴──────┴────────────────────────────────────┘
    // ══════════════════════════════════════════════════════════════════════════
    public function getPersonalizedRecommendations(string $userId, int $limit = 20): Collection
    {
        $user = User::find($userId);
        if (!$user) return $this->getGeneralRecommendations($limit);

        // ── 1. Load all 6 signals ─────────────────────────────────────────────
        $calcSignal   = $this->getCalculatorSignal($userId);
        $filterSignal = $this->getFilterSignal($userId);
        $searchSignal = $this->getLatestSearchSignal($userId);
        $comparedData = $this->getComparedProperties($userId);

        // ── 2. Load behavioral interactions (views + favorites, 60 days) ───────
        $interactions = DB::table('user_property_interactions')
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(60))
            ->whereIn('interaction_type', ['view', 'favorite'])
            ->whereNotIn('property_id', [
                'calculator_signal',
                'filter_signal',
                'search_signal',
                'search_signal_latest',
            ])
            ->select('property_id', 'interaction_type')
            ->get();

        $viewedIds    = $interactions->where('interaction_type', 'view')->pluck('property_id')->toArray();
        $favoritedIds = $interactions->where('interaction_type', 'favorite')->pluck('property_id')->toArray();
        $comparedIds  = $comparedData->pluck('id')->toArray();

        $allInteractedIds = array_unique(array_merge($viewedIds, $favoritedIds, $comparedIds));
        $hasBrowseHistory = !empty($allInteractedIds);

        // Budget signal
        $hasBudget      = $calcSignal !== null && ($calcSignal['budget_min_usd'] ?? 0) > 0;
        $signalStrength = (int) ($calcSignal['signal_strength'] ?? 0);
        $budgetWeight   = $hasBudget ? ($signalStrength / 100) : 0.0;

        // ── 3. No history at all ──────────────────────────────────────────────
        if (!$hasBrowseHistory && $comparedData->isEmpty()) {
            if ($filterSignal) {
                return $this->getFilterMatchedRecommendations($filterSignal, $limit, []);
            }
            if ($hasBudget && $signalStrength >= 40) {
                return $this->getBudgetMatchedRecommendations($calcSignal, $limit, []);
            }
            return $this->getGeneralRecommendations($limit);
        }

        // ── 4. Build behavioral + compare profile ─────────────────────────────
        $viewedData    = Property::whereIn('id', $viewedIds)->get();
        $favoritedData = Property::whereIn('id', $favoritedIds)->get();

        // Property type (favorites 5×, compared 4×, viewed 1×)
        $preferredTypes = collect()
            ->merge($viewedData->pluck('type')->map(fn($t) => $t['category'] ?? null))
            ->merge($favoritedData->pluck('type')->map(fn($t) => $t['category'] ?? null)->replicate(5))
            ->merge($comparedData->pluck('type')->map(fn($t) => $t['category'] ?? null)->replicate(4))
            ->filter()->countBy()->sortDesc()->keys()->take(3)->toArray();

        if ($filterSignal && !empty($filterSignal['property_type'])) {
            array_unshift($preferredTypes, $filterSignal['property_type']);
            $preferredTypes = array_unique($preferredTypes);
        }

        // Listing type
        $listingTypeVotes = collect()
            ->merge($viewedData->pluck('listing_type'))
            ->merge($favoritedData->pluck('listing_type')->replicate(5))
            ->merge($comparedData->pluck('listing_type')->replicate(4))
            ->filter();

        $preferredListingType = $listingTypeVotes->mode()[0] ?? null;

        if ($filterSignal && !empty($filterSignal['listing_type'])) {
            $preferredListingType = $filterSignal['listing_type'];
        }
        if ($searchSignal && !empty($searchSignal['active_filters']['listing_type'])) {
            $preferredListingType = $searchSignal['active_filters']['listing_type'];
        }

        // Price range (blended)
        $priceSource    = $favoritedData->isNotEmpty() ? $favoritedData : $viewedData;
        $behaviorAvg    = $priceSource->avg(fn($p) => $p->price['usd'] ?? 0) ?? 0;
        $compareAvg     = $comparedData->avg(fn($p) => $p->price['usd'] ?? 0) ?? 0;

        $behaviorBlended = $behaviorAvg;
        if ($compareAvg > 0 && $behaviorAvg > 0) {
            $behaviorBlended = (($behaviorAvg * 1) + ($compareAvg * 4)) / 5;
        } elseif ($compareAvg > 0) {
            $behaviorBlended = $compareAvg;
        }

        if ($hasBudget && $behaviorBlended > 0) {
            $calcMid     = ($calcSignal['budget_min_usd'] + $calcSignal['budget_max_usd']) / 2;
            $blendedAvg  = ($behaviorBlended * (1 - $budgetWeight)) + ($calcMid * $budgetWeight);
        } elseif ($hasBudget) {
            $blendedAvg = ($calcSignal['budget_min_usd'] + $calcSignal['budget_max_usd']) / 2;
        } else {
            $blendedAvg = $behaviorBlended;
        }

        $hardCeilingUsd = null;
        if ($filterSignal && !empty($filterSignal['max_price_usd'])) {
            $hardCeilingUsd = (float) $filterSignal['max_price_usd'];
        }

        $hasExplicitFilter = $filterSignal && !empty($filterSignal['max_price_usd']);
        $priceTolerance    = $hasBudget
            ? max(0.25, 0.45 - ($budgetWeight * 0.20))
            : ($hasExplicitFilter ? 0.20 : 0.35);

        $preferredBedrooms = null;
        if ($filterSignal && !empty($filterSignal['bedrooms'])) {
            $preferredBedrooms = (int) $filterSignal['bedrooms'];
        }

        // Cities
        $preferredCities = collect()
            ->merge($favoritedData->map(fn($p) => $p->address_details['city']['en'] ?? null)->replicate(5))
            ->merge($comparedData->map(fn($p) => $p->address_details['city']['en'] ?? null)->replicate(4))
            ->merge($viewedData->map(fn($p) => $p->address_details['city']['en'] ?? null))
            ->filter()->countBy()->sortDesc()->keys()->take(3)->toArray();

        if ($filterSignal && !empty($filterSignal['city'])) {
            array_unshift($preferredCities, $filterSignal['city']);
            $preferredCities = array_unique($preferredCities);
        }

        // ── 5. Build query ────────────────────────────────────────────────────
        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $allInteractedIds);

        if (!empty($preferredTypes)) {
            $query->where(function ($q) use ($preferredTypes) {
                foreach ($preferredTypes as $type) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                        [strtolower($type)]
                    );
                }
            });
        }

        if ($preferredListingType) {
            $query->where('listing_type', $preferredListingType);
        }

        if ($blendedAvg > 0) {
            $priceMin = $blendedAvg * (1 - $priceTolerance);
            $priceMax = $blendedAvg * (1 + $priceTolerance);
            if ($hardCeilingUsd !== null) {
                $priceMax = min($priceMax, $hardCeilingUsd);
            }
            $query->whereBetween(
                DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2))"),
                [$priceMin, $priceMax]
            );
        }

        if ($preferredBedrooms !== null) {
            $query->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) = ?",
                [$preferredBedrooms]
            );
        }

        if (!empty($preferredCities)) {
            $query->where(function ($q) use ($preferredCities) {
                foreach ($preferredCities as $city) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                        [strtolower($city)]
                    );
                }
            });
        }

        // ── 6. Score ──────────────────────────────────────────────────────────
        $budgetBonusExpr = '0';
        if ($hasBudget) {
            $bMin  = (float) $calcSignal['budget_min_usd'];
            $bMax  = (float) $calcSignal['budget_max_usd'];
            $bonus = round(35 * $budgetWeight);
            $budgetBonusExpr = "
                (CASE
                    WHEN CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2))
                         BETWEEN {$bMin} AND {$bMax}
                    THEN {$bonus}
                    ELSE 0
                END)
            ";
        }

        $results = $query->selectRaw("
            *,
            (
                (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                (CASE WHEN verified   = 1 THEN 20 ELSE 0 END) +
                (LEAST(views, 100) * 0.15) +
                (LEAST(favorites_count, 50) * 0.8) +
                (rating * 5) +
                (CASE
                    WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 15
                    WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10
                    ELSE 0
                END) +
                {$budgetBonusExpr}
            ) as recommendation_score
        ")
            ->orderByDesc('recommendation_score')
            ->limit($limit)
            ->get();

        // ── 7. Fallback ───────────────────────────────────────────────────────
        if ($results->count() < $limit) {
            $needed      = $limit - $results->count();
            $existingIds = array_merge($allInteractedIds, $results->pluck('id')->toArray());

            $fallback = Property::query()
                ->where('is_active', true)->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                ->whereNotIn('id', $existingIds)
                ->when($preferredListingType, fn($q) => $q->where('listing_type', $preferredListingType))
                ->selectRaw("*, (
                    (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                    (CASE WHEN verified   = 1 THEN 20 ELSE 0 END) +
                    (LEAST(favorites_count, 50) * 0.8) +
                    (rating * 5) +
                    {$budgetBonusExpr}
                ) as recommendation_score")
                ->orderByDesc('recommendation_score')
                ->limit($needed)
                ->get();

            $results = $results->merge($fallback);
        }

        return $results;
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

        if (!empty($filterSignal['listing_type'])) {
            $query->where('listing_type', $filterSignal['listing_type']);
        }
        if (!empty($filterSignal['property_type'])) {
            $query->whereRaw(
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
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) <= ?",
                [(float) $filterSignal['max_price_usd']]
            );
        }
        if (!empty($filterSignal['min_price_usd'])) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) >= ?",
                [(float) $filterSignal['min_price_usd']]
            );
        }
        if (!empty($filterSignal['bedrooms'])) {
            $query->whereRaw(
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
            (CASE WHEN DATEDIFF(NOW(), created_at) <= 7 THEN 15
                  WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10 ELSE 0 END)
        ) as recommendation_score')
            ->orderByDesc('recommendation_score')
            ->limit($limit)
            ->get();
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  BUDGET-MATCHED RECOMMENDATIONS
    // ══════════════════════════════════════════════════════════════════════════
    private function getBudgetMatchedRecommendations(
        array $calcSignal,
        int   $limit,
        array $excludeIds = []
    ): Collection {
        $min = (float) ($calcSignal['budget_min_usd'] ?? 0);
        $max = (float) ($calcSignal['budget_max_usd'] ?? 0);
        if ($min <= 0 || $max <= 0) return $this->getGeneralRecommendations($limit);

        $results = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $excludeIds)
            ->whereBetween(
                DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2))"),
                [$min, $max]
            )
            ->selectRaw('*, (
                (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                (CASE WHEN verified   = 1 THEN 20 ELSE 0 END) +
                (LEAST(views, 100) * 0.15) +
                (LEAST(favorites_count, 50) * 0.8) +
                (rating * 5) +
                (CASE WHEN DATEDIFF(NOW(), created_at) <= 7 THEN 15
                      WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10 ELSE 0 END)
            ) as recommendation_score')
            ->orderByDesc('recommendation_score')
            ->limit($limit)->get();

        if ($results->count() < $limit) {
            $needed  = $limit - $results->count();
            $general = $this->getGeneralRecommendations($needed + 5)
                ->whereNotIn('id', array_merge($excludeIds, $results->pluck('id')->toArray()))
                ->take($needed);
            $results = $results->merge($general);
        }
        return $results;
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

    // ══════════════════════════════════════════════════════════════════════════
    //  IMPRESSIONS
    // ══════════════════════════════════════════════════════════════════════════
    public function trackImpressions(string $userId, $properties, string $sourceEndpoint, array $extra = []): void
    {
        if (empty($properties) || (is_object($properties) && method_exists($properties, 'isEmpty') && $properties->isEmpty())) return;
        try {
            $propertyIds = collect($properties)->pluck('id')->sort()->implode(',');
            $cacheKey    = 'impressions_' . $userId . '_' . $sourceEndpoint . '_' . md5($propertyIds);
            if (Cache::has($cacheKey)) return;
            Cache::put($cacheKey, true, 300);

            $timestamp  = now();
            $insertData = [];
            $ip         = request()->ip();
            $isGuest    = str_starts_with($userId, 'guest_');
            $sessionId  = $isGuest ? str_replace('guest_', '', $userId) : session()->getId();

            foreach ($properties as $property) {
                $insertData[] = [
                    'user_id'          => $isGuest ? null : $userId,
                    'session_id'       => $sessionId,
                    'property_id'      => $property->id,
                    'interaction_type' => 'impression',
                    'metadata'         => json_encode(array_merge([
                        'source_endpoint' => $sourceEndpoint,
                        'ip'              => $ip,
                        'is_guest'        => $isGuest,
                    ], $extra)),
                    'created_at' => $timestamp,
                ];
            }
            UserPropertyInteraction::insert($insertData);
        } catch (\Exception $e) {
            Log::error('Failed to track impressions', ['error' => $e->getMessage()]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  CACHE BUSTING HELPERS
    // ══════════════════════════════════════════════════════════════════════════
    public function bustPopularityCache(?string $listingType = null, ?string $city = null): void
    {
        $patterns = [
            "popularity_scores_{$listingType}_{$city}_30_150",
            "popularity_scores_{$listingType}_{$city}_30_100",
            "popularity_scores_{$listingType}_{$city}_30_60",
            'popular_properties_global',
        ];

        foreach ($patterns as $key) {
            Cache::forget($key);
        }
    }

    public function bustFeaturedCache(?string $userId = null): void
    {
        if ($userId) {
            Cache::forget("featured_contextual_{$userId}");
        }

        // Bust global featured cache variants
        foreach (['balanced', 'premium', 'engagement', 'recent', 'advanced'] as $strategy) {
            foreach ([5, 10, 20, 50] as $limit) {
                Cache::forget("featured_properties_{$strategy}_{$limit}");
            }
        }
    }
}