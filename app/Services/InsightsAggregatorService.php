<?php

namespace App\Services;

use App\Models\AreaMarketInsight;
use App\Models\MarketTrend;
use App\Models\InvestmentScore;
use App\Models\ExternalDataSource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * InsightsAggregatorService
 * ─────────────────────────────────────────────────────────────────────────────
 * Computes all market metrics by querying the existing properties, areas,
 * and branches tables. Writes results to our analytics tables.
 *
 * Schema notes:
 *  - properties.price       = JSON (numeric or {"amount": X})
 *  - properties.area        = DECIMAL (direct m²)
 *  - properties.rooms       = JSON
 *  - properties.type        = JSON
 *  - properties.locations   = JSON {"lat":X,"lng":Y}
 *  - properties.address_details = JSON {"city":{"en":"Erbil",...},"district":{...}}
 *  - properties.status      = enum(cancelled,pending,approved,available,sold,rented)
 *  - areas.area_name_en     (not "name")
 *  - branches.city_name_en  (not "name")
 *  - NO area_id / branch_id on properties — group by city from address_details
 *
 * NEVER modifies the existing properties/areas/branches tables.
 * ─────────────────────────────────────────────────────────────────────────────
 */
class InsightsAggregatorService
{
    // ── Area Market Insights ─────────────────────────────────────────────────

