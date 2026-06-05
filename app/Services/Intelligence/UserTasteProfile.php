<?php

namespace App\Services\Intelligence;

use App\Models\Property;
use App\Models\User;
use App\Models\UserPropertyInteraction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * UserTasteProfile
 * ----------------
 * Builds ONE coherent picture of a user from ALL their signals, with recency
 * decay so fresh behaviour counts more than old behaviour.
 *
 * This is the "memory" half of the brain. The other half (FeedBrain) scores
 * properties against this profile. Every smart endpoint shares both, so the
 * app behaves consistently instead of running five disagreeing formulas.
 *
 * Signal weights (base, before decay):
 *   favorite          5.0   strongest taste signal
 *   compare           4.0   near-decision behaviour
 *   search_click      3.0   chose this from a list
 *   filter_applied    3.0   explicit stated intent (structured)
 *   calculator_search 3.0   explicit budget intent
 *   view              1.0   mild interest
 *   impression        0.1   barely a signal (was shown, may not have looked)
 *
 * Recency decay: weight *= 0.5 ^ (ageDays / HALF_LIFE_DAYS)
 *   A 14-day half-life means a signal is worth half as much after two weeks.
 */
class UserTasteProfile
{
    private const HALF_LIFE_DAYS = 14;
    private const LOOKBACK_DAYS  = 90;
    private const CACHE_TTL      = 900; // 15 min

    private const SIGNAL_WEIGHTS = [
        'favorite'          => 5.0,
        'compare'           => 4.0,
        'search_click'      => 3.0,
        'filter_applied'    => 3.0,
        'calculator_search' => 3.0,
        'view'              => 1.0,
        'impression'        => 0.1,
    ];

    private const VIRTUAL_IDS = [
        'calculator_signal',
        'filter_signal',
        'search_signal',
        'search_signal_latest',
    ];

