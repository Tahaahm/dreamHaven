<?php

namespace App\Services\Intelligence;

use App\Models\Property;
use App\Models\User;
use App\Models\UserPropertyInteraction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * UserTasteProfile — v2 with all new signals
 *
 * NEW signal weights added:
 *   scroll_depth      → 0.5–4.0× based on depth
 *   time_on_listing   → -1.0–4.0× (negative for bounces)
 *   return_to_listing → 8.0×
 *   photo_gallery_open→ 2.5×
 *   contact_intent    → 6.0×
 *   share_property    → 3.5×
 *   map_pin_tap       → 2.0× (also contributes to heat centroid)
 *   search_refinement → 3.0×
 *
 * NEGATIVE SIGNALS:
 *   time_on_listing (<5s) → -1.0× penalises that property's type/price/city
 *
 * SESSION CONTEXT:
 *   Last 2 hours of interactions dominate (3× amplifier)
 *   vs 90-day history (1× base)
 */
class UserTasteProfile
{
    private const HALF_LIFE_DAYS  = 14;
    private const LOOKBACK_DAYS   = 90;
    private const CACHE_TTL       = 1200; // 20 min
    private const SESSION_WINDOW  = 2;    // hours — "today's session"
    private const SESSION_BOOST   = 3.0;  // session interactions worth 3×

