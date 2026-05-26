<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SmartStripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helper\ApiResponse;


// ═══════════════════════════════════════════════════════════════════════════════
//  SmartStripController
//
//  Route: GET /api/v1/properties/smart-strip
//  Auth:  sanctum (required — guests get null strip)
//
//  Response shape:
//  {
//    "status": true,
//    "data": {
//      "strip": {
//        "type":       "resume_search",
//        "intent":     "active_searcher",
//        "confidence": 0.85,
//        "icon":       "search",
//        "headline":   "resume_search_headline",
//        "subline":    "resume_search_subline",
//        "params": {
//          "label_parts":     ["Villa", "Rent", "Erbil"],
//          "total_count":     47,
//          "new_since_visit": 12
//        },
//        "filters": {
//          "listing_type":  "rent",
//          "property_type": "villa",
//          "city":          "Erbil"
//        },
//        "count":      47,
//        "properties": [ ...5 preview properties... ]
//      }
//      // OR "strip": null  if no confident strip found
//    }
//  }
// ═══════════════════════════════════════════════════════════════════════════════

class SmartStripController extends Controller
{
    public function __construct(
        private readonly SmartStripService $smartStripService
    ) {}

    // ── GET /api/v1/properties/smart-strip ────────────────────────────────────
    public function getStrip(Request $request)
    {
        $user = auth('sanctum')->user();

        // Guest users → no strip (they have no signals)
        if (!$user) {
            return ApiResponse::success('No strip for guest', ['strip' => null], 200);
        }

        $language = $request->get('language', 'en');

        try {
            $strip = $this->smartStripService->getStrip(
                userId: (string) $user->id,
                language: $language
            );

            return ApiResponse::success(
                $strip ? 'Smart strip ready' : 'No strip available',
                ['strip' => $strip],
                200
            );
        } catch (\Throwable $e) {
            Log::error('SmartStrip endpoint error', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            // Always return 200 — Flutter falls back gracefully to null strip
            return ApiResponse::success('No strip available', ['strip' => null], 200);
        }
    }

    // ── POST /api/v1/properties/smart-strip/dismiss ───────────────────────────
    // Called when user taps × on the strip — invalidates cache so next load
    // the strip algorithm runs fresh and won't show the same strip again
    public function dismiss(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return ApiResponse::success('OK', [], 200);
        }

        try {
            $this->smartStripService->invalidate((string) $user->id);
        } catch (\Throwable $e) {
            // non-fatal
        }

        return ApiResponse::success('Strip dismissed', [], 200);
    }
}