    /**
     * Compute and upsert market insights for every area.
     * Groups properties by city extracted from address_details JSON.
     * Returns count of areas processed.
     */
    public function computeAllAreaInsights(): int
    {
        $areas = DB::table('areas')
            ->select('id', 'area_name_en', 'branch_id')
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->get();

        $count = 0;
        foreach ($areas as $area) {
            try {
                $this->computeAreaInsight($area->id, $area->area_name_en);
                $count++;
            } catch (\Exception $e) {
                Log::error("InsightsAggregator: failed for area {$area->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Compute market insight for a single area.
     * Matches properties by city name in address_details JSON.
     */
    public function computeAreaInsight(int $areaId, string $areaNameEn = ''): AreaMarketInsight
    {
        // Get area info
        if (!$areaNameEn) {
            $area = DB::table('areas')->where('id', $areaId)->first();
            $areaNameEn = $area?->area_name_en ?? '';
        }

        // Get all active properties for this area (match by city/district in address_details)
        $properties = $this->getPropertiesForArea($areaId, $areaNameEn);

        $prices    = $properties->map(fn($p) => $this->extractPrice($p->price))->filter(fn($v) => $v > 0);
        $areaSizes = $properties->pluck('area');
        $pricesM2  = $properties->map(function ($p) {
            $price = $this->extractPrice($p->price);
            $area  = (float) $p->area;
            return ($price > 0 && $area > 0) ? $price / $area : null;
        })->filter();

        $listingCount       = $prices->count();
        $avgPrice           = $listingCount > 0 ? $prices->avg() : 0;
        $medianPrice        = $this->median($prices->values()->toArray());
        $minPrice           = $listingCount > 0 ? $prices->min() : 0;
        $maxPrice           = $listingCount > 0 ? $prices->max() : 0;
        $avgPricePerM2      = $pricesM2->count() > 0 ? $pricesM2->avg() : 0;
        $medianPricePerM2   = $this->median($pricesM2->values()->toArray());
        $avgDaysOnMarket    = $properties->map(fn($p) => now()->diffInDays($p->created_at))->avg() ?? 0;

        $growth7d  = $this->computePriceGrowth($areaId, 7);
        $growth30d = $this->computePriceGrowth($areaId, 30);
        $growth90d = $this->computePriceGrowth($areaId, 90);
        $growth1y  = $this->computePriceGrowth($areaId, 365);
        $tier      = $this->determinePriceTier($avgPricePerM2);

        $insight = AreaMarketInsight::updateOrCreate(
            ['area_id' => $areaId],
            [
                'average_price'          => round($avgPrice, 2),
                'median_price'           => round($medianPrice, 2),
                'min_price'              => round($minPrice, 2),
                'max_price'              => round($maxPrice, 2),
                'average_price_per_m2'   => round($avgPricePerM2, 2),
                'median_price_per_m2'    => round($medianPricePerM2, 2),
                'listing_count'          => $listingCount,
                'active_listings'        => $listingCount,
                'average_days_on_market' => round($avgDaysOnMarket, 1),
                'price_growth_7d'        => $growth7d,
                'price_growth_30d'       => $growth30d,
                'price_growth_90d'       => $growth90d,
                'price_growth_1y'        => $growth1y,
                'price_tier'             => $tier,
                'computed_at'            => now(),
            ]
        );

        return $insight;
    }

    // ── Market Trends Snapshot ───────────────────────────────────────────────

    public function snapshotAllAreaTrends(): int
    {
        $areas = DB::table('areas')
            ->select('id', 'area_name_en')
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->get();

        $today = now()->toDateString();
        $count = 0;

        foreach ($areas as $area) {
            try {
                $this->snapshotAreaTrend($area->id, $today, $area->area_name_en);
                $count++;
            } catch (\Exception $e) {
                Log::error("InsightsAggregator: trend snapshot failed for area {$area->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    public function snapshotAreaTrend(int $areaId, string $date, string $areaNameEn = ''): void
    {
        $exists = MarketTrend::where('area_id', $areaId)
            ->where('snapshot_date', $date)
            ->where('period_type', 'daily')
            ->exists();
        if ($exists) return;

        $properties = $this->getPropertiesForArea($areaId, $areaNameEn);
        $prices     = $properties->map(fn($p) => $this->extractPrice($p->price))->filter(fn($v) => $v > 0);
        $pricesM2   = $properties->map(function ($p) {
            $price = $this->extractPrice($p->price);
            $area  = (float) $p->area;
            return ($price > 0 && $area > 0) ? $price / $area : null;
        })->filter();

        $avgPrice    = $prices->count() > 0 ? $prices->avg() : 0;
        $avgPriceM2  = $pricesM2->count() > 0 ? $pricesM2->avg() : 0;
        $newListings = $properties->filter(fn($p) => Carbon::parse($p->created_at)->toDateString() === $date)->count();

        // Price change vs yesterday
        $yesterday       = MarketTrend::where('area_id', $areaId)
            ->where('snapshot_date', now()->subDay()->toDateString())
            ->where('period_type', 'daily')
            ->value('avg_price');
        $changeVsYesterday = ($yesterday && $yesterday > 0)
            ? round((($avgPrice - $yesterday) / $yesterday) * 100, 2)
            : 0;

        MarketTrend::create([
            'area_id'                   => $areaId,
            'snapshot_date'             => $date,
            'avg_price'                 => round($avgPrice, 2),
            'median_price'              => round($this->median($prices->values()->toArray()), 2),
            'avg_price_per_m2'          => round($avgPriceM2, 2),
            'min_price'                 => round($prices->min() ?? 0, 2),
            'max_price'                 => round($prices->max() ?? 0, 2),
            'listing_count'             => $prices->count(),
            'new_listings'              => $newListings,
            'price_change_vs_yesterday' => $changeVsYesterday,
            'price_by_type'             => [],
            'count_by_listing_type'     => [],
            'period_type'               => 'daily',
        ]);
    }

    // ── Investment Score ─────────────────────────────────────────────────────

    public function computeAllInvestmentScores(): int
    {
        $insights = AreaMarketInsight::all();
        $count    = 0;

        $maxGrowth    = $insights->max('price_growth_90d') ?: 1;
        $minGrowth    = $insights->min('price_growth_90d') ?: 0;
        $maxDemand    = $insights->max('demand_score')     ?: 1;
        $maxListings  = $insights->max('listing_count')    ?: 1;
        $maxLiquidity = $insights->max('liquidity_score')  ?: 1;

        foreach ($insights as $insight) {
            try {
                $growthScore = $this->normalizeMinMax(
                    $insight->price_growth_90d ?? 0,
                    $minGrowth,
                    $maxGrowth
                ) * 100;

                $demandScore = $maxDemand > 0
                    ? (($insight->demand_score ?? 0) / $maxDemand) * 100
                    : 0;

                $supplyScore = $maxListings > 0
                    ? (1 - (($insight->listing_count ?? 0) / $maxListings)) * 100
                    : 50;

                $liquidityScore = $maxLiquidity > 0
                    ? (($insight->liquidity_score ?? 0) / $maxLiquidity) * 100
                    : 50;

                $poiCount         = $this->getPoiCountForArea($insight->area_id);
                $developmentScore = min(($poiCount / 20) * 100, 100);

                $finalScore = (
                    ($growthScore      * 0.30) +
                    ($demandScore      * 0.25) +
                    ($supplyScore      * 0.20) +
                    ($liquidityScore   * 0.15) +
                    ($developmentScore * 0.10)
                );
                $finalScore = round(min(max($finalScore, 0), 100), 2);

                InvestmentScore::updateOrCreate(
                    ['area_id' => $insight->area_id],
                    [
                        'investment_score'     => $finalScore,
                        'price_growth_score'   => round($growthScore, 2),
                        'demand_score'         => round($demandScore, 2),
                        'supply_score'         => round($supplyScore, 2),
                        'liquidity_score'      => round($liquidityScore, 2),
                        'development_score'    => round($developmentScore, 2),
                        'grade'                => $this->scoreToGrade($finalScore),
                        'recommendation'       => $this->scoreToRecommendation($finalScore),
                        'price_growth_90d'     => $insight->price_growth_90d ?? 0,
                        'active_listing_count' => $insight->active_listings ?? 0,
                        'poi_count'            => $poiCount,
                        'trend'                => $this->growthToTrend($insight->price_growth_90d ?? 0),
                        'risk_flags'           => $this->detectRiskFlags($insight),
                        'positive_signals'     => $this->detectPositiveSignals($insight),
                        'computed_at'          => now(),
                    ]
                );

                AreaMarketInsight::where('area_id', $insight->area_id)
                    ->update(['investment_score' => $finalScore]);

                $count++;
            } catch (\Exception $e) {
                Log::error("InvestmentScore: failed for area {$insight->area_id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    // ── Property Feature Extraction ──────────────────────────────────────────

    public function extractPropertyFeatures(string $propertyId): ?array
    {
        $property = DB::table('properties')->where('id', $propertyId)->first();
        if (!$property) return null;

        // Parse locations JSON
        $locations = is_string($property->locations ?? null)
            ? json_decode($property->locations, true)
            : (array)($property->locations ?? []);
        $lat = $locations['lat'] ?? $locations['latitude'] ?? null;
        $lng = $locations['lng'] ?? $locations['longitude'] ?? null;

        // Parse price
        $price = $this->extractPrice($property->price);

        // Parse rooms
        $rooms = is_string($property->rooms ?? null)
            ? json_decode($property->rooms, true)
            : ($property->rooms ?? 0);
        $bedrooms = is_array($rooms)
            ? ($rooms['bedrooms'] ?? $rooms['bedroom'] ?? current($rooms) ?? 0)
            : (int)$rooms;

        // Parse type
        $type = is_string($property->type ?? null)
            ? json_decode($property->type, true)
            : ($property->type ?? 'apartment');
        $typeStr = is_array($type) ? ($type['en'] ?? 'apartment') : (string)$type;

        // Parse address for area lookup
        $address = is_string($property->address_details ?? null)
            ? json_decode($property->address_details, true)
            : [];
        $cityData = $address['city'] ?? [];
        $cityName = is_array($cityData) ? ($cityData['en'] ?? '') : (string)$cityData;

        // Find matching area
        $area     = DB::table('areas')->where('area_name_en', 'LIKE', "%{$cityName}%")->first();
        $areaId   = $area?->id ?? 0;
        $branchId = $area?->branch_id ?? 0;

        $poiCount    = ($lat && $lng) ? ExternalDataSource::nearby($lat, $lng, 2.0)->count() : 0;
        $areaInsight = $areaId ? AreaMarketInsight::where('area_id', $areaId)->first() : null;

        // Parse amenities from features JSON
        $features  = is_string($property->features ?? null) ? json_decode($property->features, true) : [];
        $amenities = is_string($property->amenities ?? null) ? json_decode($property->amenities, true) : [];
        $allFeatures = array_merge((array)$features, (array)$amenities);

        return [
            'property_id'           => $propertyId,
            'price'                 => $price,
            'area_size'             => (float)$property->area,
            'bedrooms'              => (int)$bedrooms,
            'bathrooms'             => 0, // not in schema
            'property_type'         => $typeStr,
            'listing_type'          => $property->listing_type ?? 'sell',
            'floor'                 => (int)($property->floor_number ?? 0),
            'year_built'            => (int)($property->year_built ?? 0),
            'latitude'              => $lat ? (float)$lat : null,
            'longitude'             => $lng ? (float)$lng : null,
            'area_id'               => (int)$areaId,
            'branch_id'             => (int)$branchId,
            'has_parking'           => in_array('parking', $allFeatures),
            'has_elevator'          => in_array('elevator', $allFeatures),
            'has_balcony'           => in_array('balcony', $allFeatures),
            'has_garden'            => in_array('garden', $allFeatures),
            'has_pool'              => in_array('pool', $allFeatures),
            'has_security'          => in_array('security', $allFeatures),
            'is_furnished'          => (bool)$property->furnished,
            'area_avg_price_per_m2' => $areaInsight?->average_price_per_m2 ?? 0,
            'area_demand_score'     => $areaInsight?->demand_score ?? 0,
            'area_listing_count'    => $areaInsight?->listing_count ?? 0,
            'area_price_growth_30d' => $areaInsight?->price_growth_30d ?? 0,
            'nearby_poi_count'      => $poiCount,
        ];
    }

    // ── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Get active properties for an area by matching branch city name in address_details JSON.
     * Properties store city (e.g. "Erbil") not neighborhood — so we match via branch.
     * All areas under the same branch share the city's property pool.
     */
    private function getPropertiesForArea(int $areaId, string $areaNameEn): \Illuminate\Support\Collection
    {
        // Get area → branch → city name
        $area   = DB::table('areas')->where('id', $areaId)->first();
        $branch = $area ? DB::table('branches')->where('id', $area->branch_id)->first() : null;

        if (!$branch) return collect();

        $cityName = $branch->city_name_en; // e.g. "Erbil"

        return DB::table('properties')
            ->whereIn('status', ['approved', 'available'])
            ->where('is_active', 1)
            ->where('published', 1)
            ->where('address_details', 'LIKE', "%{$cityName}%")
            ->select(
                'id',
                'price',
                'area',
                'rooms',
                'type',
                'listing_type',
                'address_details',
                'locations',
                'created_at',
                'furnished',
                'floor_number',
                'year_built',
                'views',
                'favorites_count',
                'rating'
            )
            ->get();
    }

    /**
     * Extract numeric price from JSON or plain value.
     */
    private function extractPrice($priceVal): float
    {
        if (is_null($priceVal)) return 0.0;
        if (is_numeric($priceVal)) return (float)$priceVal;
        if (is_string($priceVal)) {
            $decoded = json_decode($priceVal, true);
            if (is_numeric($decoded)) return (float)$decoded;
            if (is_array($decoded)) {
                foreach (['amount', 'value', 'price'] as $k) {
                    if (isset($decoded[$k]) && is_numeric($decoded[$k])) {
                        return (float)$decoded[$k];
                    }
                }
                // Return first numeric value
                foreach ($decoded as $v) {
                    if (is_numeric($v)) return (float)$v;
                }
            }
        }
        return 0.0;
    }

    private function median(array $values): float
    {
        if (empty($values)) return 0.0;
        sort($values);
        $count = count($values);
        $mid   = (int)floor($count / 2);
        return $count % 2 === 0
            ? ($values[$mid - 1] + $values[$mid]) / 2
            : $values[$mid];
    }

    private function computePriceGrowth(int $areaId, int $days): float
    {
        $past = MarketTrend::where('area_id', $areaId)
            ->where('snapshot_date', now()->subDays($days)->toDateString())
            ->where('period_type', 'daily')
            ->value('avg_price');

        $current = MarketTrend::where('area_id', $areaId)
            ->where('period_type', 'daily')
            ->orderByDesc('snapshot_date')
            ->value('avg_price');

        if (!$past || $past <= 0 || !$current) return 0.0;
        return round((($current - $past) / $past) * 100, 2);
    }

    private function determinePriceTier(float $pricePerM2): string
    {
        return match (true) {
            $pricePerM2 <= 600  => 'affordable',
            $pricePerM2 <= 1200 => 'medium',
            $pricePerM2 <= 2500 => 'expensive',
            default             => 'luxury',
        };
    }

    private function normalizeMinMax(float $value, float $min, float $max): float
    {
        if ($max === $min) return 0.5;
        return ($value - $min) / ($max - $min);
    }

    private function getPoiCountForArea(int $areaId): int
    {
        return ExternalDataSource::where('area_id', $areaId)->where('is_active', true)->count();
    }

    private function scoreToGrade(float $score): string
    {
        return match (true) {
            $score >= 85 => 'A+',
            $score >= 70 => 'A',
            $score >= 55 => 'B',
            $score >= 40 => 'C',
            default      => 'D',
        };
    }

    private function scoreToRecommendation(float $score): string
    {
        return match (true) {
            $score >= 75 => 'strong_buy',
            $score >= 55 => 'buy',
            $score >= 35 => 'hold',
            default      => 'avoid',
        };
    }

    private function growthToTrend(float $growth90d): string
    {
        if ($growth90d > 2)  return 'rising';
        if ($growth90d < -2) return 'declining';
        return 'stable';
    }

    private function detectRiskFlags(AreaMarketInsight $insight): array
    {
        $flags = [];
        if (($insight->price_growth_90d ?? 0) < -5) $flags[] = 'price_decline';
        if (($insight->demand_score ?? 0) < 30)      $flags[] = 'low_demand';
        if (($insight->listing_count ?? 0) > 200)    $flags[] = 'high_supply';
        if (($insight->average_days_on_market ?? 0) > 90) $flags[] = 'slow_market';
        return $flags;
    }

    private function detectPositiveSignals(AreaMarketInsight $insight): array
    {
        $signals = [];
        if (($insight->price_growth_90d ?? 0) > 5)  $signals[] = 'strong_price_growth';
        if (($insight->demand_score ?? 0) > 70)      $signals[] = 'high_demand';
        if (($insight->listing_count ?? 0) < 20)     $signals[] = 'low_supply';
        if (($insight->liquidity_score ?? 0) > 70)   $signals[] = 'fast_transactions';
        return $signals;
    }
}
