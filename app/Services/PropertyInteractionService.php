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
    //  TRACK SEARCH IMPRESSIONS
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
    //  SEARCH SIGNAL
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
    //  POPULARITY ENGINE
    // ══════════════════════════════════════════════════════════════════════════
    // ══════════════════════════════════════════════════════════════════════════
    //  POPULARITY ENGINE
    // ══════════════════════════════════════════════════════════════════════════
    public function computePopularityScores(
        ?string $listingType = null,
        ?string $city        = null,
        int     $days        = 30,
        int     $limit       = 50
    ): Collection {
        // ── KEY FIX: $limit excluded from cache key so featured + popular share one entry
        $cacheKey = "popularity_scores_{$listingType}_{$city}_{$days}";

        $all = Cache::remember($cacheKey, 3600, function () use ($listingType, $city, $days) {

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

                $ctr           = $impressions > 0 ? ($clicks / $impressions) : 0;
                $ctrScore      = min($ctr * 100, 20);
                $clickScore    = $clicks   > 0 ? min(log($clicks + 1, 2) * 5, 35)  : 0;
                $compareScore  = $compares > 0 ? min(log($compares + 1, 2) * 4, 20) : 0;
                $favScore      = min($property->favorites_count * 0.5, 15);
                $viewScore     = min(log(($property->views ?? 0) + 1, 10) * 2, 5);
                $ratingScore   = ($property->rating ?? 0) * 1;
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

        // Apply limit AFTER cache — so featured(limit=50) and popular(limit=20)
        // both hit the same Redis entry instead of running separate DB queries
        return $all->take($limit);
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
    //  FEATURED ENGINE (2-layer)
    // ══════════════════════════════════════════════════════════════════════════
    public function getFeaturedProperties(
        int     $limit  = 10,
        ?string $userId = null
    ): Collection {
        $context      = $this->resolveUserContext($userId);
        $boostedLimit = (int) ceil($limit * 0.40);

        $boostedQuery = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('is_boosted', true)
            ->where('boost_start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('boost_end_date')->orWhere('boost_end_date', '>=', now());
            });

        if ($context['listing_type']) {
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
            ->selectRaw("*, (
                (CASE WHEN verified = 1 THEN 15 ELSE 0 END) +
                (LEAST(favorites_count, 50) * 0.5) +
                (rating * 5) +
                (CASE
                    WHEN DATEDIFF(NOW(), boost_start_date) <= 1  THEN 15
                    WHEN DATEDIFF(NOW(), boost_start_date) <= 7  THEN 10
                    WHEN DATEDIFF(NOW(), boost_start_date) <= 30 THEN 5
                    ELSE 0
                END)
            ) as layer1_score")
            ->orderByDesc('layer1_score')
            ->limit($boostedLimit)
            ->get();

        $boostedIds        = $boostedProperties->pluck('id')->toArray();
        $contextualLimit   = $limit - $boostedProperties->count();

        $contextualProperties = $this->getContextualFeatured(
            limit: $contextualLimit,
            context: $context,
            excludeIds: $boostedIds,
            userId: $userId
        );

        $merged = $boostedProperties->merge($contextualProperties);
        return $merged->map(function ($property) use ($boostedIds) {
            $property->featured_layer  = in_array($property->id, $boostedIds) ? 1 : 2;
            $property->featured_reason = $this->resolveFeaturedReason($property);
            return $property;
        })->values();
    }

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
            $filterSignal = $this->getFilterSignal($userId);
            if ($filterSignal) {
                $context['city']          = $filterSignal['city']          ?? null;
                $context['listing_type']  = $filterSignal['listing_type']  ?? null;
                $context['property_type'] = $filterSignal['property_type'] ?? null;
                $context['max_price']     = $filterSignal['max_price_usd'] ?? null;
                $context['min_price']     = $filterSignal['min_price_usd'] ?? null;
                $context['bedrooms']      = $filterSignal['bedrooms']      ?? null;
            }

            $searchSignal = $this->getLatestSearchSignal($userId);
            if ($searchSignal) {
                $filters = $searchSignal['active_filters'] ?? [];
                if (!$context['listing_type'] && !empty($filters['listing_type']))
                    $context['listing_type'] = $filters['listing_type'];
                if (!$context['city'] && !empty($filters['city']))
                    $context['city'] = $filters['city'];
            }

            $calcSignal = $this->getCalculatorSignal($userId);
            if ($calcSignal && !$context['max_price']) {
                $context['max_price'] = $calcSignal['budget_max_usd'] ?? null;
                $context['min_price'] = $calcSignal['budget_min_usd'] ?? null;
            }

            if (!$context['city'] || !$context['listing_type']) {
                $virtualIds = ['calculator_signal', 'filter_signal', 'search_signal', 'search_signal_latest'];
                $recentIds  = DB::table('user_property_interactions')
                    ->where('user_id', $userId)
                    ->whereIn('interaction_type', ['view', 'favorite'])
                    ->where('created_at', '>=', now()->subDays(30))
                    ->whereNotIn('property_id', $virtualIds)
                    ->pluck('property_id')->unique()->take(20);

                if ($recentIds->isNotEmpty()) {
                    $recentProps = Property::whereIn('id', $recentIds)
                        ->select('id', 'address_details', 'listing_type')->get();

                    if (!$context['city']) {
                        $cityMode = $recentProps
                            ->map(fn($p) => $p->address_details['city']['en'] ?? null)
                            ->filter()->mode();
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

    private function getContextualFeatured(
        int     $limit,
        array   $context,
        array   $excludeIds,
        ?string $userId
    ): Collection {
        $popularityPool = $this->computePopularityScores(
            listingType: $context['listing_type'],
            city: null,
            days: 30,
            limit: $limit * 5
        );

        $scored = $popularityPool
            ->filter(fn($p) => !in_array($p->id, $excludeIds))
            ->filter(fn($p) => !in_array($p->status, ['sold', 'rented', 'cancelled', 'pending']))
            ->map(function ($property) use ($context) {
                $relevanceScore = 0;

                if ($context['city']) {
                    $propCity = strtolower($property->address_details['city']['en'] ?? '');
                    if ($propCity === strtolower($context['city'])) $relevanceScore += 30;
                }
                if ($context['listing_type'] && $property->listing_type === $context['listing_type'])
                    $relevanceScore += 25;
                if ($context['property_type']) {
                    $propType = strtolower($property->type['category'] ?? '');
                    if ($propType === strtolower($context['property_type'])) $relevanceScore += 15;
                }
                $propPrice = $property->price['usd'] ?? 0;
                if ($context['min_price'] && $context['max_price'] && $propPrice > 0) {
                    if ($propPrice >= $context['min_price'] && $propPrice <= $context['max_price'])
                        $relevanceScore += 20;
                    elseif ($propPrice <= $context['max_price'])
                        $relevanceScore += 10;
                }
                if ($context['bedrooms']) {
                    $propBeds = (int) ($property->rooms['bedroom']['count'] ?? 0);
                    if ($propBeds === (int) $context['bedrooms']) $relevanceScore += 10;
                }

                $property->layer2_score    = ($property->popularity_score ?? 0) + $relevanceScore;
                $property->relevance_score = $relevanceScore;
                return $property;
            })
            ->sortByDesc('layer2_score')
            ->values();

        $selected  = collect();
        $cityCount = [];
        $typeCount = [];
        $cityMax   = max(2, (int) ceil($limit * 0.35));
        $typeMax   = max(2, (int) ceil($limit * 0.45));

        foreach ($scored as $property) {
            if ($selected->count() >= $limit) break;
            $city = strtolower($property->address_details['city']['en'] ?? 'unknown');
            $type = strtolower($property->type['category']              ?? 'unknown');
            $cityCount[$city] = $cityCount[$city] ?? 0;
            $typeCount[$type] = $typeCount[$type] ?? 0;
            if ($cityCount[$city] >= $cityMax || $typeCount[$type] >= $typeMax) continue;
            $selected->push($property);
            $cityCount[$city]++;
            $typeCount[$type]++;
        }

        if ($selected->count() < $limit) {
            $remaining = $scored->whereNotIn('id', $selected->pluck('id')->toArray())
                ->take($limit - $selected->count());
            $selected = $selected->merge($remaining);
        }

        if ($selected->count() < $limit) {
            $fallback = $this->getGlobalFeaturedFallback(
                $limit - $selected->count(),
                array_merge($excludeIds, $selected->pluck('id')->toArray())
            );
            $selected = $selected->merge($fallback);
        }

        return $selected->values();
    }

    private function getGlobalFeaturedFallback(int $limit, array $excludeIds): Collection
    {
        return Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $excludeIds)
            ->selectRaw("*, (
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
            ) as fallback_score")
            ->orderByDesc('fallback_score')
            ->limit($limit)
            ->get();
    }

    private function resolveFeaturedReason(Property $property): array
    {
        $reasons = [];
        if ($property->is_boosted)
            $reasons[] = ['key' => 'promoted',    'label' => 'Promoted listing'];
        if ($property->verified)
            $reasons[] = ['key' => 'verified',    'label' => 'Verified property'];
        if (($property->popularity_breakdown['scores']['velocity_score'] ?? 0) > 8)
            $reasons[] = ['key' => 'trending',    'label' => 'Trending now'];
        if (($property->popularity_breakdown['scores']['click_score'] ?? 0) > 15)
            $reasons[] = ['key' => 'high_demand', 'label' => 'High search demand'];
        if (($property->popularity_breakdown['scores']['ctr_score'] ?? 0) > 10)
            $reasons[] = ['key' => 'popular',     'label' => 'Frequently chosen from search'];
        if (($property->relevance_score ?? 0) >= 30)
            $reasons[] = ['key' => 'relevant',    'label' => 'Matches your preferences'];
        if ($property->created_at->diffInDays(now()) <= 7)
            $reasons[] = ['key' => 'new',         'label' => 'New listing'];
        if ($property->favorites_count > 10)
            $reasons[] = ['key' => 'saved',       'label' => 'Frequently saved'];
        if (($property->popularity_breakdown['compare_count'] ?? 0) > 5)
            $reasons[] = ['key' => 'compared',    'label' => 'Often compared by buyers'];
        return $reasons ?: [['key' => 'quality', 'label' => 'Top quality listing']];
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  PERSONALIZED RECOMMENDATIONS (UserTasteProfile-backed)
    //  All inference now lives in App\Services\Intelligence\UserTasteProfile.
    //  This method only reads from the profile and runs the SQL.
    // ══════════════════════════════════════════════════════════════════════════
    public function getPersonalizedRecommendations(string $userId, int $limit = 20): Collection
    {
        $user = User::find($userId);
        if (!$user) return $this->getGeneralRecommendations($limit);

        $profile = app(\App\Services\Intelligence\UserTasteProfile::class)->build($userId);

        Log::info('🎯 REC: profile loaded', [
            'user_id'        => $userId,
            'has_history'    => $profile['has_history'],
            'is_cold_start'  => $profile['is_cold_start'],
            'intent_score'   => $profile['intent_score'],
            'cities'         => $profile['cities'],
            'types'          => $profile['types'],
            'listing_type'   => $profile['listing_type'],
            'price'          => $profile['price'],
            'bedrooms'       => $profile['bedrooms'],
            'has_centroid'   => $profile['heat_centroid'] !== null,
            'seen_count'     => count($profile['seen_ids']),
            'signal_counts'  => $profile['signal_counts'],
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

        $types       = array_keys($profile['types']);
        $cities      = array_keys($profile['cities']);
        $listingType = $profile['listing_type'];
        $priceMin    = $profile['price']['min'] ?? null;
        $priceMax    = $profile['price']['max'] ?? null;
        $bedrooms    = $profile['bedrooms'];
        $heat        = $profile['heat_centroid'];
        $seenIds     = $profile['seen_ids'];

        $query = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $seenIds);

        // Types — FIXED: use bindings, not string interpolation
        if (!empty($types)) {
            $query->where(function ($q) use ($types) {
                foreach ($types as $type) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                        [strtolower($type)]
                    );
                }
            });
        }
        if ($listingType) {
            $query->where('listing_type', $listingType);
        }
        if ($priceMin && $priceMax) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) BETWEEN ? AND ?",
                [$priceMin, $priceMax]
            );
        }
        if ($bedrooms !== null) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) AS UNSIGNED) = ?",
                [$bedrooms]
            );
        }
        if (!empty($cities)) {
            $query->where(function ($q) use ($cities) {
                foreach ($cities as $city) {
                    $q->orWhereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                        [strtolower($city)]
                    );
                }
            });
        }

        // Heat-centroid bonus — FIXED: use bindings instead of inlining floats
        $scoreSelectSql = "*, (
        (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
        (CASE WHEN verified   = 1 THEN 20 ELSE 0 END) +
        (LEAST(views, 100) * 0.15) +
        (LEAST(favorites_count, 50) * 0.8) +
        (rating * 5) +
        (CASE WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 15
              WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10 ELSE 0 END)";

        if ($heat) {
            // Floats are sanitized to ensure no SQL injection (they're computed from
            // our own DB values, but we cast explicitly to be defensive).
            $cLat = (float) $heat['lat'];
            $cLng = (float) $heat['lng'];
            $r    = (float) $heat['radius_km'];

            $scoreSelectSql .= " + (CASE WHEN (6371 * acos(LEAST(1, "
                . "cos(radians({$cLat})) * "
                . "cos(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lat')) AS DECIMAL(10,6)))) * "
                . "cos(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lng')) AS DECIMAL(10,6))) - radians({$cLng})) + "
                . "sin(radians({$cLat})) * "
                . "sin(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lat')) AS DECIMAL(10,6)))) "
                . "))) <= {$r} THEN 25 ELSE 0 END)";
        }

        $scoreSelectSql .= ") as recommendation_score";

        $results = $query
            ->selectRaw($scoreSelectSql)
            ->orderByDesc('recommendation_score')
            ->limit($limit * 2)
            ->get();

        Log::info('🎯 REC: query results', [
            'user_id' => $userId,
            'found'   => $results->count(),
            'needed'  => $limit,
        ]);

        // Fallback to relaxed + general if too few
        if ($results->count() < $limit) {
            $needed      = $limit - $results->count();
            $existingIds = array_merge($seenIds, $results->pluck('id')->toArray());

            $relaxed = Property::query()
                ->where('is_active', true)->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                ->whereNotIn('id', $existingIds)
                ->when($listingType, fn($q) => $q->where('listing_type', $listingType))
                ->when(!empty($types), function ($q) use ($types) {
                    $q->where(function ($q2) use ($types) {
                        foreach ($types as $type) {
                            $q2->orWhereRaw(
                                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                                [strtolower($type)]
                            );
                        }
                    });
                })
                ->orderByDesc('created_at')
                ->limit($needed)
                ->get();

            $results     = $results->merge($relaxed);
            $existingIds = array_merge($existingIds, $relaxed->pluck('id')->toArray());

            if ($results->count() < $limit) {
                $needed2 = $limit - $results->count();
                $general = $this->getGeneralRecommendations($needed2 + 5)
                    ->filter(fn($p) => !in_array($p->id, $existingIds))
                    ->take($needed2);
                $results = $results->merge($general);
            }
        }

        Log::info('🎯 REC: final result', [
            'user_id'        => $userId,
            'total_returned' => $results->count(),
        ]);

        return $results->values()->take($limit);
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

        if (!empty($filterSignal['listing_type']))
            $query->where('listing_type', $filterSignal['listing_type']);
        if (!empty($filterSignal['property_type']))
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                [strtolower($filterSignal['property_type'])]
            );
        if (!empty($filterSignal['city']))
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                [strtolower($filterSignal['city'])]
            );
        if (!empty($filterSignal['max_price_usd']))
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) <= ?",
                [(float) $filterSignal['max_price_usd']]
            );
        if (!empty($filterSignal['min_price_usd']))
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) >= ?",
                [(float) $filterSignal['min_price_usd']]
            );
        if (!empty($filterSignal['bedrooms']))
            $query->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) = ?",
                [(int) $filterSignal['bedrooms']]
            );
        if (!empty($filterSignal['furnished']))
            $query->where('furnished', true);

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
                (CASE WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 15
                      WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10 ELSE 0 END)
            ) as recommendation_score')
            ->orderByDesc('recommendation_score')
            ->limit($limit)->get();

        if ($results->count() < $limit) {
            $needed  = $limit - $results->count();
            $general = $this->getGeneralRecommendations($needed + 5)
                ->filter(fn($p) => !in_array($p->id, array_merge($excludeIds, $results->pluck('id')->toArray())))
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
    //  IMPRESSIONS TRACKER
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
    //  CACHE BUSTING
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
        if ($userId) Cache::forget("featured_contextual_{$userId}");
        foreach (['balanced', 'premium', 'engagement', 'recent', 'advanced'] as $strategy) {
            foreach ([5, 10, 20, 50] as $limit) {
                Cache::forget("featured_properties_{$strategy}_{$limit}");
            }
        }
    }
    public function trackContactIntent(
        string  $userId,
        string  $propertyId,
        string  $method      = 'whatsapp',
        ?string $propertyType = null,
        ?string $city         = null,
        ?float  $priceUsd     = null,
    ): void {
        try {
            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'property_id'      => $propertyId,
                'interaction_type' => 'contact_intent',
                'metadata'         => [
                    'contact_method' => $method,
                    'property_type'  => $propertyType,
                    'city'           => $city,
                    'price_usd'      => $priceUsd,
                    'weight'         => 6.0, // matches SIGNAL_WEIGHTS in UserTasteProfile
                    'timestamp'      => now()->toISOString(),
                ],
                'created_at' => now(),
            ]);
            // Bust taste profile cache so next recommendation reflects this signal
            Cache::forget("taste_profile_{$userId}");
        } catch (\Throwable $e) {
            Log::warning('trackContactIntent failed', ['error' => $e->getMessage()]);
        }
    }
}
