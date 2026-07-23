<?php

namespace App\Services\Concerns;

use App\Models\Property;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Scores properties by search CTR, clicks, compares, recent-activity
 * velocity, favorites, rating, recency, and verification — the "popular"
 * ranking used by both the popular-properties endpoint and (as an input
 * pool) the featured-properties engine. Extracted from
 * PropertyInteractionService.php as-is — no behavior changed, only
 * relocated. See TracksUserSignals.php for why this split is safe.
 */
trait ComputesPropertyPopularity
{
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
}
