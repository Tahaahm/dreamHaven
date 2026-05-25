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
    /**
     * Track user viewing a property
     */
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
            Log::error('Failed to track property view', [
                'user_id'     => $userId,
                'property_id' => $propertyId,
                'error'       => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Update user's recently viewed properties
     */
    private function updateRecentlyViewed(string $userId, string $propertyId): void
    {
        $user = User::find($userId);
        if (!$user) return;

        $recentlyViewed = $user->recently_viewed_properties ?? [];
        $recentlyViewed = array_filter($recentlyViewed, fn($id) => $id !== $propertyId);
        array_unshift($recentlyViewed, $propertyId);
        $recentlyViewed = array_slice($recentlyViewed, 0, 50);

        $user->update([
            'recently_viewed_properties' => $recentlyViewed,
            'last_activity_at'           => now(),
        ]);
    }

    /**
     * Get user's recently viewed properties
     */
    public function getRecentlyViewed(string $userId, int $limit = 20): Collection
    {
        $user = User::find($userId);
        if (!$user || empty($user->recently_viewed_properties)) {
            return collect();
        }

        $propertyIds = array_slice($user->recently_viewed_properties, 0, $limit);

        $properties = Property::whereIn('id', $propertyIds)
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->get();

        return $properties->sortBy(function ($property) use ($propertyIds) {
            return array_search($property->id, $propertyIds);
        })->values();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CALCULATOR SIGNAL — store user budget intent silently
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Called from the savings calculator page after every debounced calculation.
     * Upserts a single virtual interaction so it never spams the table.
     *
     * @param  string  $userId
     * @param  float   $targetPriceUsd   — the house price they're saving for
     * @param  float   $savedSoFarUsd    — current savings
     * @param  float   $monthlyUsd       — monthly saving amount (0 in howMuch mode)
     * @param  int     $targetYears      — target years (0 in howLong mode)
     * @param  string  $mode             — 'how_long' | 'how_much'
     */
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

            // Use a virtual property_id so it never conflicts with real rows.
            // updateOrCreate ensures one row per user — always the latest signal.
            UserPropertyInteraction::updateOrCreate(
                [
                    'user_id'          => $userId,
                    'property_id'      => 'calculator_signal',
                    'interaction_type' => 'calculator_search',
                ],
                [
                    'metadata' => json_encode([
                        'target_price_usd'  => $targetPriceUsd,
                        'saved_so_far_usd'  => $savedSoFarUsd,
                        'monthly_usd'       => $monthlyUsd,
                        'target_years'      => $targetYears,
                        'mode'              => $mode,
                        // Budget range for recommendation matching (±20% tolerance)
                        'budget_min_usd'    => round($targetPriceUsd * 0.80),
                        'budget_max_usd'    => round($targetPriceUsd * 1.20),
                        'signal_strength'   => $this->calcSignalStrength($targetPriceUsd, $savedSoFarUsd, $monthlyUsd, $targetYears),
                        'updated_at'        => now()->toISOString(),
                    ]),
                    'created_at' => now(),
                ]
            );

            // Bust recommendation cache so next page load reflects new budget
            Cache::forget("personalized_recs_{$userId}");

            Log::info('💰 Calculator signal stored', [
                'user_id'    => $userId,
                'price'      => $targetPriceUsd,
                'mode'       => $mode,
                'strength'   => $this->calcSignalStrength($targetPriceUsd, $savedSoFarUsd, $monthlyUsd, $targetYears),
            ]);
        } catch (\Throwable $e) {
            // Silent — never break the calculator
            Log::warning('Calculator signal failed (non-fatal)', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Signal strength 0–100:
     * Higher = user has a concrete plan → recommendation should weight it more.
     *
     * Logic:
     *  - Valid price alone:          20 pts
     *  - Has monthly saving:         +20 pts (they're actively planning)
     *  - monthly >= 5% of price/yr:  +20 pts (realistic plan)
     *  - Has saved something:        +20 pts (already started)
     *  - Saved >= 10% of target:     +20 pts (serious buyer)
     */
    private function calcSignalStrength(
        float $price,
        float $saved,
        float $monthly,
        int   $years
    ): int {
        $score = 20; // base: valid price

        if ($monthly > 0) {
            $score += 20;
            $annualSaving = $monthly * 12;
            if ($price > 0 && ($annualSaving / $price) >= 0.05) {
                $score += 20; // saving ≥5% of price per year = realistic
            }
        }

        if ($years > 0) {
            $score += 10; // they know their timeline
        }

        if ($saved > 0) {
            $score += 20;
            if ($price > 0 && ($saved / $price) >= 0.10) {
                $score += 10; // saved ≥10% of target = serious
            }
        }

        return min($score, 100);
    }

    /**
     * Read the stored calculator signal for a user.
     * Returns null if no signal exists or signal is older than 90 days.
     */
    private function getCalculatorSignal(string $userId): ?array
    {
        try {
            $row = UserPropertyInteraction::where('user_id', $userId)
                ->where('interaction_type', 'calculator_search')
                ->where('property_id', 'calculator_signal')
                ->where('created_at', '>=', now()->subDays(90))
                ->latest()
                ->first();

            if (!$row || !$row->metadata) return null;

            $meta = is_array($row->metadata)
                ? $row->metadata
                : json_decode($row->metadata, true);

            return $meta ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  PERSONALIZED RECOMMENDATIONS — extended with calculator signal
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Get personalized recommendations based on user behavior.
     *
     * Algorithm (in priority order):
     *  1. Behavioral profile  — types, listing type, price range, cities from views+favorites
     *  2. Calculator signal   — if user has used the savings calculator, boost budget-matched properties
     *  3. Scoring             — boosted, verified, views, favorites, rating, freshness
     *  4. Fallback            — relax city filter if not enough results
     *  5. General fallback    — if no history at all
     */
    public function getPersonalizedRecommendations(string $userId, int $limit = 20): Collection
    {
        $user = User::find($userId);
        if (!$user) {
            return $this->getGeneralRecommendations($limit);
        }

        // ── 1. Load interaction history ──────────────────────────────────────
        $interactions = DB::table('user_property_interactions')
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(60))
            ->whereIn('interaction_type', ['view', 'favorite'])
            ->select('property_id', 'interaction_type')
            ->get();

        $viewedIds    = $interactions->where('interaction_type', 'view')
            ->pluck('property_id')->toArray();
        $favoritedIds = $interactions->where('interaction_type', 'favorite')
            ->pluck('property_id')->toArray();

        $allInteractedIds = array_unique(array_merge($viewedIds, $favoritedIds));

        // ── 2. Read calculator signal (may be null) ──────────────────────────
        $calcSignal = $this->getCalculatorSignal($userId);
        $hasBudget  = $calcSignal !== null
            && ($calcSignal['budget_min_usd'] ?? 0) > 0
            && ($calcSignal['budget_max_usd'] ?? 0) > 0;

        // Signal strength determines how much it influences recommendations.
        // Weak signal (20/100) = just valid price, mild influence.
        // Strong signal (80+/100) = concrete plan with savings started.
        $signalStrength  = (int)($calcSignal['signal_strength'] ?? 0);
        $budgetWeight    = $hasBudget ? ($signalStrength / 100) : 0; // 0.0–1.0

        // ── 3. No behavioral history — use calculator signal or general ───────
        if (empty($allInteractedIds)) {
            if ($hasBudget && $signalStrength >= 40) {
                // User has no browse history but did use the calculator
                // → show budget-matched properties
                return $this->getBudgetMatchedRecommendations(
                    $calcSignal,
                    $limit,
                    []
                );
            }
            return $this->getGeneralRecommendations($limit);
        }

        // ── 4. Build behavioral profile ──────────────────────────────────────
        $viewedData    = Property::whereIn('id', $viewedIds)->get();
        $favoritedData = Property::whereIn('id', $favoritedIds)->get();

        // Property types (favorites count 3×)
        $preferredTypes = collect()
            ->merge($viewedData->pluck('type')->map(fn($t) => $t['category'] ?? null))
            ->merge($favoritedData->pluck('type')->map(fn($t) => $t['category'] ?? null))
            ->merge($favoritedData->pluck('type')->map(fn($t) => $t['category'] ?? null))
            ->merge($favoritedData->pluck('type')->map(fn($t) => $t['category'] ?? null))
            ->filter()->countBy()->sortDesc()->keys()->take(3)->toArray();

        // Listing type (favorites count 3×)
        $preferredListingType = collect()
            ->merge($viewedData->pluck('listing_type'))
            ->merge($favoritedData->pluck('listing_type'))
            ->merge($favoritedData->pluck('listing_type'))
            ->merge($favoritedData->pluck('listing_type'))
            ->filter()->mode()[0] ?? null;

        // Price range — blend behavioral average with calculator signal
        $priceSource = $favoritedData->isNotEmpty() ? $favoritedData : $viewedData;
        $behaviorAvg = $priceSource->avg(fn($p) => $p->price['usd'] ?? 0);

        // If calculator signal exists, blend it in weighted by signal strength
        if ($hasBudget && $behaviorAvg > 0) {
            $calcMid    = ($calcSignal['budget_min_usd'] + $calcSignal['budget_max_usd']) / 2;
            $blendedAvg = ($behaviorAvg * (1 - $budgetWeight)) + ($calcMid * $budgetWeight);
        } elseif ($hasBudget) {
            // No behavioral price data — use calculator directly
            $blendedAvg = ($calcSignal['budget_min_usd'] + $calcSignal['budget_max_usd']) / 2;
        } else {
            $blendedAvg = $behaviorAvg;
        }

        // Price tolerance: stronger calculator signal → tighter range
        $priceTolerance = $hasBudget
            ? max(0.25, 0.45 - ($budgetWeight * 0.20)) // 0.25–0.45
            : 0.35;

        // Cities (favorites 3×)
        $preferredCities = collect()
            ->merge($favoritedData->map(fn($p) => $p->address_details['city']['en'] ?? null))
            ->merge($favoritedData->map(fn($p) => $p->address_details['city']['en'] ?? null))
            ->merge($favoritedData->map(fn($p) => $p->address_details['city']['en'] ?? null))
            ->merge($viewedData->map(fn($p) => $p->address_details['city']['en'] ?? null))
            ->filter()->countBy()->sortDesc()->keys()->take(3)->toArray();

        // ── 5. Build the main recommendation query ───────────────────────────
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
            $query->whereBetween(
                DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2))"),
                [
                    $blendedAvg * (1 - $priceTolerance),
                    $blendedAvg * (1 + $priceTolerance),
                ]
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

        // ── 6. Scoring — calculator signal adds a budget-match bonus ─────────
        //
        // Budget match bonus logic:
        //   If the property price is within the calculator budget range,
        //   it earns up to 35 bonus points, scaled by signal strength.
        //   Weak signal (20) → up to 7 pts.  Strong signal (100) → up to 35 pts.
        //
        // This means calculator-matched properties float to the top without
        // completely overriding behavioral preferences (types, cities, etc.)
        $budgetBonusExpr = '0';
        if ($hasBudget) {
            $min    = (float) $calcSignal['budget_min_usd'];
            $max    = (float) $calcSignal['budget_max_usd'];
            $bonus  = round(35 * $budgetWeight); // 0–35 pts
            // Properties within the budget range earn the bonus
            $budgetBonusExpr = "
                (CASE
                    WHEN CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2))
                         BETWEEN {$min} AND {$max}
                    THEN {$bonus}
                    ELSE 0
                END)
            ";
        }

        $results = $query->selectRaw("
            *,
            (
                (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                (CASE WHEN verified  = 1 THEN 20 ELSE 0 END) +
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

        // ── 7. Fallback: relax city filter if not enough results ─────────────
        if ($results->count() < $limit) {
            $needed      = $limit - $results->count();
            $existingIds = array_merge($allInteractedIds, $results->pluck('id')->toArray());

            $fallback = Property::query()
                ->where('is_active', true)
                ->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                ->whereNotIn('id', $existingIds)
                ->when($preferredListingType, fn($q) => $q->where('listing_type', $preferredListingType))
                ->selectRaw("*, (
                    (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                    (CASE WHEN verified  = 1 THEN 20 ELSE 0 END) +
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

    // ═══════════════════════════════════════════════════════════════════════════
    //  BUDGET-MATCHED RECOMMENDATIONS
    //  Used when user has calculator signal but no browse/favorite history.
    // ═══════════════════════════════════════════════════════════════════════════

    private function getBudgetMatchedRecommendations(
        array $calcSignal,
        int   $limit,
        array $excludeIds = []
    ): Collection {
        $min = (float)($calcSignal['budget_min_usd'] ?? 0);
        $max = (float)($calcSignal['budget_max_usd'] ?? 0);

        if ($min <= 0 || $max <= 0) {
            return $this->getGeneralRecommendations($limit);
        }

        $results = Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $excludeIds)
            ->whereBetween(
                DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2))"),
                [$min, $max]
            )
            ->selectRaw('
                *,
                (
                    (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                    (CASE WHEN verified  = 1 THEN 20 ELSE 0 END) +
                    (LEAST(views, 100) * 0.15) +
                    (LEAST(favorites_count, 50) * 0.8) +
                    (rating * 5) +
                    (CASE
                        WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 15
                        WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10
                        ELSE 0
                    END)
                ) as recommendation_score
            ')
            ->orderByDesc('recommendation_score')
            ->limit($limit)
            ->get();

        // If not enough budget-matched results, fill with general
        if ($results->count() < $limit) {
            $needed   = $limit - $results->count();
            $existing = array_merge($excludeIds, $results->pluck('id')->toArray());
            $general  = $this->getGeneralRecommendations($needed + 5)
                ->whereNotIn('id', $existing)
                ->take($needed);
            $results  = $results->merge($general);
        }

        return $results;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  GENERAL FALLBACK (unchanged)
    // ═══════════════════════════════════════════════════════════════════════════

    private function getGeneralRecommendations(int $limit): Collection
    {
        return Property::query()
            ->where('is_active', true)
            ->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->selectRaw('
                *,
                (
                    (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                    (CASE WHEN verified  = 1 THEN 20 ELSE 0 END) +
                    (LEAST(views, 100) * 0.15) +
                    (LEAST(favorites_count, 50) * 0.5) +
                    (rating * 5) +
                    (CASE
                        WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 20
                        WHEN DATEDIFF(NOW(), created_at) <= 14 THEN 15
                        WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10
                        ELSE 0
                    END)
                ) as recommendation_score
            ')
            ->orderByDesc('recommendation_score')
            ->limit($limit)
            ->get();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  IMPRESSIONS (unchanged)
    // ═══════════════════════════════════════════════════════════════════════════

    public function trackImpressions(string $userId, $properties, string $sourceEndpoint, array $extra = []): void
    {
        if (empty($properties) || (is_object($properties) && method_exists($properties, 'isEmpty') && $properties->isEmpty())) {
            return;
        }

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
                    'created_at'       => $timestamp,
                ];
            }

            Log::info('🔵 trackImpressions called', [
                'userId'        => $userId,
                'isGuest'       => $isGuest,
                'sessionId'     => $sessionId,
                'propertyCount' => count($insertData),
                'endpoint'      => $sourceEndpoint,
            ]);

            UserPropertyInteraction::insert($insertData);
        } catch (\Exception $e) {
            Log::error('Failed to track impressions', [
                'user_id'  => $userId,
                'endpoint' => $sourceEndpoint,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}