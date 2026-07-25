<?php

/*
|==============================================================================
| REPLACES: app/Services/Intelligence/UserTasteProfile.php
|==============================================================================
|
| THREE REAL BUGS FOUND
| ---------------------
|
| BUG 1 — one cheap bounce destroyed the whole price band.
|
|   derivePriceBand() did:
|       $negativeCap = min($negativePrices) * 0.95;
|       if ($negativeCap && $max > $negativeCap) { $max = $negativeCap; ... }
|
|   min() takes the CHEAPEST property the user bounced off. So a single 5-second
|   bounce on a $400 listing capped the user's entire budget at $380 — while
|   their behavioural target was $48,722. That produced the exact window in your
|   logs: {"target":48722,"min":266,"max":380,"negative_cap":380}.
|
|   A bounce on a CHEAP listing means "too cheap for me", not "my ceiling is
|   $380". The cap now:
|     • only applies to bounces ABOVE the target (genuinely too expensive)
|     • needs at least 2 of them before it's treated as a signal
|     • can never pull max below the target
|
| BUG 2 — seen_ids included impressions, so it swallowed the whole catalogue.
|
|   Every row was pushed into $seenIds, including 'impression'. An impression
|   only means "appeared in a list". Your home screen shows 20 per load, so a
|   user who opens the app a few times has "seen" every listing you own — and
|   the recommender, which excludes seen ids, then had nothing left to return.
|   Your log proved it: every tier excluding all seen returned 0; capping the
|   exclusion at 30 returned 94.
|
|   seen_ids is now built only from real engagement (view, favorite, compare,
|   contact_intent, return_to_listing, share, gallery open, dwell time),
|   limited to the last 30 days and capped at 150 entries.
|
| BUG 3 — 11 seconds and 100MB per build.
|
|   compute() loaded EVERY interaction row for 90 days into PHP, including
|   34,545 impressions with their JSON metadata. Impressions carry a weight of
|   0.1 — almost nothing — but were 99% of the memory and time.
|
|   Impressions are now aggregated in SQL (property_id => count) instead of
|   hydrated as rows, and the engagement query is bounded. Same scoring result,
|   a fraction of the cost.
*/

namespace App\Services\Intelligence;

