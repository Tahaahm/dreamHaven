<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AreaMarketInsight;
use App\Models\InvestmentScore;
use App\Models\MarketTrend;
use App\Models\PropertyValuation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\PipelineOrchestratorService;
use App\Services\PipelineOrchestratorService as ServicesPipelineOrchestratorService;

// ─────────────────────────────────────────────────────────────────────────────
// PropertyValuationController
// GET /api/v1/properties/ai-valuation/{propertyId}
// ─────────────────────────────────────────────────────────────────────────────
class PropertyValuationController extends Controller
{
    public function __construct(
        private ServicesPipelineOrchestratorService $pipeline
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
