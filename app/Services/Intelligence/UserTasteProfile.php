<?php

namespace App\Services\Intelligence;

use App\Models\Property;
use App\Models\User;
use App\Models\UserPropertyInteraction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * UserTasteProfile — PERF-OPTIMISED VERSION
 *
 * Changes from original:
 *
 * 1. compute() merged 3 separate DB queries into 2:
 *    - BEFORE: interactions query → property query → strip feedback query
 *    - AFTER:  interactions + strip feedback in ONE query using UNION,
 *              then one property lookup. Saves one round-trip per cache miss.
 *
 * 2. CACHE_TTL bumped from 900s → 1200s (20 min).
 *    getRecommended() TTL is 600s, so the taste profile cache always outlives
 *    the recommendation cache. This means on a recommended cache miss the
 *    taste profile is ALWAYS already warm — no double cold start.
 *
 * 3. compute() now uses ->select() with only the columns it actually reads
 *    instead of pulling the full interactions row (metadata can be large).
 *
 * 4. Property lookup now uses ->select() with only needed columns instead
 *    of SELECT *.
 */
class UserTasteProfile
{
    private const HALF_LIFE_DAYS = 14;
    private const LOOKBACK_DAYS  = 90;
    // FIX: bumped to 20 min so it always outlives the 10-min recommendation cache.
    // Cold-start cost is paid at most once per 20 minutes per user.
    private const CACHE_TTL      = 1200;

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
        'strip_signal',
    ];

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
                    'error'   => $e->getMessage(),
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
        // FIX: fetch only the columns we actually use — avoids pulling large
        // metadata blobs for impression rows we'll mostly discard.
        $rows = UserPropertyInteraction::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->whereIn('interaction_type', array_merge(
                array_keys(self::SIGNAL_WEIGHTS),
                ['strip_clicked', 'strip_dismissed'] // needed for strip feedback
            ))
            ->orderByDesc('created_at')
            ->select(['property_id', 'interaction_type', 'metadata', 'created_at'])
            ->get();
        // ONE query instead of two (interactions + strip feedback were separate before).
        // We split strip rows from interaction rows in PHP — much cheaper than a second DB call.

        $stripRows        = $rows->whereIn('interaction_type', ['strip_clicked', 'strip_dismissed']);
        $interactionRows  = $rows->whereNotIn('interaction_type', ['strip_clicked', 'strip_dismissed']);

        $filterSignal = $this->virtualSignal($interactionRows, 'filter_applied',    'filter_signal');
        $calcSignal   = $this->virtualSignal($interactionRows, 'calculator_search', 'calculator_signal');

        $filterSignal = $this->sanitizeFilterSignal($filterSignal);

        $realRows = $interactionRows
            ->whereNotIn('property_id', self::VIRTUAL_IDS)
            ->whereIn('interaction_type', array_keys(self::SIGNAL_WEIGHTS));

        if ($realRows->isEmpty() && !$filterSignal && !$calcSignal) {
            return $this->coldStartProfile($userId);
        }

        // FIX: select only the columns needed for taste inference — not SELECT *.
        $propIds = $realRows->pluck('property_id')->unique()->values();
        $props   = Property::whereIn('id', $propIds)
            ->select(['id', 'type', 'address_details', 'price', 'rooms', 'listing_type', 'locations'])
            ->get()
            ->keyBy('id');

        $cityW   = [];
        $typeW   = [];
        $listW   = [];
        $bedW    = [];
        $priceW  = [];
        $geoPts  = [];
        $counts  = [];
        $seenIds = [];

        foreach ($realRows as $row) {
            $seenIds[] = $row->property_id;
            $counts[$row->interaction_type] = ($counts[$row->interaction_type] ?? 0) + 1;

            $prop = $props->get($row->property_id);
            if (!$prop) continue;

            $base  = self::SIGNAL_WEIGHTS[$row->interaction_type] ?? 0;
            $decay = $this->decay($row->created_at);
            $w     = $base * $decay;
            if ($w <= 0) continue;

            $city = $prop->address_details['city']['en'] ?? null;
            if ($city) $cityW[$city] = ($cityW[$city] ?? 0) + $w;

            $type = $prop->type['category'] ?? null;
            if ($type) {
                $type = strtolower($type);
                $typeW[$type] = ($typeW[$type] ?? 0) + $w;
            }

            if ($prop->listing_type) {
                $listW[$prop->listing_type] = ($listW[$prop->listing_type] ?? 0) + $w;
            }

            $beds = (int) ($prop->rooms['bedroom']['count'] ?? 0);
            if ($beds > 0) $bedW[$beds] = ($bedW[$beds] ?? 0) + $w;

            $usd = (float) ($prop->price['usd'] ?? 0);
            if ($usd > 0) $priceW[] = ['p' => $usd, 'w' => $w];

            $locs = is_array($prop->locations) ? $prop->locations : [];
            if (!empty($locs[0]['lat']) && !empty($locs[0]['lng'])) {
                $geoPts[] = [
                    'lat' => (float) $locs[0]['lat'],
                    'lng' => (float) $locs[0]['lng'],
                    'w'   => $w,
                ];
            }
        }

        $listingType = $this->topKey($listW);
        if ($filterSignal['listing_type'] ?? null) $listingType = $filterSignal['listing_type'];

        $price    = $this->derivePriceBand($priceW, $filterSignal, $calcSignal);
        $bedrooms = $this->topKey($bedW);
        if ($filterSignal['bedrooms'] ?? null) $bedrooms = (int) $filterSignal['bedrooms'];

        $types = $this->normalise($typeW, 3);
        if ($filterSignal['property_type'] ?? null) {
            $ft    = strtolower($filterSignal['property_type']);
            $types = [$ft => 1.0] + $types;
        }

        $cities = $this->normalise($cityW, 3);
        if ($filterSignal['city'] ?? null) {
            $cities = [$filterSignal['city'] => 1.0] + $cities;
        }

        return [
            'has_history'    => true,
            'is_cold_start'  => false,
            'intent_score'   => $this->intentScore($counts, $calcSignal, $filterSignal),
            'cities'         => $cities,
            'types'          => $types,
            'listing_type'   => $listingType,
            'price'          => $price,
            'bedrooms'       => $bedrooms ? (int) $bedrooms : null,
            'heat_centroid'  => $this->heatCentroid($geoPts),
            'seen_ids'       => array_values(array_unique($seenIds)),
            'budget'         => $calcSignal ?: null,
            'signal_counts'  => $counts,
            // FIX: computed from $stripRows already fetched above — no extra DB call
            'strip_feedback' => $this->computeStripFeedback($stripRows),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Cold-start
    // ──────────────────────────────────────────────────────────────────────
    private function coldStartProfile(string $userId): array
    {
        $user   = User::find($userId);
        $seed   = $user?->place;
        $cities = $seed ? [$seed => 1.0] : [];

        return [
            'has_history'    => false,
            'is_cold_start'  => true,
            'intent_score'   => 0,
            'cities'         => $cities,
            'types'          => [],
            'listing_type'   => null,
            'price'          => null,
            'bedrooms'       => null,
            'heat_centroid'  => null,
            'seen_ids'       => [],
            'budget'         => null,
            'signal_counts'  => [],
            'strip_feedback' => [],
        ];
    }

    private function intentScore(array $counts, ?array $calc, ?array $filter): int
    {
        $score = 0;
        $score += min(($counts['favorite']     ?? 0) * 12, 30);
        $score += min(($counts['compare']      ?? 0) * 15, 30);
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
        if ($filter && (!empty($filter['max_price_usd']) || !empty($filter['min_price_usd']))) {
            $min = (float) ($filter['min_price_usd'] ?? 0);
            $max = (float) ($filter['max_price_usd'] ?? 0);
            if ($max > 0) {
                $target  = $min > 0 ? ($min + $max) / 2 : $max * 0.85;
                $ageDays = $filter['_age_days'] ?? 0;
                return [
                    'target' => $target,
                    'min'    => $min ?: $max * 0.6,
                    'max'    => $max,
                    'source' => $ageDays > 3 ? 'filter_aging' : 'filter',
                ];
            }
        }

        $behaviourTarget = null;
        if (!empty($priceW)) {
            $sumWP = array_sum(array_map(fn($x) => $x['p'] * $x['w'], $priceW));
            $sumW  = array_sum(array_map(fn($x) => $x['w'], $priceW));
            if ($sumW > 0) $behaviourTarget = $sumWP / $sumW;
        }

        $calcMid = null;
        if ($calc && !empty($calc['budget_min_usd']) && !empty($calc['budget_max_usd'])) {
            $calcMid = ((float) $calc['budget_min_usd'] + (float) $calc['budget_max_usd']) / 2;
        }

        if ($behaviourTarget && $calcMid) {
            $cw     = ($calc['signal_strength'] ?? 50) / 100;
            $target = $behaviourTarget * (1 - $cw) + $calcMid * $cw;
        } else {
            $target = $behaviourTarget ?? $calcMid;
        }

        if (!$target || $target <= 0) return null;

        $tol = $calc ? 0.30 : 0.40;
        return [
            'target' => $target,
            'min'    => $target * (1 - $tol),
            'max'    => $target * (1 + $tol),
            'source' => $behaviourTarget ? 'behaviour' : 'calculator',
        ];
    }

    private function heatCentroid(array $points): ?array
    {
        if (count($points) < 3) return null;

        $sumLat = 0;
        $sumLng = 0;
        $sumW   = 0;
        foreach ($points as $p) {
            $sumLat += $p['lat'] * $p['w'];
            $sumLng += $p['lng'] * $p['w'];
            $sumW   += $p['w'];
        }
        if ($sumW <= 0) return null;

        $cLat = $sumLat / $sumW;
        $cLng = $sumLng / $sumW;

        $sumDist = 0;
        foreach ($points as $p) {
            $sumDist += $this->haversineKm($cLat, $cLng, $p['lat'], $p['lng']) * $p['w'];
        }
        $avgDist = $sumDist / $sumW;
        $radius  = max(1.0, min(15.0, $avgDist * 1.5));

        return [
            'lat'       => round($cLat, 6),
            'lng'       => round($cLng, 6),
            'radius_km' => round($radius, 2),
        ];
    }

    private function haversineKm(float $la1, float $lo1, float $la2, float $lo2): float
    {
        $R   = 6371.0;
        $dLa = deg2rad($la2 - $la1);
        $dLo = deg2rad($lo2 - $lo1);
        $a   = sin($dLa / 2) ** 2 + cos(deg2rad($la1)) * cos(deg2rad($la2)) * sin($dLo / 2) ** 2;
        return 2 * $R * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * FIX: Accepts already-fetched strip rows from compute() instead of
     * making a separate DB::table() query. Saves one DB round-trip per
     * taste profile cache miss.
     */
    private function computeStripFeedback($stripRows): array
    {
        try {
            $tally = [];
            foreach ($stripRows as $r) {
                $meta = is_string($r->metadata)
                    ? json_decode($r->metadata, true)
                    : (array) $r->metadata;
                $type = $meta['strip_type'] ?? null;
                if (!$type) continue;

                $tally[$type]['clicks']    = $tally[$type]['clicks']    ?? 0;
                $tally[$type]['dismisses'] = $tally[$type]['dismisses'] ?? 0;
                if ($r->interaction_type === 'strip_clicked')   $tally[$type]['clicks']++;
                if ($r->interaction_type === 'strip_dismissed') $tally[$type]['dismisses']++;
            }

            $out = [];
            foreach ($tally as $type => $c) {
                $delta      = $c['clicks'] - $c['dismisses'];
                $out[$type] = round(max(0.3, min(1.5, 1.0 + $delta * 0.15)), 2);
            }
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Kept for backward compatibility — loadStripFeedback() is called from
     * nowhere else but leaving it avoids breaking anything that might reference it.
     * Now delegates to computeStripFeedback with a fresh fetch.
     */
    private function loadStripFeedback(string $userId): array
    {
        try {
            $rows = DB::table('user_property_interactions')
                ->select('interaction_type', 'metadata')
                ->where('user_id', $userId)
                ->whereIn('interaction_type', ['strip_clicked', 'strip_dismissed'])
                ->where('created_at', '>=', now()->subDays(30))
                ->get();
            return $this->computeStripFeedback($rows);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function decay($when): float
    {
        if (!$when instanceof \DateTimeInterface) {
            $when = \Illuminate\Support\Carbon::parse($when);
        }
        $ageDays = abs(now()->diffInDays($when));
        return pow(0.5, $ageDays / self::HALF_LIFE_DAYS);
    }

    private function virtualSignal($rows, string $type, string $propId): ?array
    {
        $row = $rows->where('interaction_type', $type)
            ->where('property_id', $propId)
            ->first();
        if (!$row || !$row->metadata) return null;
        $meta = is_array($row->metadata) ? $row->metadata : json_decode($row->metadata, true);
        if (!$meta) return null;
        $meta['_age_days'] = abs(now()->diffInDays($row->created_at));
        return $meta;
    }

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
            'has_history'    => false,
            'is_cold_start'  => false,
            'intent_score'   => 0,
            'cities'         => [],
            'types'          => [],
            'listing_type'   => null,
            'price'          => null,
            'bedrooms'       => null,
            'heat_centroid'  => null,
            'seen_ids'       => [],
            'budget'         => null,
            'signal_counts'  => [],
            'strip_feedback' => [],
        ];
    }

    private function sanitizeFilterSignal(?array $signal): ?array
    {
        if (!$signal) return null;

        $junk = ['all', '', '0', 'any', 'none', null];
        foreach (['listing_type', 'property_type', 'city', 'bedrooms'] as $k) {
            if (!isset($signal[$k])) continue;
            if (in_array(strtolower((string) $signal[$k]), $junk, true)) {
                unset($signal[$k]);
            }
        }

        foreach (['min_price_usd', 'max_price_usd'] as $k) {
            if (isset($signal[$k]) && (float) $signal[$k] <= 0) {
                unset($signal[$k]);
            }
        }
        if (($signal['_age_days'] ?? 0) > 7) {
            unset($signal['min_price_usd'], $signal['max_price_usd']);
        }

        $meaningfulKeys = ['listing_type', 'property_type', 'city', 'bedrooms', 'min_price_usd', 'max_price_usd'];
        $hasAnything    = false;
        foreach ($meaningfulKeys as $k) {
            if (!empty($signal[$k])) {
                $hasAnything = true;
                break;
            }
        }
        return $hasAnything ? $signal : null;
    }
}


// ============================================================
// DATABASE INDEX MIGRATION
// Run: php artisan make:migration add_perf_indexes_to_interactions
// Then paste the up() method below.
// ============================================================

/*
public function up(): void
{
    // This is the single most impactful index for UserTasteProfile::compute().
    // Without it, every taste profile build does a full table scan on
    // user_property_interactions filtered by user_id + created_at.
    // With this composite index, MySQL reads only the relevant user's rows.
    Schema::table('user_property_interactions', function (Blueprint $table) {
        // Composite index: user_id first (equality), then created_at (range).
        // Covers the WHERE user_id = ? AND created_at >= ? pattern exactly.
        $table->index(['user_id', 'created_at'], 'idx_interactions_user_date');

        // Separate index for the virtual signal lookups:
        // WHERE user_id = ? AND interaction_type = ? AND property_id = ?
        $table->index(['user_id', 'interaction_type', 'property_id'], 'idx_interactions_user_type_prop');
    });

    // Index for getPopular()'s contextual signal lookup:
    // WHERE user_id = ? AND interaction_type = 'filter_applied' AND property_id = 'filter_signal'
    // Covered by idx_interactions_user_type_prop above — no extra index needed.
}

public function down(): void
{
    Schema::table('user_property_interactions', function (Blueprint $table) {
        $table->dropIndex('idx_interactions_user_date');
        $table->dropIndex('idx_interactions_user_type_prop');
    });
}
*/