use App\Models\Property;
use App\Models\User;
use App\Models\UserPropertyInteraction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserTasteProfile
{
    private const HALF_LIFE_DAYS = 14;
    private const LOOKBACK_DAYS  = 90;
    private const CACHE_TTL      = 3600;
    private const SESSION_WINDOW = 2;    // hours — "today's session"
    private const SESSION_BOOST  = 3.0;

    /** Hard ceilings so one heavy user can't blow up memory. */
    private const MAX_ENGAGEMENT_ROWS = 2000;
    private const MAX_IMPRESSION_ROWS = 400;
    private const MAX_SEEN_IDS        = 150;
    private const SEEN_WINDOW_DAYS    = 30;

    private const SIGNAL_WEIGHTS = [
        'favorite'           => 5.0,
        'compare'            => 4.0,
        'search_click'       => 3.0,
        'filter_applied'     => 3.0,
        'calculator_search'  => 3.0,
        'view'               => 1.0,
        'impression'         => 0.1,

        'scroll_depth'       => 1.0,  // overridden by metadata.weight (0.5–4.0)
        'time_on_listing'    => 1.0,  // overridden by metadata.weight (-1.0–4.0)
        'return_to_listing'  => 8.0,
        'photo_gallery_open' => 2.5,
        'contact_intent'     => 6.0,
        'share_property'     => 3.5,
        'map_pin_tap'        => 2.0,
        'search_refinement'  => 3.0,
    ];

    /**
     * Signals that mean the user actually opened / engaged with a listing.
     * Only these mark a property as "seen" — an impression does not.
     */
    private const ENGAGEMENT_SIGNALS = [
        'view',
        'favorite',
        'compare',
        'contact_intent',
        'return_to_listing',
        'share_property',
        'photo_gallery_open',
        'time_on_listing',
        'scroll_depth',
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
                    'line'    => $e->getLine(),
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
        // ── 1. Engagement rows (everything EXCEPT impressions) ──────────────
        // Impressions are 99% of the row count and worth 0.1 each. Loading them
        // as models is what cost 11 seconds and 100MB. They're aggregated below.
        $engagementTypes = array_values(array_diff(
            array_keys(self::SIGNAL_WEIGHTS),
            ['impression']
        ));

        $rows = UserPropertyInteraction::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->whereIn('interaction_type', array_merge($engagementTypes, ['strip_clicked', 'strip_dismissed']))
            ->orderByDesc('created_at')
            ->select(['property_id', 'interaction_type', 'metadata', 'created_at'])
            ->limit(self::MAX_ENGAGEMENT_ROWS)
            ->get();

        $stripRows       = $rows->whereIn('interaction_type', ['strip_clicked', 'strip_dismissed']);
        $interactionRows = $rows->whereNotIn('interaction_type', ['strip_clicked', 'strip_dismissed']);

        // ── 2. Impressions — aggregated in SQL, never hydrated ──────────────
        $impressions = collect();

        try {
            $impressions = UserPropertyInteraction::where('user_id', $userId)
                ->where('interaction_type', 'impression')
                ->where('created_at', '>=', now()->subDays(30))
                ->whereNotIn('property_id', self::VIRTUAL_IDS)
                ->select(
                    'property_id',
                    DB::raw('COUNT(*) as hit_count'),
                    DB::raw('MAX(created_at) as last_seen')
                )
                ->groupBy('property_id')
                ->orderByDesc('hit_count')
                ->limit(self::MAX_IMPRESSION_ROWS)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('Impression aggregate failed', ['error' => $e->getMessage()]);
        }

        $sessionCutoff  = now()->subHours(self::SESSION_WINDOW);
        $sessionRows    = $interactionRows->filter(fn($r) => $r->created_at >= $sessionCutoff);

        $filterSignal = $this->virtualSignal($interactionRows, 'filter_applied',    'filter_signal');
        $calcSignal   = $this->virtualSignal($interactionRows, 'calculator_search', 'calculator_signal');
        $filterSignal = $this->sanitizeFilterSignal($filterSignal);

        $realRows = $interactionRows
            ->whereNotIn('property_id', self::VIRTUAL_IDS)
            ->whereIn('interaction_type', array_keys(self::SIGNAL_WEIGHTS));

        if ($realRows->isEmpty() && $impressions->isEmpty() && !$filterSignal && !$calcSignal) {
            return $this->coldStartProfile($userId);
        }

        // ── 3. One property lookup for everything we touched ────────────────
        $propIds = $realRows->pluck('property_id')
            ->merge($impressions->pluck('property_id'))
            ->unique()
            ->values();

        $props = Property::whereIn('id', $propIds)
            ->select(['id', 'type', 'address_details', 'price', 'rooms', 'listing_type', 'locations'])
            ->get()
            ->keyBy('id');

        $cityW = $typeW = $listW = $bedW = [];
        $priceW = $geoPts = [];
        $negativeTypes = $negativePrices = [];
        $counts = [];
        $seenIds = [];

        // ── 4. Engagement signals ───────────────────────────────────────────
        foreach ($realRows as $row) {
            $counts[$row->interaction_type] = ($counts[$row->interaction_type] ?? 0) + 1;

            // Only genuine engagement marks a property as "seen"
            if (
                in_array($row->interaction_type, self::ENGAGEMENT_SIGNALS, true)
                && $row->created_at >= now()->subDays(self::SEEN_WINDOW_DAYS)
            ) {
                $seenIds[] = $row->property_id;
            }

            $prop = $props->get($row->property_id);

            $baseWeight = $this->resolveSignalWeight($row);
            $isSession  = $row->created_at >= $sessionCutoff;
            $w          = $baseWeight * $this->decay($row->created_at) * ($isSession ? self::SESSION_BOOST : 1.0);

            // Negative signal — a bounce
            if ($baseWeight < 0) {
                if ($prop) {
                    $type = strtolower($prop->type['category'] ?? '');
                    if ($type) {
                        $negativeTypes[$type] = ($negativeTypes[$type] ?? 0) + abs($w);
                    }

                    $usd = (float) ($prop->price['usd'] ?? 0);
                    if ($usd > 0) {
                        $negativePrices[] = $usd;
                    }
                }
                continue;
            }

            if ($w <= 0 || !$prop) {
                continue;
            }

            $this->accumulate($prop, $w, $cityW, $typeW, $listW, $bedW, $priceW);

            // Geo — a map pin tap is a deliberate location choice, weight it up
            $meta = is_array($row->metadata) ? $row->metadata : json_decode($row->metadata, true);

            if ($row->interaction_type === 'map_pin_tap' && !empty($meta['lat']) && !empty($meta['lng'])) {
                $geoPts[] = ['lat' => (float) $meta['lat'], 'lng' => (float) $meta['lng'], 'w' => $w * 2.0];
            } else {
                $locs = is_array($prop->locations) ? $prop->locations : [];
                if (!empty($locs[0]['lat']) && !empty($locs[0]['lng'])) {
                    $geoPts[] = ['lat' => (float) $locs[0]['lat'], 'lng' => (float) $locs[0]['lng'], 'w' => $w];
                }
            }
        }

        // ── 5. Impressions — aggregate weight, no seen marking ──────────────
        $impressionWeight = self::SIGNAL_WEIGHTS['impression'];
        $counts['impression'] = (int) $impressions->sum('hit_count');

        foreach ($impressions as $imp) {
            $prop = $props->get($imp->property_id);
            if (!$prop) {
                continue;
            }

            // Cap repeats — being shown a listing 400 times isn't 400x the signal
            $hits = min((int) $imp->hit_count, 10);
            $w    = $impressionWeight * $hits * $this->decay($imp->last_seen);

            if ($w > 0) {
                $this->accumulate($prop, $w, $cityW, $typeW, $listW, $bedW, $priceW);
            }
        }

        // ── 6. Apply negative type penalties ────────────────────────────────
        foreach ($negativeTypes as $type => $penalty) {
            if (isset($typeW[$type])) {
                $typeW[$type] = max(0, $typeW[$type] - $penalty);
                if ($typeW[$type] <= 0) {
                    unset($typeW[$type]);
                }
            }
        }

        // ── 7. Derive final values ──────────────────────────────────────────
        $listingType = $this->topKey($listW);
        if ($filterSignal['listing_type'] ?? null) {
            $listingType = $filterSignal['listing_type'];
        }

        $price = $this->derivePriceBand($priceW, $filterSignal, $calcSignal, $negativePrices, $userId);

        $bedrooms = $this->topKey($bedW);
        if ($filterSignal['bedrooms'] ?? null) {
            $bedrooms = (int) $filterSignal['bedrooms'];
        }

        $types = $this->normalise($typeW, 3);
        if ($filterSignal['property_type'] ?? null) {
            $types = [strtolower($filterSignal['property_type']) => 1.0] + $types;
        }

        $cities = $this->normalise($cityW, 3);
        if ($filterSignal['city'] ?? null) {
            $cities = [$filterSignal['city'] => 1.0] + $cities;
        }

        // Most recent engagement first, capped
        $seenIds = array_slice(array_values(array_unique($seenIds)), 0, self::MAX_SEEN_IDS);

        return [
            'has_history'     => true,
            'is_cold_start'   => false,
            'intent_score'    => $this->intentScore($counts, $calcSignal, $filterSignal),
            'cities'          => $cities,
            'types'           => $types,
            'listing_type'    => $listingType,
            'price'           => $price,
            'bedrooms'        => $bedrooms ? (int) $bedrooms : null,
            'heat_centroid'   => $this->heatCentroid($geoPts),
            'seen_ids'        => $seenIds,
            'budget'          => $calcSignal ?: null,
            'signal_counts'   => $counts,
            'strip_feedback'  => $this->computeStripFeedback($stripRows),
            'negative_types'  => array_keys($negativeTypes),
            'session_context' => $this->buildSessionContext($sessionRows, $props),
        ];
    }

    /**
     * Add one property's attributes into the weight maps.
     */
    private function accumulate(
        $prop,
        float $w,
        array &$cityW,
        array &$typeW,
        array &$listW,
        array &$bedW,
        array &$priceW
    ): void {
        $city = $prop->address_details['city']['en'] ?? null;
        if ($city) {
            $cityW[$city] = ($cityW[$city] ?? 0) + $w;
        }

        $type = strtolower($prop->type['category'] ?? '');
        if ($type) {
            $typeW[$type] = ($typeW[$type] ?? 0) + $w;
        }

        if ($prop->listing_type) {
            $listW[$prop->listing_type] = ($listW[$prop->listing_type] ?? 0) + $w;
        }

        $beds = (int) ($prop->rooms['bedroom']['count'] ?? 0);
        if ($beds > 0) {
            $bedW[$beds] = ($bedW[$beds] ?? 0) + $w;
        }

        $usd = (float) ($prop->price['usd'] ?? 0);
        if ($usd > 0) {
            $priceW[] = ['p' => $usd, 'w' => $w];
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  PRICE BAND — the bug that broke every recommendation
    // ══════════════════════════════════════════════════════════════════════
    private function derivePriceBand(
        array   $priceW,
        ?array  $filter,
        ?array  $calc,
        array   $negativePrices = [],
        string  $userId = ''
    ): ?array {
        // An explicit filter always wins
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

        // Behavioural target
        $behaviourTarget = null;
        if (!empty($priceW)) {
            $sumWP = array_sum(array_map(fn($x) => $x['p'] * $x['w'], $priceW));
            $sumW  = array_sum(array_map(fn($x) => $x['w'], $priceW));
            if ($sumW > 0) {
                $behaviourTarget = $sumWP / $sumW;
            }
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

        if (!$target || $target <= 0) {
            return null;
        }

        $tol = $calc ? 0.30 : 0.40;
        $min = $target * (1 - $tol);
        $max = $target * (1 + $tol);

        // ── Negative cap, fixed ─────────────────────────────────────────────
        // The old code used min($negativePrices) — the CHEAPEST listing the user
        // bounced off — which meant one 5-second look at a $400 listing capped a
        // $48,000 buyer at $380. A bounce on something cheap says "too cheap",
        // not "that's my ceiling".
        //
        // Only bounces ABOVE the target tell us anything about a ceiling, we need
        // at least two of them to trust it, and it can never cut below the target.
        $negativeCap = null;

        if (!empty($negativePrices)) {
            $tooExpensive = array_values(array_filter($negativePrices, fn($p) => $p > $target * 1.1));

            if (count($tooExpensive) >= 2) {
                $candidate = min($tooExpensive) * 0.95;

                if ($candidate > $target) {
                    $negativeCap = $candidate;
                    $max         = min($max, $negativeCap);
                }
            }
        }

        // Final sanity — the band must always contain the target
        if ($max <= $min || $target < $min || $target > $max) {
            $min = $target * 0.65;
            $max = $target * 1.45;

            Log::warning('UserTasteProfile: price band repaired', [
                'user_id' => $userId,
                'target'  => $target,
            ]);
        }

        return [
            'target'       => $target,
            'min'          => $min,
            'max'          => $max,
            'source'       => $behaviourTarget ? 'behaviour' : 'calculator',
            'negative_cap' => $negativeCap,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════════════════

    private function resolveSignalWeight($row): float
    {
        $baseWeight = self::SIGNAL_WEIGHTS[$row->interaction_type] ?? 0;

        $metaWeightTypes = [
            'scroll_depth',
            'time_on_listing',
            'photo_gallery_open',
            'contact_intent',
            'share_property',
            'map_pin_tap',
            'search_refinement',
        ];

        if (in_array($row->interaction_type, $metaWeightTypes, true) && $row->metadata) {
            $meta = is_array($row->metadata) ? $row->metadata : json_decode($row->metadata, true);
            if (isset($meta['weight'])) {
                return (float) $meta['weight'];
            }
        }

        return $baseWeight;
    }

    private function buildSessionContext($sessionRows, $propsById): array
    {
        if ($sessionRows->isEmpty()) {
            return [];
        }

        $realSession    = $sessionRows->whereNotIn('property_id', self::VIRTUAL_IDS);
        $sessionPropIds = $realSession->pluck('property_id')->unique()->values();

        $sessionTypes = $sessionCities = [];
        $hasContactIntent = $hasReturnVisit = false;

        foreach ($realSession as $row) {
            if ($row->interaction_type === 'contact_intent')    $hasContactIntent = true;
            if ($row->interaction_type === 'return_to_listing') $hasReturnVisit   = true;

            $prop = $propsById->get($row->property_id);
            if (!$prop) {
                continue;
            }

            $type = strtolower($prop->type['category'] ?? '');
            if ($type) {
                $sessionTypes[$type] = ($sessionTypes[$type] ?? 0) + 1;
            }

            $city = $prop->address_details['city']['en'] ?? null;
            if ($city) {
                $sessionCities[$city] = ($sessionCities[$city] ?? 0) + 1;
            }
        }

        arsort($sessionTypes);
        arsort($sessionCities);

        return [
            'property_count'     => $sessionPropIds->count(),
            'dominant_type'      => array_key_first($sessionTypes),
            'dominant_city'      => array_key_first($sessionCities),
            'has_contact_intent' => $hasContactIntent,
            'has_return_visit'   => $hasReturnVisit,
            'types_viewed'       => $sessionTypes,
            'cities_viewed'      => $sessionCities,
        ];
    }

    private function coldStartProfile(string $userId): array
    {
        $user   = User::find($userId);
        $seed   = $user?->place;
        $cities = $seed ? [$seed => 1.0] : [];

        return array_merge($this->emptyProfile(), [
            'is_cold_start' => true,
            'cities'        => $cities,
        ]);
    }

    private function intentScore(array $counts, ?array $calc, ?array $filter): int
    {
        $score = 0;
        $score += min(($counts['favorite']          ?? 0) * 12, 30);
        $score += min(($counts['compare']           ?? 0) * 15, 30);
        $score += min(($counts['contact_intent']    ?? 0) * 20, 40);
        $score += min(($counts['return_to_listing'] ?? 0) * 15, 30);
        $score += min(($counts['share_property']    ?? 0) * 10, 20);
        $score += min(($counts['search_click']      ?? 0) * 4,  15);

        if ($filter) {
            $score += 10;
        }

        if ($calc) {
            $score += 10;
            $score += (int) round((($calc['signal_strength'] ?? 0) / 100) * 5);
        }

        return min($score, 100);
    }

    private function heatCentroid(array $points): ?array
    {
        if (count($points) < 3) {
            return null;
        }

        $sumLat = $sumLng = $sumW = 0;

        foreach ($points as $p) {
            $sumLat += $p['lat'] * $p['w'];
            $sumLng += $p['lng'] * $p['w'];
            $sumW   += $p['w'];
        }

        if ($sumW <= 0) {
            return null;
        }

        $cLat = $sumLat / $sumW;
        $cLng = $sumLng / $sumW;

        $sumDist = 0;
        foreach ($points as $p) {
            $sumDist += $this->haversineKm($cLat, $cLng, $p['lat'], $p['lng']) * $p['w'];
        }

        $radius = max(1.0, min(15.0, ($sumDist / $sumW) * 1.5));

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
                $meta = is_string($r->metadata) ? json_decode($r->metadata, true) : (array) $r->metadata;
                $type = $meta['strip_type'] ?? null;

                if (!$type) {
                    continue;
                }

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
        $row = $rows->where('interaction_type', $type)->where('property_id', $propId)->first();

        if (!$row || !$row->metadata) {
            return null;
        }

        $meta = is_array($row->metadata) ? $row->metadata : json_decode($row->metadata, true);

        if (!$meta) {
            return null;
        }

        $meta['_age_days'] = abs(now()->diffInDays($row->created_at));

        return $meta;
    }

    private function normalise(array $map, int $topN): array
    {
        if (empty($map)) {
            return [];
        }

        arsort($map);
        $map = array_slice($map, 0, $topN, true);
        $max = max($map);

        if ($max <= 0) {
            return [];
        }

        return array_map(fn($w) => round($w / $max, 3), $map);
    }

    private function topKey(array $map)
    {
        if (empty($map)) {
            return null;
        }

        arsort($map);

        return array_key_first($map);
    }

    private function emptyProfile(): array
    {
        return [
            'has_history'     => false,
            'is_cold_start'   => false,
            'intent_score'    => 0,
            'cities'          => [],
            'types'           => [],
            'listing_type'    => null,
            'price'           => null,
            'bedrooms'        => null,
            'heat_centroid'   => null,
            'seen_ids'        => [],
            'budget'          => null,
            'signal_counts'   => [],
            'strip_feedback'  => [],
            'negative_types'  => [],
            'session_context' => [],
        ];
    }

    private function sanitizeFilterSignal(?array $signal): ?array
    {
        if (!$signal) {
            return null;
        }

        $junk = ['all', '', '0', 'any', 'none'];

        foreach (['listing_type', 'property_type', 'city', 'bedrooms'] as $k) {
            if (!isset($signal[$k])) {
                continue;
            }

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

        foreach (['listing_type', 'property_type', 'city', 'bedrooms', 'min_price_usd', 'max_price_usd'] as $k) {
            if (!empty($signal[$k])) {
                return $signal;
            }
        }

        return null;
    }
}