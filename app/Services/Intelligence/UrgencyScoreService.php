<?php

namespace App\Services\Intelligence;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * UrgencyScoreService
 *
 * Computes a real-time demand/urgency score per property.
 *
 * Urgency levels:
 *   none   → < 3 unique enquirers in 48h
 *   low    → 3–4 unique enquirers
 *   medium → 5–9 unique enquirers
 *   high   → 10+ unique enquirers
 *
 * Used by:
 *   - SmartStripService  → "high_demand" strip type
 *   - PropertyController → appends urgency_score to property detail response
 *   - FCM notifications  → "Still in demand" resurfacing
 *
 * Cache: 1h per property (invalidated on new contact_intent signal)
 */
class UrgencyScoreService
{
    private const CACHE_TTL      = 3600;   // 1 hour
    private const WINDOW_HOURS   = 48;     // demand window
    private const BATCH_CACHE_TTL = 300;   // 5 min for batch queries

    // Thresholds
    private const THRESHOLD_LOW    = 3;
    private const THRESHOLD_MEDIUM = 5;
    private const THRESHOLD_HIGH   = 10;

    // ─────────────────────────────────────────────────────────────────────────
    //  PUBLIC API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get urgency data for a single property.
     *
     * @return array {
     *   level: 'none'|'low'|'medium'|'high',
     *   enquirer_count: int,
     *   window_hours: int,
     *   label: string,    // human-readable for UI
     *   label_ar: string,
     *   label_ku: string,
     * }
     */
    public function getPropertyUrgency(string $propertyId): array
    {
        return Cache::remember(
            "urgency_{$propertyId}",
            self::CACHE_TTL,
            fn() => $this->computePropertyUrgency($propertyId)
        );
    }

    /**
     * Batch: get urgency for multiple properties at once.
     * Used by PropertyController index to append urgency without N+1.
     *
     * @param array $propertyIds
     * @return array  keyed by property_id
     */
    public function batchGetUrgency(array $propertyIds): array
    {
        if (empty($propertyIds)) return [];

        $cacheKey = 'urgency_batch_' . md5(implode(',', $propertyIds));

        return Cache::remember($cacheKey, self::BATCH_CACHE_TTL, function () use ($propertyIds) {
            $since = now()->subHours(self::WINDOW_HOURS);

            $rows = DB::table('user_property_interactions')
                ->select('property_id', DB::raw('COUNT(DISTINCT user_id) AS enquirer_count'))
                ->whereIn('property_id', $propertyIds)
                ->where('interaction_type', 'contact_intent')
                ->where('created_at', '>=', $since)
                ->groupBy('property_id')
                ->get()
                ->keyBy('property_id');

            $result = [];
            foreach ($propertyIds as $id) {
                $count = $rows->get($id)?->enquirer_count ?? 0;
                $result[$id] = $this->buildUrgencyData((int) $count);
            }
            return $result;
        });
    }

    /**
     * Get properties with HIGH urgency in a given city.
     * Used by SmartStripService to power the "high_demand" strip.
     *
     * @param string|null $city
     * @param int $limit
     * @return array  [ property_id, enquirer_count, urgency_level ]
     */
    public function getHighDemandProperties(?string $city = null, int $limit = 10): array
    {
        $cacheKey = "high_demand_{$city}_{$limit}";

        return Cache::remember($cacheKey, self::BATCH_CACHE_TTL, function () use ($city, $limit) {
            $since = now()->subHours(self::WINDOW_HOURS);

            $query = DB::table('user_property_interactions AS upi')
                ->select(
                    'upi.property_id',
                    DB::raw('COUNT(DISTINCT upi.user_id) AS enquirer_count')
                )
                ->join('properties AS p', 'p.id', '=', 'upi.property_id')
                ->where('upi.interaction_type', 'contact_intent')
                ->where('upi.created_at', '>=', $since)
                ->where('p.is_active', true)
                ->where('p.is_published', true)
                ->having('enquirer_count', '>=', self::THRESHOLD_LOW);

            if ($city) {
                $query->where(function ($q) use ($city) {
                    $q->whereRaw("JSON_EXTRACT(p.address_details, '$.city.en') = ?", [$city])
                        ->orWhere('p.city', $city);
                });
            }

            return $query
                ->groupBy('upi.property_id')
                ->orderByDesc('enquirer_count')
                ->limit($limit)
                ->get()
                ->map(fn($row) => [
                    'property_id'    => $row->property_id,
                    'enquirer_count' => $row->enquirer_count,
                    'urgency_level'  => $this->levelFromCount((int) $row->enquirer_count),
                ])
                ->toArray();
        });
    }

    /**
     * Invalidate cache for a specific property.
     * Call this from UserBehaviorController when contact_intent is received.
     */
    public function invalidate(string $propertyId): void
    {
        Cache::forget("urgency_{$propertyId}");
        // Batch caches will expire naturally (5 min TTL)
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  INTERNAL
    // ─────────────────────────────────────────────────────────────────────────

    private function computePropertyUrgency(string $propertyId): array
    {
        $since = now()->subHours(self::WINDOW_HOURS);

        $count = DB::table('user_property_interactions')
            ->where('property_id', $propertyId)
            ->where('interaction_type', 'contact_intent')
            ->where('created_at', '>=', $since)
            ->distinct('user_id')
            ->count('user_id');

        return $this->buildUrgencyData((int) $count);
    }

    private function buildUrgencyData(int $count): array
    {
        $level = $this->levelFromCount($count);

        return [
            'level'          => $level,
            'enquirer_count' => $count,
            'window_hours'   => self::WINDOW_HOURS,
            'label'          => $this->getLabel($count, $level, 'en'),
            'label_ar'       => $this->getLabel($count, $level, 'ar'),
            'label_ku'       => $this->getLabel($count, $level, 'ku'),
        ];
    }

    private function levelFromCount(int $count): string
    {
        if ($count >= self::THRESHOLD_HIGH)   return 'high';
        if ($count >= self::THRESHOLD_MEDIUM) return 'medium';
        if ($count >= self::THRESHOLD_LOW)    return 'low';
        return 'none';
    }

    private function getLabel(int $count, string $level, string $lang): string
    {
        if ($level === 'none') return '';

        $labels = [
            'en' => [
                'low'    => "{$count} people enquired this week",
                'medium' => "🔥 {$count} people are interested",
                'high'   => "🔥 High demand — {$count} enquiries this week",
            ],
            'ar' => [
                'low'    => "{$count} أشخاص استفسروا هذا الأسبوع",
                'medium' => "🔥 {$count} شخص مهتم بهذا العقار",
                'high'   => "🔥 طلب مرتفع — {$count} استفسار هذا الأسبوع",
            ],
            'ku' => [
                'low'    => "{$count} کەس ئەم هەفتەیە پرسیاریان کرد",
                'medium' => "🔥 {$count} کەس حازی ئەم خانووەیەن",
                'high'   => "🔥 داواکاری بەرز — {$count} پرسیار ئەم هەفتەیە",
            ],
        ];

        return $labels[$lang][$level] ?? $labels['en'][$level];
    }
}