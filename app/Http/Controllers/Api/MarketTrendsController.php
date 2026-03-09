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
