<?php

namespace App\Services;

use App\Models\UserPropertyInteraction;
use App\Models\User;
use App\Models\Property;
use App\Services\Intelligence\FeedBrain;
use App\Services\Intelligence\UserTasteProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PropertyInteractionService (v2)
 * --------------------------------
 *  All tracking & popularity engines are UNCHANGED from v1 (so production
 *  signals continue flowing exactly the same way).
 *
 *  REWRITTEN ONLY:
 *    - getPersonalizedRecommendations()  → delegates to FeedBrain
 *    - getFeaturedProperties()           → delegates to FeedBrain
 *    - bustFeaturedCache()               → also invalidates taste profile
 *
 *  REMOVED (their work now lives in FeedBrain / UserTasteProfile — all were
 *  PRIVATE so removing them breaks nothing externally):
 *    - resolveUserContext, getContextualFeatured, getGlobalFeaturedFallback,
 *      resolveFeaturedReason, getFilterMatchedRecommendations,
 *      getBudgetMatchedRecommendations, getGeneralRecommendations
 */
class PropertyInteractionService
{
    /**
     * The brain & profile are auto-resolved by Laravel's container.
     * No service provider binding needed — both are concrete classes.
     */
    public function __construct(
        private UserTasteProfile $profiles,
        private FeedBrain $brain,
    ) {}

