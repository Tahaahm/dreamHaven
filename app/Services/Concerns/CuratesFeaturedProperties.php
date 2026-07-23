<?php

namespace App\Services\Concerns;

use App\Models\Property;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Two-layer "featured properties" curation: layer 1 is actively-boosted
 * listings, layer 2 fills the rest from the popularity pool re-scored for
 * relevance to the user's inferred city/type/price/bedroom context, with
 * diversity caps and a global fallback if there aren't enough matches.
 * Extracted from PropertyInteractionService.php as-is — no behavior
 * changed, only relocated. See TracksUserSignals.php for why this split
 * is safe. Depends on $this->getFilterSignal() / getLatestSearchSignal() /
 * getCalculatorSignal() / computePopularityScores(), defined in the other
 * traits composed into PropertyInteractionService.
 */
trait CuratesFeaturedProperties
{
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

    public function bustFeaturedCache(?string $userId = null): void
    {
        if ($userId) Cache::forget("featured_contextual_{$userId}");
        foreach (['balanced', 'premium', 'engagement', 'recent', 'advanced'] as $strategy) {
            foreach ([5, 10, 20, 50] as $limit) {
                Cache::forget("featured_properties_{$strategy}_{$limit}");
            }
        }
    }
}