    private const SIGNAL_WEIGHTS = [
        // Existing
        'favorite'            => 5.0,
        'compare'             => 4.0,
        'search_click'        => 3.0,
        'filter_applied'      => 3.0,
        'calculator_search'   => 3.0,
        'view'                => 1.0,
        'impression'          => 0.1,

        // NEW signals — weights are base; actual weight read from metadata
        'scroll_depth'        => 1.0,  // overridden by metadata.weight (0.5–4.0)
        'time_on_listing'     => 1.0,  // overridden by metadata.weight (-1.0–4.0)
        'return_to_listing'   => 8.0,
        'photo_gallery_open'  => 2.5,
        'contact_intent'      => 6.0,
        'share_property'      => 3.5,
        'map_pin_tap'         => 2.0,
        'search_refinement'   => 3.0,
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
        $allSignalTypes = array_merge(
            array_keys(self::SIGNAL_WEIGHTS),
            ['strip_clicked', 'strip_dismissed']
        );

        $rows = UserPropertyInteraction::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->whereIn('interaction_type', $allSignalTypes)
            ->orderByDesc('created_at')
            ->select(['property_id', 'interaction_type', 'metadata', 'created_at'])
            ->get();

        $stripRows       = $rows->whereIn('interaction_type', ['strip_clicked', 'strip_dismissed']);
        $interactionRows = $rows->whereNotIn('interaction_type', ['strip_clicked', 'strip_dismissed']);

        // Split session (last 2h) vs historical
        $sessionCutoff   = now()->subHours(self::SESSION_WINDOW);
        $sessionRows     = $interactionRows->filter(fn($r) => $r->created_at >= $sessionCutoff);
        $historicalRows  = $interactionRows->filter(fn($r) => $r->created_at < $sessionCutoff);

        $filterSignal = $this->virtualSignal($interactionRows, 'filter_applied',    'filter_signal');
        $calcSignal   = $this->virtualSignal($interactionRows, 'calculator_search', 'calculator_signal');
        $filterSignal = $this->sanitizeFilterSignal($filterSignal);

        $realRows = $interactionRows
            ->whereNotIn('property_id', self::VIRTUAL_IDS)
            ->whereIn('interaction_type', array_keys(self::SIGNAL_WEIGHTS));

        if ($realRows->isEmpty() && !$filterSignal && !$calcSignal) {
            return $this->coldStartProfile($userId);
        }

        // Property lookup for all interacted properties
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
        $negativeTypes  = []; // track negative signals by type/city
        $negativePrices = [];
        $counts  = [];
        $seenIds = [];

        foreach ($realRows as $row) {
            $seenIds[] = $row->property_id;
            $counts[$row->interaction_type] = ($counts[$row->interaction_type] ?? 0) + 1;

            $prop = $props->get($row->property_id);

            // ── Derive weight ──────────────────────────────────────────────
            $baseWeight = $this->resolveSignalWeight($row);
            $isSession  = $row->created_at >= $sessionCutoff;
            $decay      = $this->decay($row->created_at);

            // Session interactions get 3× boost
            $w = $baseWeight * $decay * ($isSession ? self::SESSION_BOOST : 1.0);

            // ── Handle NEGATIVE signals ────────────────────────────────────
            if ($baseWeight < 0) {
                // This is a bounce — penalise the property's type/price
                if ($prop) {
                    $type = strtolower($prop->type['category'] ?? '');
                    if ($type) $negativeTypes[$type] = ($negativeTypes[$type] ?? 0) + abs($w);

                    $usd = (float) ($prop->price['usd'] ?? 0);
                    if ($usd > 0) $negativePrices[] = $usd;
                }
                continue; // don't add negative to positive weights
            }

            if ($w <= 0 || !$prop) continue;

            // ── Accumulate positive signals ────────────────────────────────
            $city = $prop->address_details['city']['en'] ?? null;
            if ($city) $cityW[$city] = ($cityW[$city] ?? 0) + $w;

            $type = strtolower($prop->type['category'] ?? '');
            if ($type) $typeW[$type] = ($typeW[$type] ?? 0) + $w;

            if ($prop->listing_type) {
                $listW[$prop->listing_type] = ($listW[$prop->listing_type] ?? 0) + $w;
            }

            $beds = (int) ($prop->rooms['bedroom']['count'] ?? 0);
            if ($beds > 0) $bedW[$beds] = ($bedW[$beds] ?? 0) + $w;

            $usd = (float) ($prop->price['usd'] ?? 0);
            if ($usd > 0) $priceW[] = ['p' => $usd, 'w' => $w];

            // ── Geo points — include map_pin_tap coords too ────────────────
            $meta = is_array($row->metadata)
                ? $row->metadata
                : json_decode($row->metadata, true);

            // If this is a map_pin_tap, use the exact tap coords (more precise)
            if ($row->interaction_type === 'map_pin_tap' && !empty($meta['lat']) && !empty($meta['lng'])) {
                $geoPts[] = [
                    'lat' => (float) $meta['lat'],
                    'lng' => (float) $meta['lng'],
                    'w'   => $w * 2.0, // extra weight: user explicitly tapped here
                ];
            } else {
                $locs = is_array($prop->locations) ? $prop->locations : [];
                if (!empty($locs[0]['lat']) && !empty($locs[0]['lng'])) {
                    $geoPts[] = [
                        'lat' => (float) $locs[0]['lat'],
                        'lng' => (float) $locs[0]['lng'],
                        'w'   => $w,
                    ];
                }
            }
        }

        // ── Apply negative type penalties ──────────────────────────────────
        foreach ($negativeTypes as $type => $penalty) {
            if (isset($typeW[$type])) {
                $typeW[$type] = max(0, $typeW[$type] - $penalty);
                if ($typeW[$type] === 0) unset($typeW[$type]);
            }
        }

        // ── Derive final values ────────────────────────────────────────────
        $listingType = $this->topKey($listW);
        if ($filterSignal['listing_type'] ?? null) $listingType = $filterSignal['listing_type'];

        $price    = $this->derivePriceBand($priceW, $filterSignal, $calcSignal, $negativePrices);
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

        // ── Session context summary ────────────────────────────────────────
        $sessionContext = $this->buildSessionContext($sessionRows, $props);

        return [
            'has_history'      => true,
            'is_cold_start'    => false,
            'intent_score'     => $this->intentScore($counts, $calcSignal, $filterSignal),
            'cities'           => $cities,
            'types'            => $types,
            'listing_type'     => $listingType,
            'price'            => $price,
            'bedrooms'         => $bedrooms ? (int) $bedrooms : null,
            'heat_centroid'    => $this->heatCentroid($geoPts),
            'seen_ids'         => array_values(array_unique($seenIds)),
            'budget'           => $calcSignal ?: null,
            'signal_counts'    => $counts,
            'strip_feedback'   => $this->computeStripFeedback($stripRows),
            'negative_types'   => array_keys($negativeTypes),  // NEW
            'session_context'  => $sessionContext,               // NEW
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  NEW: Resolve actual weight from signal row
    //  (new signals store their weight in metadata)
    // ──────────────────────────────────────────────────────────────────────
    private function resolveSignalWeight($row): float
    {
        $baseWeight = self::SIGNAL_WEIGHTS[$row->interaction_type] ?? 0;

        // For new signal types that store weight in metadata
        $metaWeightTypes = [
            'scroll_depth',
            'time_on_listing',
            'photo_gallery_open',
            'contact_intent',
            'share_property',
            'map_pin_tap',
            'search_refinement',
        ];

        if (in_array($row->interaction_type, $metaWeightTypes) && $row->metadata) {
            $meta = is_array($row->metadata)
                ? $row->metadata
                : json_decode($row->metadata, true);
            if (isset($meta['weight'])) {
                return (float) $meta['weight'];
            }
        }

        return $baseWeight;
    }

    // ──────────────────────────────────────────────────────────────────────
    //  NEW: Build session context (last 2h summary)
    // ──────────────────────────────────────────────────────────────────────
    private function buildSessionContext($sessionRows, $propsById): array
    {
        if ($sessionRows->isEmpty()) return [];

        $realSession = $sessionRows->whereNotIn('property_id', self::VIRTUAL_IDS);

        $sessionPropIds = $realSession->pluck('property_id')->unique()->values();
        $sessionTypes   = [];
        $sessionCities  = [];
        $hasContactIntent = false;
        $hasReturnVisit   = false;

        foreach ($realSession as $row) {
            if ($row->interaction_type === 'contact_intent') $hasContactIntent = true;
            if ($row->interaction_type === 'return_to_listing') $hasReturnVisit = true;

            $prop = $propsById->get($row->property_id);
            if (!$prop) continue;

            $type = strtolower($prop->type['category'] ?? '');
            if ($type) $sessionTypes[$type] = ($sessionTypes[$type] ?? 0) + 1;

            $city = $prop->address_details['city']['en'] ?? null;
            if ($city) $sessionCities[$city] = ($sessionCities[$city] ?? 0) + 1;
        }

        arsort($sessionTypes);
        arsort($sessionCities);

        return [
            'property_count'    => $sessionPropIds->count(),
            'dominant_type'     => array_key_first($sessionTypes),
            'dominant_city'     => array_key_first($sessionCities),
            'has_contact_intent' => $hasContactIntent,
            'has_return_visit'  => $hasReturnVisit,
            'types_viewed'      => $sessionTypes,
            'cities_viewed'     => $sessionCities,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  UPDATED: derivePriceBand now also avoids negative-signalled prices
    // ──────────────────────────────────────────────────────────────────────
    private function derivePriceBand(
        array   $priceW,
        ?array  $filter,
        ?array  $calc,
        array   $negativePrices = []
    ): ?array {
        // (same logic as before, plus negative price exclusion)
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

        // If we have negative prices, cap the max at slightly below the cheapest bounce
        $negativeCap = null;
        if (!empty($negativePrices)) {
            $negativeCap = min($negativePrices) * 0.95; // 5% below cheapest bounce
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
        $max = $target * (1 + $tol);

        // Apply negative price cap
        if ($negativeCap && $max > $negativeCap) {
            $max = $negativeCap;
        }

        return [
            'target'       => $target,
            'min'          => $target * (1 - $tol),
            'max'          => $max,
            'source'       => $behaviourTarget ? 'behaviour' : 'calculator',
            'negative_cap' => $negativeCap,
        ];
    }

    // ── All existing helpers preserved ────────────────────────────────────

    private function coldStartProfile(string $userId): array
    {
        $user   = User::find($userId);
        $seed   = $user?->place;
        $cities = $seed ? [$seed => 1.0] : [];

        return [
            'has_history'      => false,
            'is_cold_start'    => true,
            'intent_score'     => 0,
            'cities'           => $cities,
            'types'            => [],
            'listing_type'     => null,
            'price'            => null,
            'bedrooms'         => null,
            'heat_centroid'    => null,
            'seen_ids'         => [],
            'budget'           => null,
            'signal_counts'    => [],
            'strip_feedback'   => [],
            'negative_types'   => [],
            'session_context'  => [],
        ];
    }

    private function intentScore(array $counts, ?array $calc, ?array $filter): int
    {
        $score = 0;
        $score += min(($counts['favorite']          ?? 0) * 12, 30);
        $score += min(($counts['compare']           ?? 0) * 15, 30);
        $score += min(($counts['contact_intent']    ?? 0) * 20, 40); // NEW — high value
        $score += min(($counts['return_to_listing'] ?? 0) * 15, 30); // NEW
        $score += min(($counts['share_property']    ?? 0) * 10, 20); // NEW
        $score += min(($counts['search_click']      ?? 0) * 4,  15);
        if ($filter) $score += 10;
        if ($calc) {
            $score += 10;
            $score += (int) round((($calc['signal_strength'] ?? 0) / 100) * 5);
        }
        return min($score, 100);
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
            'has_history'      => false,
            'is_cold_start'    => false,
            'intent_score'     => 0,
            'cities'           => [],
            'types'            => [],
            'listing_type'     => null,
            'price'            => null,
            'bedrooms'         => null,
            'heat_centroid'    => null,
            'seen_ids'         => [],
            'budget'           => null,
            'signal_counts'    => [],
            'strip_feedback'   => [],
            'negative_types'   => [],
            'session_context'  => [],
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