    // ══════════════════════════════════════════════════════════════════════════
    //  TRACK VIEW  (UNCHANGED)
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
    //  TRACK SEARCH CLICK  (UNCHANGED)
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearchClick(
        string $userId,
        string $propertyId,
        string $searchQuery    = '',
        int    $resultPosition = 0,
        array  $activeFilters  = []
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
    //  TRACK SEARCH IMPRESSIONS  (UNCHANGED)
    // ══════════════════════════════════════════════════════════════════════════
    public function trackSearchImpressions(
        string $userId,
        array  $propertyIds,
        string $searchQuery    = '',
        array  $activeFilters  = []
    ): void {
        try {
            if (empty($propertyIds)) return;
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
    //  CALCULATOR SIGNAL  (UNCHANGED behavior + 1 new cache key for v2)
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

            // ── Calculator is a strong signal — invalidate so the next feed
            //    reflects the new budget immediately (not in 15 min).
            Cache::forget("personalized_recs_{$userId}");
            $this->bustPersonalizedCacheForUser($userId);
            $this->profiles->invalidate($userId);
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
    //  FILTER SIGNAL  (UNCHANGED)
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
    //  COMPARE SIGNAL  (UNCHANGED)
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
    //  SEARCH SIGNAL  (UNCHANGED)
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
    //  POPULARITY ENGINE  (UNCHANGED — was already solid)
    // ══════════════════════════════════════════════════════════════════════════
    public function computePopularityScores(
        ?string $listingType = null,
        ?string $city        = null,
        int     $days        = 30,
        int     $limit       = 50
    ): Collection {
        $cacheKey = "popularity_scores_{$listingType}_{$city}_{$days}_{$limit}";
        return Cache::remember($cacheKey, 600, function () use ($listingType, $city, $days, $limit) {

            $virtualIds = ['calculator_signal', 'filter_signal', 'search_signal', 'search_signal_latest'];

            $searchClicks = DB::table('user_property_interactions')
                ->select('property_id', DB::raw('COUNT(*) as click_count'))
                ->where('interaction_type', 'search_click')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereNotIn('property_id', $virtualIds)
                ->groupBy('property_id')
                ->pluck('click_count', 'property_id');

            $searchImpressions = DB::table('user_property_interactions')
                ->select('property_id', DB::raw('COUNT(*) as impression_count'))
                ->where('interaction_type', 'search_impression')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereNotIn('property_id', $virtualIds)
                ->groupBy('property_id')
                ->pluck('impression_count', 'property_id');

            $compareCounts = DB::table('user_property_interactions')
                ->select('property_id', DB::raw('COUNT(*) as compare_count'))
                ->where('interaction_type', 'compare')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereNotIn('property_id', $virtualIds)
                ->groupBy('property_id')
                ->pluck('compare_count', 'property_id');

            $velocityData = DB::table('user_property_interactions')
                ->select('property_id', DB::raw('COUNT(*) as recent_count'))
                ->whereIn('interaction_type', ['view', 'search_click', 'favorite', 'compare'])
                ->where('created_at', '>=', now()->subHours(48))
                ->whereNotIn('property_id', $virtualIds)
                ->groupBy('property_id')
                ->pluck('recent_count', 'property_id');

            $query = Property::query()
                ->where('is_active', true)
                ->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented']);

            if ($listingType) $query->where('listing_type', $listingType);
            if ($city) {
                $query->whereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                    [strtolower($city)]
                );
            }

            $properties = $query->get();

            return $properties->map(function ($property) use (
                $searchClicks,
                $searchImpressions,
                $compareCounts,
                $velocityData
            ) {
                $pid         = $property->id;
                $clicks      = (int) ($searchClicks[$pid]      ?? 0);
                $impressions = (int) ($searchImpressions[$pid] ?? 0);
                $compares    = (int) ($compareCounts[$pid]     ?? 0);
                $velocity    = (int) ($velocityData[$pid]      ?? 0);

                $ctr      = $impressions > 0 ? ($clicks / $impressions) : 0;
                $ctrScore = min($ctr * 100, 20);
                $clickScore   = $clicks   > 0 ? min(log($clicks + 1, 2) * 5, 35)   : 0;
                $compareScore = $compares > 0 ? min(log($compares + 1, 2) * 4, 20)  : 0;
                $favScore     = min($property->favorites_count * 0.5, 15);
                $viewScore    = min(log(($property->views ?? 0) + 1, 10) * 2, 5);
                $ratingScore  = ($property->rating ?? 0) * 1;
                $velocityScore = $velocity > 0 ? min(log($velocity + 1, 2) * 3, 15) : 0;

                $daysSince    = $property->created_at->diffInDays(now());
                $recencyScore = match (true) {
                    $daysSince <= 3  => 10,
                    $daysSince <= 7  => 7,
                    $daysSince <= 14 => 4,
                    $daysSince <= 30 => 2,
                    default          => 0,
                };
                $verifiedScore = $property->verified ? 5 : 0;

                $totalScore = $clickScore + $ctrScore + $compareScore + $favScore
                    + $viewScore + $ratingScore + $velocityScore
                    + $recencyScore + $verifiedScore;

                $property->popularity_score = round($totalScore, 2);
                $property->popularity_breakdown = [
                    'search_clicks' => $clicks,
                    'search_ctr'    => round($ctr * 100, 1) . '%',
                    'compare_count' => $compares,
                    'velocity_48h'  => $velocity,
                    'scores'        => [
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
    //  FEATURED — REWRITTEN to use the brain
    //  -------------------------------------
    //  Layer 1 (40% of slots): boosted listings, ordered by listing-type match
    //                          to the user, then by recent boost activity.
    //                          Boosted properties pay for placement — they
    //                          always appear regardless of taste profile.
    //
    //  Layer 2 (60% of slots): brain-ranked non-boosted candidates.
    //                          Same FeedBrain that drives Personalized & Map,
    //                          so the "why" labels are consistent everywhere.
    //
    //  Daily salt rotates the explore/exploit jitter so featured doesn't show
    //  the exact same 10 cards every load.
    // ══════════════════════════════════════════════════════════════════════════
    public function getFeaturedProperties(int $limit = 10, ?string $userId = null): Collection
    {
        try {
            $profile = $userId ? $this->profiles->build($userId) : $this->emptyProfile();
            $salt    = (int) now()->format('Ymd');

            // ── Layer 1: paid boosted slots ──────────────────────────────────
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

            // Prefer boosted listings matching the user's listing_type (sell/rent)
            if (!empty($profile['listing_type'])) {
                $boostedQuery->orderByRaw(
                    "CASE WHEN listing_type = ? THEN 1 ELSE 2 END ASC",
                    [$profile['listing_type']]
                );
            }

            $boosted = $boostedQuery
                ->orderByDesc('boost_start_date')
                ->limit($boostedLimit)
                ->get();

            // Score boosted properties too so frontend sees consistent reasons
            $boosted = $boosted->map(function ($p) use ($profile) {
                $r = $this->brain->scoreProperty($p, $profile);
                $p->feed_score    = $r['score'];
                $p->feed_reasons  = $r['reasons'];
                $p->featured_layer = 1;
                $p->relevance_score = $r['relevance'];
                return $p;
            });

            // ── Layer 2: brain-ranked organic contextual quality ─────────────
            $boostedIds = $boosted->pluck('id')->toArray();
            $excludeIds = array_merge($boostedIds, $profile['seen_ids'] ?? []);
            $remaining  = $limit - $boosted->count();

            if ($remaining > 0) {
                // Pull a generous pool — brain does the precision work
                $poolSize = $remaining * 5;
                $pool = Property::query()
                    ->where('is_active', true)
                    ->where('published', true)
                    ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                    ->whereNotIn('id', $excludeIds ?: ['__none__'])
                    ->orderByDesc('created_at')
                    ->limit($poolSize)
                    ->get();

                $contextual = $this->brain->rank($pool, $profile, $remaining, $salt)
                    ->map(function ($p) {
                        $p->featured_layer = 2;
                        return $p;
                    });
            } else {
                $contextual = collect();
            }

            $merged = $boosted->merge($contextual)->values();

            Log::info('⭐ FEATURED v2', [
                'user_id'        => $userId,
                'has_profile'    => $profile['has_history'] ?? false,
                'intent_score'   => $profile['intent_score'] ?? 0,
                'boosted_count'  => $boosted->count(),
                'organic_count'  => $contextual->count(),
                'total_returned' => $merged->count(),
            ]);

            return $merged;
        } catch (\Throwable $e) {
            Log::error('Featured v2 error', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
            ]);
            // Production safety: if anything fails, fall back to simple boosted+new
            return $this->safeFallback($limit);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  PERSONALIZED RECOMMENDATIONS — REWRITTEN to use the brain
    //  ---------------------------------------------------------
    //  The flow:
    //    1. Build the user's taste profile (recency-weighted across all signals)
    //    2. Fetch a wide candidate pool (tight match first, then loose)
    //    3. Brain ranks with intent-adaptive weights + explore/exploit jitter
    //    4. If somehow short, top up with general fallback
    //
    //  Daily salt means the same user sees a slightly reshuffled feed each day,
    //  preventing "I've seen these 20 already" fatigue while keeping ranking
    //  stable within a single session.
    // ══════════════════════════════════════════════════════════════════════════
    public function getPersonalizedRecommendations(string $userId, int $limit = 20): Collection
    {
        $cacheKey = "personalized_recs_v2_{$userId}_{$limit}";

        return Cache::remember($cacheKey, 600, function () use ($userId, $limit) {
            try {
                $profile = $this->profiles->build($userId);
                $salt    = (int) now()->format('Ymd');

                Log::info('🎯 REC v2 start', [
                    'user_id'       => $userId,
                    'has_history'   => $profile['has_history'],
                    'intent_score'  => $profile['intent_score'],
                    'top_city'      => array_key_first($profile['cities']),
                    'top_type'      => array_key_first($profile['types']),
                    'listing_type'  => $profile['listing_type'],
                    'price_target'  => $profile['price']['target'] ?? null,
                    'bedrooms'      => $profile['bedrooms'],
                    'signal_counts' => $profile['signal_counts'],
                ]);

                // No history → general fallback (still smart: boosted+new+popular)
                if (!$profile['has_history']) {
                    return $this->generalFallback($limit);
                }

                // Pull a wide candidate pool
                $candidates = $this->fetchCandidates($profile, $limit * 4);

                if ($candidates->isEmpty()) {
                    Log::info('🎯 REC v2: empty candidates, falling back', ['user_id' => $userId]);
                    return $this->generalFallback($limit);
                }

                // The brain ranks with intent-adaptive weights
                $ranked = $this->brain->rank($candidates, $profile, $limit, $salt);

                // Top up if brain didn't fill the limit (rare — only if pool too small)
                if ($ranked->count() < $limit) {
                    $needed     = $limit - $ranked->count();
                    $existingIds = array_merge(
                        $ranked->pluck('id')->toArray(),
                        $profile['seen_ids']
                    );
                    $topup = $this->generalFallback($needed + 5)
                        ->filter(fn($p) => !in_array($p->id, $existingIds))
                        ->take($needed)
                        ->map(function ($p) use ($profile) {
                            // Score these too so frontend gets consistent labels
                            $r = $this->brain->scoreProperty($p, $profile);
                            $p->feed_score   = $r['score'];
                            $p->feed_reasons = $r['reasons'];
                            return $p;
                        });
                    $ranked = $ranked->merge($topup);
                }

                Log::info('🎯 REC v2 done', [
                    'user_id'        => $userId,
                    'returned'       => $ranked->count(),
                    'avg_score'      => round($ranked->avg('feed_score') ?? 0, 1),
                    'top_score'      => $ranked->max('feed_score'),
                ]);

                return $ranked->values();
            } catch (\Throwable $e) {
                Log::error('🎯 REC v2 error', [
                    'user_id' => $userId,
                    'error'   => $e->getMessage(),
                    'line'    => $e->getLine(),
                ]);
                // Production safety
                return $this->safeFallback($limit);
            }
        });
    }

    // ── Candidate selection (NEW, private) ───────────────────────────────────
    //
    //  Strategy: tight-then-loose. First query pulls properties matching the
    //  user's PREFERRED city + listing_type (their strongest signals). If
    //  that doesn't fill the pool, the second query relaxes the city filter.
    //  Brain then scores everything and ranks.
    //
    //  Soft cap on price (2× user's max) prevents 5x-over-budget listings
    //  from clogging the candidate pool. Brain still scores within that.
    //
    private function fetchCandidates(array $profile, int $poolSize): Collection
    {
        // Tight pass: city + listing match
        $tight = $this->baseCandidateQuery($profile)
            ->when($profile['listing_type'], fn($q) => $q->where('listing_type', $profile['listing_type']))
            ->when(!empty($profile['cities']), function ($q) use ($profile) {
                $cities = array_map('strtolower', array_keys($profile['cities']));
                $q->where(function ($sub) use ($cities) {
                    foreach ($cities as $c) {
                        $sub->orWhereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                            [$c]
                        );
                    }
                });
            })
            ->orderByDesc('created_at')
            ->limit($poolSize)
            ->get();

        // If tight pool is big enough, just return it
        if ($tight->count() >= $poolSize * 0.6) {
            return $tight;
        }

        // Loose pass: drop city, keep listing_type
        $existingIds = $tight->pluck('id')->toArray();
        $loose = $this->baseCandidateQuery($profile)
            ->whereNotIn('id', $existingIds ?: ['__none__'])
            ->when($profile['listing_type'], fn($q) => $q->where('listing_type', $profile['listing_type']))
            ->orderByDesc('created_at')
            ->limit($poolSize - $tight->count())
            ->get();

        return $tight->merge($loose);
    }

    private function baseCandidateQuery(array $profile)
    {
        $excludeIds = $profile['seen_ids'] ?: ['__none__'];

        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $excludeIds);

        // Soft price ceiling: 2× their max keeps obviously-out-of-reach
        // listings out of the pool. Brain handles precise band scoring.
        if (!empty($profile['price']['max'])) {
            $hardMax = $profile['price']['max'] * 2.0;
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) <= ?",
                [$hardMax]
            );
        }

        return $query;
    }

    // ── General fallback (NEW, private) ──────────────────────────────────────
    //
    //  No personalization signal available — still produce a quality feed:
    //  boosted first, then verified + popular + fresh, with proper engagement
    //  weighting. This is what guests and brand-new users see.
    //
    private function generalFallback(int $limit): Collection
    {
        return Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->selectRaw('*, (
                (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                (CASE WHEN verified   = 1 THEN 20 ELSE 0 END) +
                (LEAST(favorites_count, 50) * 0.8) +
                (LEAST(views, 200) * 0.1) +
                (rating * 5) +
                (CASE
                    WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 20
                    WHEN DATEDIFF(NOW(), created_at) <= 14 THEN 15
                    WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10
                    ELSE 0
                END)
            ) as fallback_score')
            ->orderByDesc('fallback_score')
            ->limit($limit)
            ->get();
    }

    // ── Absolute safety net (NEW, private) ───────────────────────────────────
    // Used only inside catch blocks — must NEVER throw.
    private function safeFallback(int $limit): Collection
    {
        try {
            return Property::where('is_active', true)
                ->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                ->orderByDesc('is_boosted')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function emptyProfile(): array
    {
        return [
            'has_history'   => false,
            'intent_score'  => 0,
            'cities'        => [],
            'types'         => [],
            'listing_type'  => null,
            'price'         => null,
            'bedrooms'      => null,
            'seen_ids'      => [],
            'budget'        => null,
            'signal_counts' => [],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  IMPRESSIONS TRACKER  (UNCHANGED)
    // ══════════════════════════════════════════════════════════════════════════
    public function trackImpressions(string $userId, $properties, string $sourceEndpoint, array $extra = []): void
    {
        if (
            empty($properties) ||
            (is_object($properties) && method_exists($properties, 'isEmpty') && $properties->isEmpty())
        )
            return;
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
    //  CACHE BUSTING  (UNCHANGED + added profile & v2 keys)
    // ══════════════════════════════════════════════════════════════════════════
    public function bustPopularityCache(?string $listingType = null, ?string $city = null): void
    {
        $patterns = [
            "popularity_scores_{$listingType}_{$city}_30_150",
            "popularity_scores_{$listingType}_{$city}_30_100",
            "popularity_scores_{$listingType}_{$city}_30_60",
            'popular_properties_global',
        ];
        foreach ($patterns as $key) Cache::forget($key);
    }

    public function bustFeaturedCache(?string $userId = null): void
    {
        if ($userId) {
            // ── v1 keys (preserved) ──
            Cache::forget("featured_contextual_{$userId}");
            // ── v2 keys & taste profile ──
            $this->bustPersonalizedCacheForUser($userId);
            $this->profiles->invalidate($userId);
        }

        // Guest featured caches
        foreach ([5, 10, 20] as $limit) {
            Cache::forget("featured_guest_{$limit}");
            if ($userId) Cache::forget("featured_user_{$userId}_{$limit}");
        }

        // Legacy featured strategy caches (kept for safety)
        foreach (['balanced', 'premium', 'engagement', 'recent', 'advanced'] as $strategy) {
            foreach ([5, 10, 20, 50] as $limit) {
                Cache::forget("featured_properties_{$strategy}_{$limit}");
            }
        }
    }

    /**
     * Invalidate all known personalized_recs_v2 cache keys for a user.
     * Called from any signal change that should affect the next /recommended.
     */
    private function bustPersonalizedCacheForUser(string $userId): void
    {
        foreach ([5, 10, 14, 20, 30, 40, 50] as $limit) {
            Cache::forget("personalized_recs_v2_{$userId}_{$limit}");
        }
    }
}
