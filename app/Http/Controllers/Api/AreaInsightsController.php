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

        try {
            $data = Cache::remember($cacheKey, now()->addHours(2), function () use (
                $branchId,
                $tier,
                $sort,
                $request
            ) {
                $query = AreaMarketInsight::with(['area' => fn($q) => $q->select('id', 'area_name_en', 'area_name_ar', 'area_name_ku', 'branch_id')]);

                if ($branchId) {
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
        } catch (\Exception $e) {
            Log::error('AreaInsightsController@index failed', [
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'branch_id' => $branchId,
                'tier'    => $tier,
                'sort'    => $sort,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
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
