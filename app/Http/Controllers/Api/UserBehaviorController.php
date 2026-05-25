<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertyInteractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserBehaviorController extends Controller
{
    private PropertyInteractionService $interactionService;

    public function __construct(PropertyInteractionService $interactionService)
    {
        $this->interactionService = $interactionService;
    }

    /**
     * POST /api/v1/user/calculator-signal
     *
     * Fired silently from Flutter 2s after the user stops changing calculator
     * inputs. Stores the user's current budget intent so the recommendation
     * engine can boost matching properties.
     *
     * Always returns 200 — never blocks the UI.
     */
    public function storeCalculatorSignal(Request $request): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();

            // Guest or unauthenticated — silently ignore
            if (!$user) {
                return response()->json(['success' => true]);
            }

            $price   = (float) ($request->input('target_price_usd', 0));
            $saved   = (float) ($request->input('saved_so_far_usd', 0));
            $monthly = (float) ($request->input('monthly_usd', 0));
            $years   = (int)   ($request->input('target_years', 0));
            $mode    = $request->input('mode', 'how_long');

            // Delegate entirely to service — controller stays thin
            $this->interactionService->storeCalculatorSignal(
                userId: $user->id,
                targetPriceUsd: $price,
                savedSoFarUsd: $saved,
                monthlyUsd: $monthly,
                targetYears: $years,
                mode: $mode
            );

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            // Never fail — calculator must work even if this endpoint breaks
            Log::warning('calculator-signal endpoint failed (non-fatal)', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => true]);
        }
    }
}