    /**
     * Build the full taste profile for a user. Cached.
     *
     * Returns a normalised array:
     * [
     *   'has_history'    => bool,
     *   'intent_score'   => 0..100,   how close to buying (drives explore/exploit)
     *   'cities'         => ['Erbil' => 0.8, ...]  weight 0..1
     *   'types'          => ['villa' => 0.7, ...]
     *   'listing_type'   => 'sell'|'rent'|null
     *   'price'          => ['target' => 72000, 'min' => 57000, 'max' => 86000] (USD) | null
     *   'bedrooms'       => int|null
     *   'seen_ids'       => [...]  things to NOT show again
     *   'budget'         => raw calculator signal | null
     *   'signal_counts'  => ['favorite' => 3, ...]  for debugging / labels
     * ]
     */
    public function build(string $userId): array
    {
        if (str_starts_with($userId, 'guest_')) {
            return $this->emptyProfile();
        }

        return Cache::remember("taste_profile_{$userId}", self::CACHE_TTL, function () use ($userId) {
            try {
                return $this->compute($userId);
            } catch (\Throwable $e) {
                Log::warning('UserTasteProfile build failed', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
                return $this->emptyProfile();
            }
        });
    }

    public function invalidate(string $userId): void
    {
        Cache::forget("taste_profile_{$userId}");
    }

    // ──────────────────────────────────────────────────────────────────────

    private function compute(string $userId): array
    {
        $rows = UserPropertyInteraction::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->orderByDesc('created_at')
            ->get(['property_id', 'interaction_type', 'metadata', 'created_at']);

        if ($rows->isEmpty()) {
            return $this->emptyProfile();
        }

        // Split real property interactions from virtual "signal" rows.
        $realRows = $rows->whereNotIn('property_id', self::VIRTUAL_IDS)
            ->whereIn('interaction_type', array_keys(self::SIGNAL_WEIGHTS));

        // Pull the property data we'll attribute taste to, in one query.
        $propIds = $realRows->pluck('property_id')->unique()->values();
        $props   = Property::whereIn('id', $propIds)
            ->get(['id', 'type', 'address_details', 'price', 'rooms', 'listing_type'])
            ->keyBy('id');

        // Accumulators
        $cityW    = [];   // city  => decayed weight
        $typeW    = [];   // type  => decayed weight
        $listW    = [];   // sell/rent => decayed weight
        $bedW     = [];   // bedroom count => decayed weight
        $priceW   = [];   // [price => weight] pairs for weighted target
        $counts   = [];   // raw signal counts (for labels)
        $seenIds  = [];

        foreach ($realRows as $row) {
            $seenIds[] = $row->property_id;
            $counts[$row->interaction_type] = ($counts[$row->interaction_type] ?? 0) + 1;

            $prop = $props->get($row->property_id);
            if (!$prop) continue;

            $base  = self::SIGNAL_WEIGHTS[$row->interaction_type] ?? 0;
            $decay = $this->decay($row->created_at);
            $w     = $base * $decay;
            if ($w <= 0) continue;

            // City
            $city = $prop->address_details['city']['en'] ?? null;
            if ($city) $cityW[$city] = ($cityW[$city] ?? 0) + $w;

            // Type
            $type = $prop->type['category'] ?? null;
            if ($type) {
                $type = strtolower($type);
                $typeW[$type] = ($typeW[$type] ?? 0) + $w;
            }

            // Listing type (sell/rent)
            if ($prop->listing_type) {
                $listW[$prop->listing_type] = ($listW[$prop->listing_type] ?? 0) + $w;
            }

            // Bedrooms
            $beds = (int) ($prop->rooms['bedroom']['count'] ?? 0);
            if ($beds > 0) $bedW[$beds] = ($bedW[$beds] ?? 0) + $w;

            // Price (USD only)
            $usd = (float) ($prop->price['usd'] ?? 0);
            if ($usd > 0) $priceW[] = ['p' => $usd, 'w' => $w];
        }

        // Explicit signals override / sharpen behavioural ones.
        $filterSignal = $this->virtualSignal($rows, 'filter_applied', 'filter_signal');
        $calcSignal   = $this->virtualSignal($rows, 'calculator_search', 'calculator_signal');

        // ── Derive final profile ──────────────────────────────────────────
        $listingType = $this->topKey($listW);
        if ($filterSignal['listing_type'] ?? null) $listingType = $filterSignal['listing_type'];

        $price = $this->derivePriceBand($priceW, $filterSignal, $calcSignal);

        $bedrooms = $this->topKey($bedW);
        if ($filterSignal['bedrooms'] ?? null) $bedrooms = (int) $filterSignal['bedrooms'];

        $types = $this->normalise($typeW, 3);
        if ($filterSignal['property_type'] ?? null) {
            $ft = strtolower($filterSignal['property_type']);
            $types = [$ft => 1.0] + $types; // explicit choice goes to front at full weight
        }

        $cities = $this->normalise($cityW, 3);
        if ($filterSignal['city'] ?? null) {
            $cities = [$filterSignal['city'] => 1.0] + $cities;
        }

        return [
            'has_history'   => true,
            'intent_score'  => $this->intentScore($counts, $calcSignal, $filterSignal),
            'cities'        => $cities,
            'types'         => $types,
            'listing_type'  => $listingType,
            'price'         => $price,
            'bedrooms'      => $bedrooms ? (int) $bedrooms : null,
            'seen_ids'      => array_values(array_unique($seenIds)),
            'budget'        => $calcSignal ?: null,
            'signal_counts' => $counts,
        ];
    }

    /**
     * Intent score 0..100: how close is this person to buying?
     * Drives the explore/exploit balance downstream — high intent means
     * "show them the closest matches", low intent means "let them discover".
     */
    private function intentScore(array $counts, ?array $calc, ?array $filter): int
    {
        $score = 0;
        $score += min(($counts['favorite']     ?? 0) * 12, 30);
        $score += min(($counts['compare']      ?? 0) * 15, 30); // comparing = near decision
        $score += min(($counts['search_click'] ?? 0) * 4,  15);
        if ($filter) $score += 10;
        if ($calc) {
            $score += 10;
            $score += (int) round((($calc['signal_strength'] ?? 0) / 100) * 5);
        }
        return min($score, 100);
    }

    private function derivePriceBand(array $priceW, ?array $filter, ?array $calc): ?array
    {
        // 1. Explicit filter ceiling/floor wins.
        if ($filter && (!empty($filter['max_price_usd']) || !empty($filter['min_price_usd']))) {
            $min = (float) ($filter['min_price_usd'] ?? 0);
            $max = (float) ($filter['max_price_usd'] ?? 0);
            if ($max > 0) {
                $target = $min > 0 ? ($min + $max) / 2 : $max * 0.85;
                return ['target' => $target, 'min' => $min ?: $max * 0.6, 'max' => $max, 'source' => 'filter'];
            }
        }

        // 2. Behavioural weighted average price.
        $behaviourTarget = null;
        if (!empty($priceW)) {
            $sumWP = array_sum(array_map(fn($x) => $x['p'] * $x['w'], $priceW));
            $sumW  = array_sum(array_map(fn($x) => $x['w'], $priceW));
            if ($sumW > 0) $behaviourTarget = $sumWP / $sumW;
        }

        // 3. Calculator budget.
        $calcMid = null;
        if ($calc && !empty($calc['budget_min_usd']) && !empty($calc['budget_max_usd'])) {
            $calcMid = ((float) $calc['budget_min_usd'] + (float) $calc['budget_max_usd']) / 2;
        }

        // Blend behaviour + calculator weighted by calculator signal strength.
        if ($behaviourTarget && $calcMid) {
            $cw     = ($calc['signal_strength'] ?? 50) / 100;
            $target = $behaviourTarget * (1 - $cw) + $calcMid * $cw;
        } else {
            $target = $behaviourTarget ?? $calcMid;
        }

        if (!$target || $target <= 0) return null;

        // Tighter band for high-intent calculator users, wider for browsers.
        $tol = $calc ? 0.30 : 0.40;
        return [
            'target' => $target,
            'min'    => $target * (1 - $tol),
            'max'    => $target * (1 + $tol),
            'source' => $behaviourTarget ? 'behaviour' : 'calculator',
        ];
    }

    private function decay(\Illuminate\Support\Carbon $when): float
    {
        $ageDays = max(0, now()->diffInDays($when));
        return pow(0.5, $ageDays / self::HALF_LIFE_DAYS);
    }

    private function virtualSignal($rows, string $type, string $propId): ?array
    {
        $row = $rows->where('interaction_type', $type)
            ->where('property_id', $propId)
            ->first();
        if (!$row || !$row->metadata) return null;
        $meta = is_array($row->metadata) ? $row->metadata : json_decode($row->metadata, true);
        return $meta ?: null;
    }

    /** Normalise a weight map to 0..1 against its own max, keep top N. */
    private function normalise(array $map, int $topN): array
    {
        if (empty($map)) return [];
        arsort($map);
        $map = array_slice($map, 0, $topN, true);
        $max = max($map);
        if ($max <= 0) return [];
        return array_map(fn($w) => round($w / $max, 3), $map);
    }

    private function topKey(array $map)
    {
        if (empty($map)) return null;
        arsort($map);
        return array_key_first($map);
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
}
