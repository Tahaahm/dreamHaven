<?php

namespace App\Services;

use App\Models\Property;
use App\Models\UserPropertyInteraction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SmartStripService — v2 with 5 new strip types
 *
 * NEW strip types added:
 *   deep_interest      — scrolled 80%+ on 3+ properties of same type in session
 *   re_engagement      — returned to same property 2+ times
 *   about_to_contact   — contact_intent + return_to_listing in session
 *   price_sensitivity  — behavioural price ceiling detected from bounce signals
 *   hot_neighbourhood  — 5+ map_pin_taps in same small area
 *
 * Priority order (first qualifying strip wins):
 *   1. about_to_contact  (highest conversion value)
 *   2. budget_match
 *   3. re_engagement
 *   4. deep_interest
 *   5. price_sensitivity
 *   6. hot_neighbourhood
 *   7. resume_search
 *   8. area_focus
 *   9. new_matches
 *  10. returning_visitor
 */
class SmartStripService
{
    private const CACHE_TTL      = 3600;
    private const SESSION_WINDOW = 24;

    public function getStrip(string $userId, string $language = 'en'): ?array
    {
        $cacheKey = "smart_strip_{$userId}_{$language}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $language) {
            try {
                $signals = $this->loadSignals($userId);

                Log::info('SmartStrip: signals loaded', [
                    'user_id'               => $userId,
                    'has_filter_signal'     => !empty($signals['filterSignal']),
                    'has_calc_signal'       => !empty($signals['calcSignal']),
                    'recently_viewed_count' => count($signals['recentlyViewedIds']),
                    'has_contact_intent'    => $signals['hasContactIntent'],
                    'has_return_visit'      => $signals['hasReturnVisit'],
                    'deep_interest_count'   => count($signals['deepInterestProps'] ?? []),
                    'hot_neighbourhood'     => $signals['hotNeighbourhood'] !== null,
                ]);

                // Priority order — first match wins
                $stripTypes = [
                    'about_to_contact'  => fn() => $this->tryAboutToContact($userId, $signals, $language),
                    'budget_match'      => fn() => $this->tryBudgetMatch($userId, $signals, $language),
                    're_engagement'     => fn() => $this->tryReEngagement($userId, $signals, $language),
                    'deep_interest'     => fn() => $this->tryDeepInterest($userId, $signals, $language),
                    'price_sensitivity' => fn() => $this->tryPriceSensitivity($userId, $signals, $language),
                    'hot_neighbourhood' => fn() => $this->tryHotNeighbourhood($userId, $signals, $language),
                    'resume_search'     => fn() => $this->tryResumeSearch($userId, $signals, $language),
                    'area_focus'        => fn() => $this->tryAreaFocus($userId, $signals, $language),
                    'new_matches'       => fn() => $this->tryNewMatches($userId, $signals, $language),
                    'returning_visitor' => fn() => $this->tryReturningVisitor($userId, $signals, $language),
                ];

                $strip = null;
                foreach ($stripTypes as $typeName => $resolver) {
                    $candidate = $resolver();
                    if ($candidate === null) {
                        Log::info("SmartStrip: {$typeName} skipped", ['user_id' => $userId]);
                        continue;
                    }
                    Log::info("SmartStrip: {$typeName} resolved", [
                        'user_id'    => $userId,
                        'count'      => $candidate['count']      ?? null,
                        'confidence' => $candidate['confidence'] ?? null,
                    ]);
                    $strip = $candidate;
                    break;
                }

                if (!$strip || ($strip['confidence'] ?? 0) < 0.50) {
                    return null;
                }

                Log::info('SmartStrip: strip selected', [
                    'user_id' => $userId,
                    'type'    => $strip['type'],
                    'intent'  => $strip['intent'],
                ]);

                return $strip;
            } catch (\Throwable $e) {
                Log::warning('SmartStrip failed (non-fatal)', [
                    'user_id' => $userId,
                    'error'   => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    public function invalidate(string $userId): void
    {
        foreach (['en', 'ar', 'ku'] as $lang) {
            Cache::forget("smart_strip_{$userId}_{$lang}");
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    //  SIGNAL LOADER — extended with new signal types
    // ──────────────────────────────────────────────────────────────────────
    private function loadSignals(string $userId): array
    {
        $virtualIds = ['calculator_signal', 'filter_signal', 'search_signal', 'search_signal_latest'];

        $recentRows = UserPropertyInteraction::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->get();

        // Existing signals
        $filterRow = $recentRows
            ->where('interaction_type', 'filter_applied')
            ->where('property_id', 'filter_signal')
            ->where('created_at', '>=', now()->subHours(self::SESSION_WINDOW))
            ->first();

        $filterSignal = null;
        if ($filterRow?->metadata) {
            $meta = is_array($filterRow->metadata)
                ? $filterRow->metadata
                : json_decode($filterRow->metadata, true);
            $filterSignal = $meta;
        }

        $calcRow = UserPropertyInteraction::where('user_id', $userId)
            ->where('interaction_type', 'calculator_search')
            ->where('property_id', 'calculator_signal')
            ->where('created_at', '>=', now()->subDays(90))
            ->latest()->first();

        $calcSignal = null;
        if ($calcRow?->metadata) {
            $meta = is_array($calcRow->metadata)
                ? $calcRow->metadata
                : json_decode($calcRow->metadata, true);
            $calcSignal = $meta;
        }

        $recentlyViewedIds = $recentRows
            ->where('interaction_type', 'view')
            ->whereNotIn('property_id', $virtualIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->pluck('property_id')->unique()->values()->toArray();

        $favoriteIds = $recentRows
            ->where('interaction_type', 'favorite')
            ->whereNotIn('property_id', $virtualIds)
            ->pluck('property_id')->unique()->values()->toArray();

        $compareIds = $recentRows
            ->where('interaction_type', 'compare')
            ->whereNotIn('property_id', $virtualIds)
            ->pluck('property_id')->unique()->values()->toArray();

        $user           = User::find($userId);
        $lastSeenAt     = $user?->last_activity_at ?? $user?->updated_at;
        $daysSinceVisit = $lastSeenAt
            ? (int) abs(now()->diffInDays($lastSeenAt))
            : 999;

        // ── NEW signal extractions ─────────────────────────────────────────

        // contact_intent in session
        $hasContactIntent = $recentRows
            ->where('interaction_type', 'contact_intent')
            ->where('created_at', '>=', now()->subHours(self::SESSION_WINDOW))
            ->isNotEmpty();

        // return_to_listing in session
        $hasReturnVisit = $recentRows
            ->where('interaction_type', 'return_to_listing')
            ->where('created_at', '>=', now()->subHours(self::SESSION_WINDOW))
            ->isNotEmpty();

        // Most recent property that had return_to_listing
        $returnedPropertyId = $recentRows
            ->where('interaction_type', 'return_to_listing')
            ->where('created_at', '>=', now()->subHours(self::SESSION_WINDOW))
            ->first()?->property_id;

        // Deep interest: scroll_depth >= 80% properties in session
        $deepScrollRows = $recentRows
            ->where('interaction_type', 'scroll_depth')
            ->where('created_at', '>=', now()->subHours(self::SESSION_WINDOW))
            ->filter(function ($row) {
                $meta = is_array($row->metadata)
                    ? $row->metadata
                    : json_decode($row->metadata, true);
                return (int) ($meta['scroll_percent'] ?? 0) >= 80;
            });

        // Bounce signals — properties that user left quickly
        $bouncePropertyIds = $recentRows
            ->where('interaction_type', 'time_on_listing')
            ->filter(function ($row) {
                $meta = is_array($row->metadata)
                    ? $row->metadata
                    : json_decode($row->metadata, true);
                return ($meta['sentiment'] ?? '') === 'bounce';
            })
            ->pluck('property_id')->unique()->values()->toArray();

        // Price sensitivity: prices of bounced properties
        $bouncePrices = [];
        if (!empty($bouncePropertyIds)) {
            $bounceProps = \App\Models\Property::whereIn('id', $bouncePropertyIds)
                ->select('id', 'price')
                ->get();
            foreach ($bounceProps as $bp) {
                $usd = is_array($bp->price) ? (float) ($bp->price['usd'] ?? 0) : 0;
                if ($usd > 0) $bouncePrices[] = $usd;
            }
        }

        // Hot neighbourhood: map_pin_taps clustered in small area
        $mapPinTaps = $recentRows
            ->where('interaction_type', 'map_pin_tap')
            ->where('created_at', '>=', now()->subDays(7));

        $hotNeighbourhood = $this->detectHotNeighbourhood($mapPinTaps);

        // Deep interest props (properties with 80%+ scroll in session)
        $deepInterestProps = $deepScrollRows->pluck('property_id')->unique()->values()->toArray();
        $searchSignal = null; // not used in v2 — kept for compact() compatibility

        return compact(
            'filterSignal',
            'calcSignal',
            'recentlyViewedIds',
            'favoriteIds',
            'compareIds',
            'daysSinceVisit',
            'user',
            'hasContactIntent',
            'hasReturnVisit',
            'returnedPropertyId',
            'deepInterestProps',
            'bouncePrices',
            'hotNeighbourhood',
            'searchSignal',  // may be null
            'bouncePropertyIds'
        );
    }

    // ──────────────────────────────────────────────────────────────────────
    //  NEW STRIP TYPE 1: ABOUT_TO_CONTACT
    //  "Looks like you're close to a decision on this villa.
    //   The agent replies in under 2 hours."
    //  Fires when: contact_intent + return_to_listing in same session
    // ──────────────────────────────────────────────────────────────────────
    private function tryAboutToContact(string $userId, array $signals, string $lang): ?array
    {
        if (!$signals['hasContactIntent'] || !$signals['hasReturnVisit']) {
            return null;
        }

        $propertyId = $signals['returnedPropertyId'];
        if (!$propertyId) return null;

        $property = Property::find($propertyId);
        if (!$property || !$property->is_active) return null;

        $headline = match ($lang) {
            'ar' => 'يبدو أنك قريب من قرار بشأن هذا العقار',
            'ku' => 'وا دەکات نزیک بە بڕیاردانی ئەم خانووەیی',
            default => "Looks like you're close on this {$property->type['category']}",
        };

        $subline = match ($lang) {
            'ar' => 'الوكيل يرد خلال ساعتين. تواصل الآن.',
            'ku' => 'نوێنەرەکە لە ماوەی ٢ کاتژمێردا وەڵام دەداتەوە.',
            default => 'The agent typically replies within 2 hours.',
        };

        return [
            'type'       => 'about_to_contact',
            'intent'     => 'decision_stage',
            'confidence' => 0.92,
            'icon'       => 'phone',
            'headline'   => $headline,
            'subline'    => $subline,
            'params'     => [
                'property_id' => $propertyId,
                'property'    => [
                    'id'    => $property->id,
                    'name'  => $property->name,
                    'price' => $property->price,
                    'image' => is_array($property->images) ? ($property->images[0] ?? null) : null,
                ],
            ],
            'filters'    => [],
            'count'      => 1,
            'properties' => $this->transformProperties(collect([$property]), $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  NEW STRIP TYPE 2: RE_ENGAGEMENT
    //  "You keep coming back to this villa — want a price drop alert?"
    //  Fires when: return_to_listing detected in recent history
    // ──────────────────────────────────────────────────────────────────────
    private function tryReEngagement(string $userId, array $signals, string $lang): ?array
    {
        if (!$signals['hasReturnVisit'] || !$signals['returnedPropertyId']) {
            return null;
        }

        $property = Property::find($signals['returnedPropertyId']);
        if (!$property || !$property->is_active) return null;

        $typeName = ucfirst($property->type['category'] ?? 'property');

        $headline = match ($lang) {
            'ar' => "عدت مرة أخرى لهذا {$typeName}",
            'ku' => "دووبارەت گەڕایتەوە ئەم {$typeName}ەکە",
            default => "You keep coming back to this {$typeName}",
        };

        $subline = match ($lang) {
            'ar' => 'هل تريد إشعاراً إذا انخفض السعر؟',
            'ku' => 'دەتەوێت ئاگادارت بکەینەوە ئەگەر نرخ کەم بووە؟',
            default => 'Want an alert if the price drops?',
        };

        return [
            'type'       => 're_engagement',
            'intent'     => 'strong_consideration',
            'confidence' => 0.88,
            'icon'       => 'refresh',
            'headline'   => $headline,
            'subline'    => $subline,
            'params'     => [
                'property_id' => $property->id,
            ],
            'filters'    => [],
            'count'      => 1,
            'properties' => $this->transformProperties(collect([$property]), $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  NEW STRIP TYPE 3: DEEP_INTEREST
    //  "You've been deep in 3-bed apartments tonight — here are 4 more."
    //  Fires when: 3+ properties with 80%+ scroll depth, same type, in session
    // ──────────────────────────────────────────────────────────────────────
    private function tryDeepInterest(string $userId, array $signals, string $lang): ?array
    {
        $deepIds = $signals['deepInterestProps'] ?? [];
        if (count($deepIds) < 3) return null;

        // Get the dominant type from deep-scrolled properties
        $deepProps = Property::whereIn('id', $deepIds)
            ->select('id', 'type', 'address_details', 'listing_type')
            ->get();

        if ($deepProps->isEmpty()) return null;

        // Find dominant type
        $typeCounts = [];
        foreach ($deepProps as $p) {
            $type = strtolower($p->type['category'] ?? '');
            if ($type) $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
        }
        arsort($typeCounts);
        $dominantType = array_key_first($typeCounts);

        // Require at least 3 of the same type
        if (($typeCounts[$dominantType] ?? 0) < 3) return null;

        $dominantCity = null;
        $cityCounts = [];
        foreach ($deepProps as $p) {
            $city = $p->address_details['city']['en'] ?? null;
            if ($city) $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
        }
        arsort($cityCounts);
        $dominantCity = array_key_first($cityCounts);

        // Find MORE properties of same type not yet seen
        $unseenQuery = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereNotIn('id', $deepIds)
            ->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
                [$dominantType]
            );

        if ($dominantCity) {
            $unseenQuery->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                [strtolower($dominantCity)]
            );
        }

        $count = $unseenQuery->count();
        if ($count === 0) return null;

        $topProps = $unseenQuery->orderByDesc('created_at')->limit(5)->get();

        $typeDisplay = ucfirst($dominantType);
        $headline = match ($lang) {
            'ar' => "أنت تتصفح {$typeDisplay} بعمق — هنا {$count} أكثر",
            'ku' => "قووڵی تەماشای {$typeDisplay} دەکەیت — {$count} تریش هەیە",
            default => "You've been deep in {$typeDisplay}s — {$count} more here",
        };

        $subline = match ($lang) {
            'ar' => $dominantCity ? "في {$dominantCity} — مطابق لبحثك الليلة" : "مطابق لبحثك الليلة",
            'ku' => $dominantCity ? "لە {$dominantCity} — لەگەڵ گەڕانی ئەمشەوت دەگونجێت" : "لەگەڵ گەڕانی ئەمشەوت دەگونجێت",
            default => $dominantCity ? "In {$dominantCity} — matching tonight's search" : "Matching your session",
        };

        return [
            'type'       => 'deep_interest',
            'intent'     => 'active_searcher',
            'confidence' => min(0.60 + (count($deepIds) * 0.08), 0.90),
            'icon'       => 'eye',
            'headline'   => $headline,
            'subline'    => $subline,
            'params'     => [
                'dominant_type'  => $dominantType,
                'dominant_city'  => $dominantCity,
                'deep_count'     => count($deepIds),
                'unseen_count'   => $count,
            ],
            'filters' => array_filter([
                'property_type' => $dominantType,
                'city'          => $dominantCity,
            ]),
            'count'      => $count,
            'properties' => $this->transformProperties($topProps, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  NEW STRIP TYPE 4: PRICE_SENSITIVITY
    //  "Staying under $85K? These 6 new apartments fit exactly."
    //  Fires when: user has bounce signals above a consistent price point
    // ──────────────────────────────────────────────────────────────────────
    private function tryPriceSensitivity(string $userId, array $signals, string $lang): ?array
    {
        $bouncePrices = $signals['bouncePrices'] ?? [];
        if (count($bouncePrices) < 2) return null;

        // Real price ceiling = slightly below cheapest bounced price
        $ceiling = min($bouncePrices) * 0.95;
        if ($ceiling <= 0) return null;

        // Also need to know what they DO like — use filter signal or viewed props
        $filterSignal = $signals['filterSignal'];
        $listingType  = $filterSignal['listing_type'] ?? null;

        $query = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) <= ?",
                [$ceiling]
            )
            ->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) > 0"
            )
            ->where('created_at', '>=', now()->subDays(30));

        if ($listingType) $query->where('listing_type', $listingType);

        $count = $query->count();
        if ($count === 0) return null;

        $topProps = $query->orderByDesc('created_at')->limit(5)->get();

        $ceilingFmt = '$' . number_format((int) $ceiling);

        $headline = match ($lang) {
            'ar' => "تبقى تحت {$ceilingFmt}؟ هذه {$count} عقارات تناسبك",
            'ku' => "لەژێر {$ceilingFmt} دەمێنیتەوە؟ ئەم {$count} خانووە بۆ تۆیە",
            default => "Staying under {$ceilingFmt}? {$count} listings fit exactly",
        };

        $subline = match ($lang) {
            'ar' => 'بناءً على سجل بحثك وعروض الأسعار',
            'ku' => 'بە پێی مێژووی گەڕانت و نرخەکانت',
            default => 'Based on your search history and price behavior',
        };

        return [
            'type'       => 'price_sensitivity',
            'intent'     => 'budget_constrained',
            'confidence' => min(0.65 + (count($bouncePrices) * 0.05), 0.88),
            'icon'       => 'wallet',
            'headline'   => $headline,
            'subline'    => $subline,
            'params'     => [
                'price_ceiling'  => (int) $ceiling,
                'ceiling_fmt'    => $ceilingFmt,
                'bounce_count'   => count($bouncePrices),
                'count'          => $count,
            ],
            'filters' => array_filter([
                'max_price'    => (int) $ceiling,
                'listing_type' => $listingType,
            ]),
            'count'      => $count,
            'properties' => $this->transformProperties($topProps, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  NEW STRIP TYPE 5: HOT_NEIGHBOURHOOD
    //  "You keep looking at Dream City — 8 new listings just dropped there."
    //  Fires when: 5+ map_pin_taps in same small geographic cluster
    // ──────────────────────────────────────────────────────────────────────
    private function tryHotNeighbourhood(string $userId, array $signals, string $lang): ?array
    {
        $neighbourhood = $signals['hotNeighbourhood'];
        if (!$neighbourhood) return null;

        $lat    = $neighbourhood['lat'];
        $lng    = $neighbourhood['lng'];
        $radius = 1.5; // km — tighter than city, neighbourhood level

        $query = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('created_at', '>=', now()->subDays(14)) // fresh only
            ->whereRaw(
                "(6371 * acos(LEAST(1, cos(radians(?)) *
                    cos(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lat')) AS DECIMAL(10,6)))) *
                    cos(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lng')) AS DECIMAL(10,6))) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(CAST(JSON_UNQUOTE(JSON_EXTRACT(locations, '$[0].lat')) AS DECIMAL(10,6))))
                ))) <= ?",
                [$lat, $lng, $lat, $radius]
            );

        $count = $query->count();
        if ($count === 0) return null;

        $topProps = $query->orderByDesc('created_at')->limit(5)->get();

        $areaName = $neighbourhood['area_name'] ?? null;

        $headline = match ($lang) {
            'ar' => $areaName
                ? "تستمر في البحث في {$areaName} — {$count} قوائم جديدة للتو"
                : "{$count} عقارات جديدة في المنطقة التي تراقبها",
            'ku' => $areaName
                ? "بەردەوامت لە {$areaName} دەگەڕێیت — {$count} لیستی نوێ"
                : "{$count} خانووی نوێ لە ناوچەکەی تۆ",
            default => $areaName
                ? "You keep looking at {$areaName} — {$count} new listings just dropped"
                : "{$count} new listings in the area you keep exploring",
        };

        $subline = match ($lang) {
            'ar' => 'بناءً على نقرات خريطتك',
            'ku' => 'بەپێی کرتەکردنەکانت لەسەر نەخشەکە',
            default => 'Based on where you keep tapping on the map',
        };

        return [
            'type'       => 'hot_neighbourhood',
            'intent'     => 'location_obsessed',
            'confidence' => min(0.60 + ($neighbourhood['tap_count'] * 0.04), 0.88),
            'icon'       => 'map_pin',
            'headline'   => $headline,
            'subline'    => $subline,
            'params'     => [
                'lat'       => $lat,
                'lng'       => $lng,
                'radius_km' => $radius,
                'tap_count' => $neighbourhood['tap_count'],
                'area_name' => $areaName,
                'count'     => $count,
            ],
            'filters'    => [],
            'count'      => $count,
            'properties' => $this->transformProperties($topProps, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  EXISTING strip types (unchanged from v1, kept for completeness)
    // ──────────────────────────────────────────────────────────────────────

    private function tryBudgetMatch(string $userId, array $signals, string $lang): ?array
    {
        $calc = $signals['calcSignal'];
        if (!$calc || empty($calc['budget_min_usd']) || empty($calc['budget_max_usd'])) {
            return null;
        }

        $minUsd = (float) $calc['budget_min_usd'];
        $maxUsd = (float) $calc['budget_max_usd'];

        $query = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('listing_type', 'sell')
            ->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) BETWEEN ? AND ?",
                [$minUsd, $maxUsd]
            )
            ->orderByDesc('created_at');

        $count = $query->count();
        if ($count === 0) return null;

        $topProperties = $query->with('owner')->limit(5)->get();
        $signalStrength = (int) ($calc['signal_strength'] ?? 50);
        $confidence     = min(0.60 + ($signalStrength / 100 * 0.30), 0.90);

        $minFmt = '$' . number_format((int) $minUsd);
        $maxFmt = '$' . number_format((int) $maxUsd);

        return [
            'type'       => 'budget_match',
            'intent'     => 'active_buyer',
            'confidence' => $confidence,
            'icon'       => 'wallet',
            'headline'   => 'budget_match_headline',
            'subline'    => 'budget_match_subline',
            'params'     => [
                'min_price' => (int) $minUsd,
                'max_price' => (int) $maxUsd,
                'count'     => $count,
            ],
            'filters'    => ['listing_type' => 'sell', 'min_price' => (int) $minUsd, 'max_price' => (int) $maxUsd],
            'count'      => $count,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    private function tryResumeSearch(string $userId, array $signals, string $lang): ?array
    {
        $filter = $signals['filterSignal'];

        if ($filter) {
            $isJunk = (
                (empty($filter['listing_type'])  || strtolower($filter['listing_type'])  === 'all') &&
                (empty($filter['property_type']) || strtolower($filter['property_type']) === 'all') &&
                empty($filter['city']) &&
                empty($filter['min_price']) &&
                empty($filter['max_price']) &&
                (empty($filter['bedrooms']) || (int) $filter['bedrooms'] === 0)
            );
            if ($isJunk) $filter = null;
        }

        if (!$filter) return null;

        $query = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented']);

        if (!empty($filter['listing_type']) && strtolower($filter['listing_type']) !== 'all')
            $query->where('listing_type', $filter['listing_type']);
        if (!empty($filter['city']))
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                [strtolower($filter['city'])]
            );
        if (!empty($filter['max_price_usd']))
            $query->whereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.usd')) AS DECIMAL(15,2)) <= ?",
                [(float) $filter['max_price_usd']]
            );

        $count = $query->count();
        if ($count === 0) return null;

        $topProperties = $query->orderByDesc('created_at')->with('owner')->limit(5)->get();

        return [
            'type'       => 'resume_search',
            'intent'     => 'active_searcher',
            'confidence' => 0.85,
            'icon'       => 'search',
            'headline'   => 'resume_search_headline',
            'subline'    => 'resume_search_subline',
            'params'     => ['total_count' => $count, 'label_parts' => []],
            'filters'    => $filter,
            'count'      => $count,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    private function tryAreaFocus(string $userId, array $signals, string $lang): ?array
    {
        if (count($signals['recentlyViewedIds']) < 3) return null;

        $viewedProperties = Property::whereIn('id', $signals['recentlyViewedIds'])
            ->whereNotNull('address_details')
            ->get(['id', 'address_details', 'listing_type']);

        if ($viewedProperties->isEmpty()) return null;

        $cityCounts = [];
        foreach ($viewedProperties as $prop) {
            $addr = is_array($prop->address_details) ? $prop->address_details : json_decode($prop->address_details, true);
            $city = $addr['city']['en'] ?? null;
            if ($city) $cityCounts[$city] = ($cityCounts[$city] ?? 0) + 1;
        }

        if (empty($cityCounts)) return null;
        arsort($cityCounts);
        $topCity      = array_key_first($cityCounts);
        $topCityCount = $cityCounts[$topCity];

        if ($topCityCount < 3) return null;

        $query = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) = ?",
                [strtolower($topCity)]
            )
            ->whereNotIn('id', $signals['recentlyViewedIds'])
            ->orderByDesc('created_at');

        $count = $query->count();
        if ($count === 0) return null;

        $topProperties = $query->with('owner')->limit(5)->get();

        return [
            'type'       => 'area_focus',
            'intent'     => 'location_focused',
            'confidence' => min(0.55 + ($topCityCount * 0.05), 0.85),
            'icon'       => 'location',
            'headline'   => 'area_focus_headline',
            'subline'    => 'area_focus_subline',
            'params'     => ['city' => $topCity, 'view_count' => $topCityCount, 'count' => $count],
            'filters'    => ['city' => $topCity],
            'count'      => $count,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    private function tryNewMatches(string $userId, array $signals, string $lang): ?array
    {
        $allIds = array_unique(array_merge(
            $signals['favoriteIds'],
            $signals['compareIds'],
            $signals['recentlyViewedIds']
        ));

        if (count($allIds) < 2) return null;

        $interacted = Property::whereIn('id', $allIds)->get(['id', 'listing_type', 'type', 'address_details', 'price']);
        if ($interacted->isEmpty()) return null;

        $dominantListing = $interacted->groupBy('listing_type')->map->count()->sortDesc()->keys()->first();
        $dominantType    = $interacted->groupBy(fn($p) => $p->type['category'] ?? '')->map->count()->sortDesc()->keys()->first();

        $lastActive = $signals['user']?->last_activity_at ?? now()->subDays(1);

        $query = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('created_at', '>=', $lastActive)
            ->whereNotIn('id', $allIds);

        if ($dominantListing) $query->where('listing_type', $dominantListing);
        if ($dominantType)    $query->whereRaw(
            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?",
            [strtolower($dominantType)]
        );

        $count = $query->count();
        if ($count === 0) return null;

        $topProperties = $query->orderByDesc('created_at')->with('owner')->limit(5)->get();

        return [
            'type'       => 'new_matches',
            'intent'     => 'casual_browser',
            'confidence' => min(0.55 + ($count * 0.02), 0.80),
            'icon'       => 'sparkles',
            'headline'   => 'new_matches_headline',
            'subline'    => 'new_matches_subline',
            'params'     => ['count' => $count],
            'filters'    => [],
            'count'      => $count,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    private function tryReturningVisitor(string $userId, array $signals, string $lang): ?array
    {
        if ($signals['daysSinceVisit'] < 2) return null;

        $lastActive = $signals['user']?->last_activity_at ?? now()->subDays($signals['daysSinceVisit']);

        $newCount = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('created_at', '>=', $lastActive)
            ->count();

        if ($newCount === 0) return null;

        $topProperties = Property::query()
            ->where('is_active', true)->where('published', true)
            ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
            ->where('created_at', '>=', $lastActive)
            ->orderByDesc('created_at')->with('owner')->limit(5)->get();

        return [
            'type'       => 'returning_visitor',
            'intent'     => 'returning',
            'confidence' => 0.55,
            'icon'       => 'wave',
            'headline'   => 'returning_visitor_headline',
            'subline'    => 'returning_visitor_subline',
            'params'     => ['days_away' => $signals['daysSinceVisit'], 'new_count' => $newCount],
            'filters'    => [],
            'count'      => $newCount,
            'properties' => $this->transformProperties($topProperties, $lang),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Detect if map pin taps cluster in a small area (≤ 1km radius).
     * Returns centroid + tap count if 5+ taps found, null otherwise.
     */
    private function detectHotNeighbourhood(Collection $mapPinTaps): ?array
    {
        if ($mapPinTaps->count() < 5) return null;

        $points = [];
        foreach ($mapPinTaps as $row) {
            $meta = is_array($row->metadata)
                ? $row->metadata
                : json_decode($row->metadata, true);
            if (!empty($meta['lat']) && !empty($meta['lng'])) {
                $points[] = ['lat' => (float) $meta['lat'], 'lng' => (float) $meta['lng']];
            }
        }

        if (count($points) < 5) return null;

        // Simple centroid
        $cLat = array_sum(array_column($points, 'lat')) / count($points);
        $cLng = array_sum(array_column($points, 'lng')) / count($points);

        // Check if most points are within 1km of centroid
        $within = array_filter($points, function ($p) use ($cLat, $cLng) {
            $dlat = deg2rad($p['lat'] - $cLat);
            $dlng = deg2rad($p['lng'] - $cLng);
            $a    = sin($dlat / 2) ** 2 + cos(deg2rad($cLat)) * cos(deg2rad($p['lat'])) * sin($dlng / 2) ** 2;
            $dist = 2 * 6371 * atan2(sqrt($a), sqrt(1 - $a));
            return $dist <= 1.0;
        });

        if (count($within) < 5) return null;

        return [
            'lat'       => round($cLat, 6),
            'lng'       => round($cLng, 6),
            'tap_count' => count($within),
            'area_name' => null, // Flutter can reverse-geocode if needed
        ];
    }

    private function transformProperties(Collection $properties, string $lang): array
    {
        return $properties->map(function ($property) use ($lang) {
            $addr = is_array($property->address_details)
                ? $property->address_details
                : json_decode($property->address_details ?? '{}', true);

            $cityKey  = match ($lang) {
                'ar' => 'ar',
                'ku' => 'ku',
                default => 'en'
            };
            $cityName = $addr['city'][$cityKey] ?? $addr['city']['en'] ?? '';

            $images   = is_array($property->images)
                ? $property->images
                : json_decode($property->images ?? '[]', true);

            $priceUsd = 0.0;
            $rawPrice = $property->price;
            if (!empty($rawPrice)) {
                $priceData = is_string($rawPrice) ? json_decode($rawPrice, true) : (array) $rawPrice;
                if (isset($priceData['usd']) && $priceData['usd'] > 0) {
                    $priceUsd = (float) $priceData['usd'];
                }
            }

            return [
                'id'            => $property->id,
                'name'          => $property->name          ?? '',
                'price'         => $priceUsd,
                'currency'      => 'USD',
                'listing_type'  => $property->listing_type  ?? '',
                'property_type' => $property->type['category'] ?? '',
                'city'          => $cityName,
                'image'         => $images[0] ?? null,
                'is_verified'   => (bool) ($property->verified ?? false),
                'bedrooms'      => (int) ($property->rooms['bedroom']['count'] ?? 0),
            ];
        })->toArray();
    }
}
