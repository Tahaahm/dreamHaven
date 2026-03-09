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
 * This is pure SQL/DB logic — no ML required.
 * ML-based scores (demand, liquidity) come from AIBridgeService separately.
 *
 * NEVER modifies the existing properties/areas/branches tables.
 * ─────────────────────────────────────────────────────────────────────────────
 */
class InsightsAggregatorService
{
    // ── Area Market Insights ─────────────────────────────────────────────────

    /**
     * Compute and upsert market insights for every area that has properties.
     * Returns count of areas processed.
     */
    public function computeAllAreaInsights(): int
    {
        $areas = DB::table('areas')->select('id')->get();
        $count = 0;

        foreach ($areas as $area) {
            try {
                $this->computeAreaInsight($area->id);
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
     * Compute market insight for a single area and upsert into DB.
     */
    public function computeAreaInsight(int $areaId): AreaMarketInsight
    {
        // ── Aggregate price metrics from properties ────────────────────────
        $metrics = DB::table('properties')
            ->where('area_id', $areaId)
            ->where('status', 'active')   // only active listings
            ->selectRaw("
                COUNT(*)                          AS listing_count,
                AVG(price)                        AS average_price,
                AVG(price / NULLIF(area_size, 0)) AS average_price_per_m2,
                MIN(price)                        AS min_price,
                MAX(price)                        AS max_price,
                AVG(DATEDIFF(NOW(), created_at))  AS average_days_on_market
            ")
            ->first();

        // ── Compute median price (MySQL doesn't have MEDIAN()) ─────────────
        $medianPrice    = $this->computeMedian('properties', 'price', ['area_id' => $areaId, 'status' => 'active']);
        $medianPriceM2  = $this->computeMedian('properties', 'price / NULLIF(area_size, 0)', ['area_id' => $areaId, 'status' => 'active']);

        // ── Price growth from market_trends snapshots ──────────────────────
        $growth7d  = $this->computePriceGrowth($areaId, 7);
        $growth30d = $this->computePriceGrowth($areaId, 30);
        $growth90d = $this->computePriceGrowth($areaId, 90);
        $growth1y  = $this->computePriceGrowth($areaId, 365);

        // ── Determine price tier ───────────────────────────────────────────
        $tier = $this->determinePriceTier($metrics->average_price_per_m2 ?? 0);

        // ── Upsert (update if exists, create if not) ───────────────────────
        $insight = AreaMarketInsight::updateOrCreate(
            ['area_id' => $areaId],
            [
                'average_price'          => $metrics->average_price ?? 0,
                'median_price'           => $medianPrice,
                'min_price'              => $metrics->min_price ?? 0,
                'max_price'              => $metrics->max_price ?? 0,
                'average_price_per_m2'   => $metrics->average_price_per_m2 ?? 0,
                'median_price_per_m2'    => $medianPriceM2,
                'listing_count'          => $metrics->listing_count ?? 0,
                'active_listings'        => $metrics->listing_count ?? 0,
                'average_days_on_market' => $metrics->average_days_on_market ?? 0,
                'price_growth_7d'        => $growth7d,
                'price_growth_30d'       => $growth30d,
                'price_growth_90d'       => $growth90d,
                'price_growth_1y'        => $growth1y,
                'price_tier'             => $tier,
                // demand_score and liquidity_score updated separately by AI service
                'computed_at'            => now(),
            ]
        );

        return $insight;
    }

    // ── Market Trends Snapshot ───────────────────────────────────────────────

    /**
     * Take daily snapshot for ALL areas.
     * Append-only — never updates existing rows.
     */
    public function snapshotAllAreaTrends(): int
    {
        $areas = DB::table('areas')->select('id')->get();
        $today = now()->toDateString();
        $count = 0;

        foreach ($areas as $area) {
            try {
                $this->snapshotAreaTrend($area->id, $today);
                $count++;
            } catch (\Exception $e) {
                Log::error("InsightsAggregator: trend snapshot failed for area {$area->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    public function snapshotAreaTrend(int $areaId, string $date): void
    {
        // Skip if snapshot already exists for today
        $exists = MarketTrend::where('area_id', $areaId)
            ->where('snapshot_date', $date)
            ->where('period_type', 'daily')
            ->exists();
        if ($exists) return;

        $metrics = DB::table('properties')
            ->where('area_id', $areaId)
            ->where('status', 'active')
            ->selectRaw("
                COUNT(*)                          AS listing_count,
                AVG(price)                        AS avg_price,
                AVG(price / NULLIF(area_size, 0)) AS avg_price_per_m2,
                MIN(price)                        AS min_price,
                MAX(price)                        AS max_price
            ")
            ->first();

        // New listings today
        $newListings = DB::table('properties')
            ->where('area_id', $areaId)
            ->whereDate('created_at', $date)
            ->count();

        // Price by type breakdown
        $priceByType = DB::table('properties')
            ->where('area_id', $areaId)
            ->where('status', 'active')
            ->selectRaw('property_type, AVG(price) AS avg_price')
            ->groupBy('property_type')
            ->pluck('avg_price', 'property_type')
            ->toArray();

        // Count by listing type
        $countByListingType = DB::table('properties')
            ->where('area_id', $areaId)
            ->where('status', 'active')
            ->selectRaw('listing_type, COUNT(*) AS cnt')
            ->groupBy('listing_type')
            ->pluck('cnt', 'listing_type')
            ->toArray();

        // Yesterday's avg_price for day-over-day comparison
        $yesterday = MarketTrend::where('area_id', $areaId)
            ->where('snapshot_date', now()->subDay()->toDateString())
            ->where('period_type', 'daily')
            ->value('avg_price');

        $changeVsYesterday = 0;
        if ($yesterday && $yesterday > 0) {
            $changeVsYesterday = round(
                (($metrics->avg_price - $yesterday) / $yesterday) * 100,
                2
            );
        }

        MarketTrend::create([
            'area_id'                   => $areaId,
            'snapshot_date'             => $date,
            'avg_price'                 => $metrics->avg_price ?? 0,
            'median_price'              => $this->computeMedian('properties', 'price', ['area_id' => $areaId, 'status' => 'active']),
            'avg_price_per_m2'          => $metrics->avg_price_per_m2 ?? 0,
            'min_price'                 => $metrics->min_price ?? 0,
            'max_price'                 => $metrics->max_price ?? 0,
            'listing_count'             => $metrics->listing_count ?? 0,
            'new_listings'              => $newListings,
            'price_change_vs_yesterday' => $changeVsYesterday,
            'price_by_type'             => $priceByType,
            'count_by_listing_type'     => $countByListingType,
            'period_type'               => 'daily',
        ]);
    }

    // ── Investment Score ─────────────────────────────────────────────────────

    /**
     * Compute investment score for all areas.
     * Weighted formula defined in analysis document.
     */
    public function computeAllInvestmentScores(): int
    {
        $insights = AreaMarketInsight::with('area')->get();
        $count    = 0;

        // ── Normalize: find global min/max for each metric ─────────────────
        $allGrowths    = $insights->pluck('price_growth_90d');
        $allDemands    = $insights->pluck('demand_score');
        $allListings   = $insights->pluck('listing_count');
        $allLiquidity  = $insights->pluck('liquidity_score');

        $maxGrowth   = $allGrowths->max()   ?: 1;
        $minGrowth   = $allGrowths->min()   ?: 0;
        $maxDemand   = $allDemands->max()   ?: 1;
        $maxListings = $allListings->max()  ?: 1;
        $maxLiquidity = $allLiquidity->max() ?: 1;

        foreach ($insights as $insight) {
            try {
                // Normalize each component 0–100
                $growthScore = $this->normalizeMinMax(
                    $insight->price_growth_90d,
                    $minGrowth,
                    $maxGrowth
                ) * 100;

                $demandScore = $maxDemand > 0
                    ? ($insight->demand_score / $maxDemand) * 100
                    : 0;

                // Supply score: fewer listings = higher score (scarcity = value)
                $supplyScore = $maxListings > 0
                    ? (1 - ($insight->listing_count / $maxListings)) * 100
                    : 50;

                $liquidityScore = $maxLiquidity > 0
                    ? ($insight->liquidity_score / $maxLiquidity) * 100
                    : 50;

                // Development score: POI count near area centroid
                $poiCount = $this->getPoiCountForArea($insight->area_id);
                $developmentScore = min(($poiCount / 20) * 100, 100); // 20+ POIs = max score

                // Weighted final score
                $finalScore = (
                    ($growthScore    * 0.30) +
                    ($demandScore    * 0.25) +
                    ($supplyScore    * 0.20) +
                    ($liquidityScore * 0.15) +
                    ($developmentScore * 0.10)
                );

                $finalScore = round(min(max($finalScore, 0), 100), 2);

                $grade          = $this->scoreToGrade($finalScore);
                $recommendation = $this->scoreToRecommendation($finalScore);
                $trend          = $this->growthToTrend($insight->price_growth_90d);

                InvestmentScore::updateOrCreate(
                    ['area_id' => $insight->area_id],
                    [
                        'investment_score'   => $finalScore,
                        'price_growth_score' => round($growthScore, 2),
                        'demand_score'       => round($demandScore, 2),
                        'supply_score'       => round($supplyScore, 2),
                        'liquidity_score'    => round($liquidityScore, 2),
                        'development_score'  => round($developmentScore, 2),
                        'grade'              => $grade,
                        'recommendation'     => $recommendation,
                        'price_growth_90d'   => $insight->price_growth_90d,
                        'active_listing_count' => $insight->active_listings,
                        'poi_count'          => $poiCount,
                        'trend'              => $trend,
                        'risk_flags'         => $this->detectRiskFlags($insight),
                        'positive_signals'   => $this->detectPositiveSignals($insight),
                        'computed_at'        => now(),
                    ]
                );

                // Also update the investment_score on area_market_insights
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

    /**
     * Extract ML-ready features for a property from the existing properties table.
     * Returns the feature array that gets sent to the Python /predict endpoint.
     *
     * Adapts to whatever columns Dream Mulk's properties table has.
     * The location JSON field is parsed for lat/lng.
     */
    public function extractPropertyFeatures(int $propertyId): ?array
    {
        $property = DB::table('properties')
            ->where('id', $propertyId)
            ->first();

        if (!$property) return null;

        // Parse location JSON: { "lat": 36.19, "lng": 44.00 }
        $location = is_string($property->location ?? null)
            ? json_decode($property->location, true)
            : (array)($property->location ?? []);

        $lat = $location['lat'] ?? $location['latitude']  ?? null;
        $lng = $location['lng'] ?? $location['longitude'] ?? null;

        // Get POI count within 2km
        $poiCount = $lat && $lng
            ? ExternalDataSource::nearby($lat, $lng, 2.0)->count()
            : 0;

        // Get area insight for market context features
        $areaInsight = AreaMarketInsight::where('area_id', $property->area_id)->first();

        return [
            // Core property features
            'property_id'           => $propertyId,
            'price'                 => (float)($property->price ?? 0),
            'area_size'             => (float)($property->area_size ?? 0),
            'bedrooms'              => (int)($property->bedrooms ?? 0),
            'bathrooms'             => (int)($property->bathrooms ?? 0),
            'property_type'         => $property->property_type ?? 'apartment',
            'listing_type'          => $property->listing_type ?? 'sale',
            'floor'                 => (int)($property->floor ?? 0),
            'year_built'            => (int)($property->year_built ?? 0),

            // Location features
            'latitude'              => $lat,
            'longitude'             => $lng,
            'area_id'               => (int)($property->area_id ?? 0),
            'branch_id'             => (int)($property->branch_id ?? 0),

            // Amenity features (assumed boolean columns)
            'has_parking'           => (bool)($property->has_parking ?? false),
            'has_elevator'          => (bool)($property->has_elevator ?? false),
            'has_balcony'           => (bool)($property->has_balcony ?? false),
            'has_garden'            => (bool)($property->has_garden ?? false),
            'has_pool'              => (bool)($property->has_pool ?? false),
            'has_security'          => (bool)($property->has_security ?? false),
            'is_furnished'          => (bool)($property->is_furnished ?? false),

            // Market context features (from area insight)
            'area_avg_price_per_m2' => $areaInsight?->average_price_per_m2 ?? 0,
            'area_demand_score'     => $areaInsight?->demand_score ?? 0,
            'area_listing_count'    => $areaInsight?->listing_count ?? 0,
            'area_price_growth_30d' => $areaInsight?->price_growth_30d ?? 0,

            // Enrichment features
            'nearby_poi_count'      => $poiCount,
        ];
    }

    // ── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Compute median using percentile trick in MySQL.
     * Works without window functions (MySQL 5.7 compatible).
     */
    private function computeMedian(string $table, string $column, array $where): float
    {
        try {
            $query = DB::table($table);
            foreach ($where as $col => $val) {
                $query->where($col, $val);
            }
            $count = $query->count();
            if ($count === 0) return 0.0;

            $offset = (int)floor(($count - 1) / 2);

            $result = DB::table($table)
                ->where($where)
                ->orderByRaw($column . ' ASC')
                ->skip($offset)
                ->take($count % 2 === 0 ? 2 : 1)
                ->selectRaw($column . ' AS val')
                ->pluck('val');

            return (float)($result->avg() ?? 0);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Compute price growth % for an area over N days.
     * Compares current avg_price to the snapshot N days ago.
     */
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

    /**
     * Determine price tier based on price per m².
     * Thresholds based on Kurdistan Region market norms (USD).
     */
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
        if ($insight->price_growth_90d < -5) $flags[] = 'price_decline';
        if ($insight->demand_score < 30)      $flags[] = 'low_demand';
        if ($insight->listing_count > 200)    $flags[] = 'high_supply';
        if ($insight->average_days_on_market > 90) $flags[] = 'slow_market';
        return $flags;
    }

    private function detectPositiveSignals(AreaMarketInsight $insight): array
    {
        $signals = [];
        if ($insight->price_growth_90d > 5)   $signals[] = 'strong_price_growth';
        if ($insight->demand_score > 70)       $signals[] = 'high_demand';
        if ($insight->listing_count < 20)      $signals[] = 'low_supply';
        if ($insight->liquidity_score > 70)    $signals[] = 'fast_transactions';
        return $signals;
    }
}
