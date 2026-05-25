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
    //  TRACK VIEW  (unchanged)
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
        $ids = array_slice($user->recently_viewed_properties, 0, $limit);
        $props = Property::whereIn('id', $ids)
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending'])->get();
        return $props->sortBy(fn($p) => array_search($p->id, $ids))->values();
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  CALCULATOR SIGNAL  (unchanged)
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
    //  NEW: READ FILTER SIGNAL
    //  Returns the last filter the user applied (max 60 days old).
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
    //  NEW: READ COMPARE SIGNAL
    //  Returns properties the user compared (last 30 days).
    //  Compare = near-decision intent → 4× weight (just below favorite 5×).
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
    //  NEW: READ SEARCH SIGNAL (latest query)
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
        $calcSignal    = $this->getCalculatorSignal($userId);
        $filterSignal  = $this->getFilterSignal($userId);
        $searchSignal  = $this->getLatestSearchSignal($userId);
        $comparedData  = $this->getComparedProperties($userId);

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

        // Exclude everything already interacted with
        $allInteractedIds = array_unique(array_merge($viewedIds, $favoritedIds, $comparedIds));

        $hasBrowseHistory = !empty($allInteractedIds);

        // Budget signal
        $hasBudget      = $calcSignal !== null && ($calcSignal['budget_min_usd'] ?? 0) > 0;
        $signalStrength  = (int)($calcSignal['signal_strength'] ?? 0);
        $budgetWeight    = $hasBudget ? ($signalStrength / 100) : 0.0;

        // ── 3. No history at all ──────────────────────────────────────────────
        if (!$hasBrowseHistory && $comparedData->isEmpty()) {
            // Filter signal alone is enough to personalize
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
        // Compare data already loaded as $comparedData

        // ── Property type (favorites 5×, compared 4×, viewed 1×) ─────────────
        $preferredTypes = collect()
            ->merge($viewedData->pluck('type')->map(fn($t) => $t['category'] ?? null))
            ->merge($favoritedData->pluck('type')->map(fn($t) => $t['category'] ?? null)->replicate(5))
            ->merge($comparedData->pluck('type')->map(fn($t) => $t['category'] ?? null)->replicate(4))
            ->filter()->countBy()->sortDesc()->keys()->take(3)->toArray();

        // Override with filter signal if available and has property_type
        if ($filterSignal && !empty($filterSignal['property_type'])) {
            array_unshift($preferredTypes, $filterSignal['property_type']);
            $preferredTypes = array_unique($preferredTypes);
        }

        // ── Listing type (favorites 5×, compared 4×, filter 3× override) ─────
        $listingTypeVotes = collect()
            ->merge($viewedData->pluck('listing_type'))
            ->merge($favoritedData->pluck('listing_type')->replicate(5))
            ->merge($comparedData->pluck('listing_type')->replicate(4))
            ->filter();

        $preferredListingType = $listingTypeVotes->mode()[0] ?? null;

        // Filter signal is most explicit — override if present
        if ($filterSignal && !empty($filterSignal['listing_type'])) {
            $preferredListingType = $filterSignal['listing_type'];
        }
        // Search signal active_filters can also hint listing type
        if ($searchSignal && !empty($searchSignal['active_filters']['listing_type'])) {
            $preferredListingType = $searchSignal['active_filters']['listing_type'];
        }

        // ── Price range (blended from all signals) ────────────────────────────
        //
        // Priority:
        //   filter max_price  → hard ceiling (most explicit)
        //   calculator budget → range blending
        //   compare avg       → decision-zone price
        //   favorite avg      → proven comfort
        //   view avg          → baseline

        $priceSource = $favoritedData->isNotEmpty() ? $favoritedData : $viewedData;
        $behaviorAvg = $priceSource->avg(fn($p) => $p->price['usd'] ?? 0) ?? 0;

        // Compare midpoint
        $compareAvg = $comparedData->avg(fn($p) => $p->price['usd'] ?? 0) ?? 0;

        // Blend: behavior(1×) + compare(4×) + favorites get double weight already above
        $behaviorBlended = $behaviorAvg;
        if ($compareAvg > 0 && $behaviorAvg > 0) {
            $behaviorBlended = (($behaviorAvg * 1) + ($compareAvg * 4)) / 5;
        } elseif ($compareAvg > 0) {
            $behaviorBlended = $compareAvg;
        }

        // Now blend with calculator
        if ($hasBudget && $behaviorBlended > 0) {
            $calcMid = ($calcSignal['budget_min_usd'] + $calcSignal['budget_max_usd']) / 2;
            $blendedAvg = ($behaviorBlended * (1 - $budgetWeight)) + ($calcMid * $budgetWeight);
        } elseif ($hasBudget) {
            $blendedAvg = ($calcSignal['budget_min_usd'] + $calcSignal['budget_max_usd']) / 2;
        } else {
            $blendedAvg = $behaviorBlended;
        }

        // Hard ceiling from filter (never recommend above what user explicitly capped)
        $hardCeilingUsd = null;
        if ($filterSignal && !empty($filterSignal['max_price_usd'])) {
            $hardCeilingUsd = (float) $filterSignal['max_price_usd'];
        }

        // Price tolerance tightens with stronger filter/calculator signals
        $hasExplicitFilter = $filterSignal && !empty($filterSignal['max_price_usd']);
        $priceTolerance = $hasBudget
            ? max(0.25, 0.45 - ($budgetWeight * 0.20))
            : ($hasExplicitFilter ? 0.20 : 0.35);

        // ── Bedrooms (from filter signal — most explicit) ─────────────────────
        $preferredBedrooms = null;
        if ($filterSignal && !empty($filterSignal['bedrooms'])) {
            $preferredBedrooms = (int) $filterSignal['bedrooms'];
        }

        // ── Cities (favorites 5×, compared 4×, filter override) ──────────────
        $preferredCities = collect()
            ->merge($favoritedData->map(fn($p) => $p->address_details['city']['en'] ?? null)->replicate(5))
            ->merge($comparedData->map(fn($p) => $p->address_details['city']['en'] ?? null)->replicate(4))
            ->merge($viewedData->map(fn($p) => $p->address_details['city']['en'] ?? null))
            ->filter()->countBy()->sortDesc()->keys()->take(3)->toArray();

        if ($filterSignal && !empty($filterSignal['city'])) {
            array_unshift($preferredCities, $filterSignal['city']);
            $preferredCities = array_unique($preferredCities);
        }

        // ── 5. Build the query ────────────────────────────────────────────────
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

            // Apply hard ceiling from filter
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

        // ── 6. Score — all signals contribute ────────────────────────────────
        //
        // Budget bonus: properties in calculator range earn up to 35 pts
        // Compare bonus: properties similar to compared ones float higher
        // (handled by price/type/city already being weighted by compare data)

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

        // ── 7. Fallback: relax city, keep type + listing type ─────────────────
        if ($results->count() < $limit) {
            $needed     = $limit - $results->count();
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
    //  Used when user has applied filters but no browse/favorite history.
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
                [(float)$filterSignal['max_price_usd']]
            );
        }
        if (!empty($filterSignal['min_price_usd'])) {
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) >= ?",
                [(float)$filterSignal['min_price_usd']]
            );
        }
        if (!empty($filterSignal['bedrooms'])) {
            $query->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(rooms, '$.bedroom.count')) = ?",
                [(int)$filterSignal['bedrooms']]
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
    //  BUDGET-MATCHED RECOMMENDATIONS  (unchanged)
    // ══════════════════════════════════════════════════════════════════════════
    private function getBudgetMatchedRecommendations(
        array $calcSignal,
        int   $limit,
        array $excludeIds = []
    ): Collection {
        $min = (float)($calcSignal['budget_min_usd'] ?? 0);
        $max = (float)($calcSignal['budget_max_usd'] ?? 0);
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
    //  GENERAL FALLBACK  (unchanged)
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
    //  IMPRESSIONS  (unchanged)
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
}