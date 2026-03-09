<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AreaMarketInsight;
use App\Models\InvestmentScore;
use App\Models\MarketTrend;
use App\Models\PropertyValuation;
use App\Services\PipelineOrchestratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

// ─────────────────────────────────────────────────────────────────────────────
// AreaInsightsController
// GET /api/v1/areas/market-insights
// GET /api/v1/areas/market-insights/{areaId}
// GET /api/v1/areas/top-investment
// ─────────────────────────────────────────────────────────────────────────────
class AreaInsightsController extends Controller
{
    /**
     * GET /api/v1/areas/market-insights
     * List all areas with their market insights summary.
     * Used by the Flutter map area list / search.
     *
     * Query params:
     *   branch_id    : filter by city
     *   tier         : filter by price tier
     *   sort         : investment_score|demand_score|avg_price|price_growth_30d
     *   per_page     : default 20
     */
    public function index(Request $request): JsonResponse
    {
        $branchId = $request->integer('branch_id') ?: null;
        $tier     = $request->string('tier')->toString() ?: null;
        $sort     = $request->string('sort', 'investment_score')->toString();

        $cacheKey = "area_insights_list_{$branchId}_{$tier}_{$sort}_p{$request->integer('page', 1)}";

        $data = Cache::remember($cacheKey, now()->addHours(2), function () use (
            $branchId,
            $tier,
            $sort,
            $request
        ) {
            $query = AreaMarketInsight::with('area:id,name');

            if ($branchId) {
                // Filter by areas belonging to this branch
                $query->whereHas('area', fn($q) => $q->where('branch_id', $branchId));
            }
            if ($tier) $query->byTier($tier);

            $allowed = ['investment_score', 'demand_score', 'average_price', 'price_growth_30d', 'listing_count'];
            if (in_array($sort, $allowed)) {
                $query->orderByDesc($sort);
            }

            return $query->paginate($request->integer('per_page', 20));
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/areas/market-insights/{areaId}
     * Full detail panel for a single area.
     * Called when user taps an area on the map.
     *
     * Returns: market insights + investment score breakdown
     */
    public function show(int $areaId): JsonResponse
    {
        $cacheKey = "area_insight_detail_{$areaId}";

        $data = Cache::remember($cacheKey, now()->addHours(2), function () use ($areaId) {
            $insight    = AreaMarketInsight::where('area_id', $areaId)
                ->with('area:id,name,branch_id')
                ->firstOrFail();

            $investment = InvestmentScore::where('area_id', $areaId)->first();

            return [
                // ── Area identity ────────────────────────────────────────────
                'area_id'          => $areaId,
                'area_name'        => $insight->area?->name,

                // ── Price metrics ────────────────────────────────────────────
                'average_price'         => $insight->average_price,
                'median_price'          => $insight->median_price,
                'min_price'             => $insight->min_price,
                'max_price'             => $insight->max_price,
                'average_price_per_m2'  => $insight->average_price_per_m2,
                'currency'              => $insight->currency,

                // ── Market activity ──────────────────────────────────────────
                'listing_count'          => $insight->listing_count,
                'active_listings'        => $insight->active_listings,
                'average_days_on_market' => $insight->average_days_on_market,
                'demand_score'           => $insight->demand_score,
                'liquidity_score'        => $insight->liquidity_score,

                // ── Price trends ─────────────────────────────────────────────
                'price_tier'        => $insight->price_tier,
                'tier_label'        => $insight->tier_label,
                'tier_color'        => $insight->tier_color,
                'trend_direction'   => $insight->trend_direction,
                'price_growth_7d'   => $insight->price_growth_7d,
                'price_growth_30d'  => $insight->price_growth_30d,
                'price_growth_90d'  => $insight->price_growth_90d,
                'price_growth_1y'   => $insight->price_growth_1y,

                // ── Investment ───────────────────────────────────────────────
                'investment_score'      => $insight->investment_score,
                'investment_grade'      => $insight->investment_grade,
                'investment_detail'     => $investment ? [
                    'recommendation'    => $investment->recommendation,
                    'recommendation_label' => $investment->recommendation_label,
                    'grade'             => $investment->grade,
                    'trend'             => $investment->trend,
                    'score_breakdown'   => $investment->score_breakdown,
                    'analysis_summary'  => $investment->analysis_summary,
                    'risk_flags'        => $investment->risk_flags,
                    'positive_signals'  => $investment->positive_signals,
                ] : null,

                'computed_at' => $insight->computed_at?->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/areas/top-investment
     * Top areas by investment score — for "Best Investment" map tab.
     *
     * Query params:
     *   branch_id : filter by city
     *   limit     : default 10
     */
    public function topInvestment(Request $request): JsonResponse
    {
        $branchId = $request->integer('branch_id') ?: null;
        $limit    = min($request->integer('limit', 10), 50);

        $cacheKey = "top_investment_{$branchId}_{$limit}";

        $data = Cache::remember($cacheKey, now()->addHours(3), function () use ($branchId, $limit) {
            $query = InvestmentScore::with('area:id,name')
                ->topScores($limit)
                ->where('recommendation', '!=', 'avoid');

            if ($branchId) {
                $query->whereHas('area', fn($q) => $q->where('branch_id', $branchId));
            }

            return $query->get()->map(fn($score) => [
                'area_id'           => $score->area_id,
                'area_name'         => $score->area?->name,
                'investment_score'  => $score->investment_score,
                'grade'             => $score->grade,
                'recommendation'    => $score->recommendation,
                'recommendation_label' => $score->recommendation_label,
                'trend'             => $score->trend,
                'price_growth_90d'  => $score->price_growth_90d,
                'positive_signals'  => $score->positive_signals,
            ]);
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// PropertyValuationController
// GET /api/v1/properties/ai-valuation/{propertyId}
// ─────────────────────────────────────────────────────────────────────────────
class PropertyValuationController extends Controller
{
    public function __construct(
        private PipelineOrchestratorService $pipeline
    ) {}

    /**
     * GET /api/v1/properties/ai-valuation/{propertyId}
     *
     * Returns AI valuation for a property.
     * If no valuation exists, dispatches async job and returns 202.
     * If valuation exists, returns it immediately.
     *
     * Response codes:
     *   200 : Valuation ready
     *   202 : Valuation queued (check back in 30s)
     *   404 : Property not found
     */
    public function show(int $propertyId): JsonResponse
    {
        // Check if completed valuation already exists
        $valuation = PropertyValuation::where('property_id', $propertyId)
            ->completed()
            ->latestVersion()
            ->first();

        if ($valuation) {
            return response()->json([
                'success' => true,
                'data'    => $this->formatValuation($valuation),
            ]);
        }

        // Check if currently processing
        $processing = PropertyValuation::where('property_id', $propertyId)
            ->where('status', 'processing')
            ->exists();

        if ($processing) {
            return response()->json([
                'success' => false,
                'status'  => 'processing',
                'message' => 'Valuation is being computed. Please check back in 30 seconds.',
            ], 202);
        }

        // Dispatch async valuation job
        $this->pipeline->valuateSingleProperty($propertyId);

        return response()->json([
            'success' => false,
            'status'  => 'queued',
            'message' => 'Valuation request queued. Please check back in 30 seconds.',
        ], 202);
    }

    private function formatValuation(PropertyValuation $v): array
    {
        return [
            'property_id'           => $v->property_id,

            // Predicted values
            'predicted_price'       => $v->predicted_price,
            'predicted_price_per_m2' => $v->predicted_price_per_m2,
            'predicted_price_low'   => $v->predicted_price_low,
            'predicted_price_high'  => $v->predicted_price_high,

            // Actual vs predicted
            'actual_price'          => $v->actual_price,
            'overprice_percent'     => $v->overprice_percent,
            'underprice_percent'    => $v->underprice_percent,

            // Verdict
            'verdict'               => $v->verdict,
            'verdict_label'         => $v->verdict_label,
            'verdict_color'         => $v->verdict_color,
            'confidence_score'      => $v->confidence_score,
            'confidence_label'      => $v->confidence_label,

            // Comparables
            'comparable_count'      => count($v->comparable_property_ids ?? []),

            // Meta
            'model_version'         => $v->model_version,
            'predicted_at'          => $v->predicted_at?->toDateTimeString(),
        ];
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// MarketTrendsController
// GET /api/v1/market/trends
// ─────────────────────────────────────────────────────────────────────────────
class MarketTrendsController extends Controller
{
    /**
     * GET /api/v1/market/trends
     *
     * Returns time-series price data for charting in Flutter.
     *
     * Query params:
     *   area_id    : required — which area
     *   period     : 7d|30d|90d|1y  (default: 30d)
     *   type       : daily|weekly|monthly  (default: daily)
     *
     * Returns chart-ready data: array of { date, avg_price, avg_price_per_m2, listing_count }
     */
    public function index(Request $request): JsonResponse
    {
        $areaId = $request->integer('area_id');
        $period = $request->string('period', '30d')->toString();
        $type   = $request->string('type', 'daily')->toString();

        if (!$areaId) {
            return response()->json(['success' => false, 'message' => 'area_id is required'], 422);
        }

        $days = match ($period) {
            '7d'  => 7,
            '90d' => 90,
            '1y'  => 365,
            default => 30,
        };

        $cacheKey = "market_trends_{$areaId}_{$period}_{$type}";

        $data = Cache::remember($cacheKey, now()->addHours(2), function () use ($areaId, $days, $type) {
            $trends = MarketTrend::forArea($areaId)
                ->lastDays($days)
                ->where('period_type', $type)
                ->orderBy('snapshot_date')
                ->select([
                    'snapshot_date',
                    'avg_price',
                    'avg_price_per_m2',
                    'listing_count',
                    'demand_score',
                    'price_change_vs_yesterday'
                ])
                ->get();

            if ($trends->isEmpty()) {
                return ['chart_data' => [], 'summary' => null];
            }

            // Growth summary
            $firstPrice = $trends->first()->avg_price;
            $lastPrice  = $trends->last()->avg_price;
            $growth     = MarketTrend::computeGrowthPercent($firstPrice, $lastPrice);

            return [
                'chart_data' => $trends->map(fn($t) => [
                    'date'             => $t->snapshot_date->toDateString(),
                    'avg_price'        => $t->avg_price,
                    'avg_price_per_m2' => $t->avg_price_per_m2,
                    'listing_count'    => $t->listing_count,
                    'demand_score'     => $t->demand_score,
                ])->values(),
                'summary' => [
                    'period_growth_percent' => $growth,
                    'start_price'           => $firstPrice,
                    'end_price'             => $lastPrice,
                    'data_points'           => $trends->count(),
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/market/overview
     * High-level market overview for the home screen banner.
     * Returns top-level KPIs across all Kurdistan regions.
     */
    public function overview(Request $request): JsonResponse
    {
        $branchId = $request->integer('branch_id') ?: null;
        $cacheKey = "market_overview_{$branchId}";

        $data = Cache::remember($cacheKey, now()->addHours(3), function () use ($branchId) {
            $query = AreaMarketInsight::query();
            if ($branchId) {
                $query->whereHas('area', fn($q) => $q->where('branch_id', $branchId));
            }

            $insights = $query->get();

            return [
                'total_listings'      => $insights->sum('active_listings'),
                'avg_price_per_m2'    => round($insights->avg('average_price_per_m2'), 2),
                'avg_demand_score'    => round($insights->avg('demand_score'), 2),
                'top_price_tier'      => $insights->groupBy('price_tier')
                    ->map->count()
                    ->sortDesc()
                    ->keys()
                    ->first(),
                'areas_rising'        => $insights->where('price_growth_30d', '>', 2)->count(),
                'areas_declining'     => $insights->where('price_growth_30d', '<', -2)->count(),
                'best_investment_score' => $insights->max('investment_score'),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
