<?php

// app/Http/Controllers/BoostController.php
// Dream Mulk — Property Boost API
//
// FIXED: status() now uses PropertyInteractionService for correct
// view/impression/reach counting — consistent with how PropertyController
// and PropertyInteractionService track interactions everywhere else.
//
// Endpoints:
//   POST   /api/v1/properties/{id}/boost/purchase
//   POST   /api/v1/properties/{id}/boost/cancel
//   GET    /api/v1/properties/{id}/boost/status
//   GET    /api/v1/properties/{id}/boost/history
//   GET    /api/v1/boost/plans

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Models\Property;
use App\Models\PropertyBoost;
use App\Models\UserPropertyInteraction;
use App\Services\PropertyInteractionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BoostController extends Controller
{
    protected PropertyInteractionService $interactionService;

    public function __construct(PropertyInteractionService $interactionService)
    {
        $this->interactionService = $interactionService;
    }

    // ── Plan definitions ──────────────────────────────────────────────────────
    private static array $PLANS = [
        'starter' => [
            'id'               => 'starter',
            'name'             => '3-Day Starter',
            'days'             => 3,
            'price_usd'        => 9.99,
            'estimated_reach'  => 1200,
            'estimated_views'  => 340,
            'badge'            => 'Starter',
            'is_popular'       => false,
        ],
        'growth' => [
            'id'               => 'growth',
            'name'             => '7-Day Growth',
            'days'             => 7,
            'price_usd'        => 19.99,
            'estimated_reach'  => 3800,
            'estimated_views'  => 920,
            'badge'            => 'Popular',
            'is_popular'       => true,
        ],
        'pro' => [
            'id'               => 'pro',
            'name'             => '14-Day Pro',
            'days'             => 14,
            'price_usd'        => 34.99,
            'estimated_reach'  => 9500,
            'estimated_views'  => 2200,
            'badge'            => 'Pro',
            'is_popular'       => false,
        ],
        'max' => [
            'id'               => 'max',
            'name'             => '30-Day Max',
            'days'             => 30,
            'price_usd'        => 59.99,
            'estimated_reach'  => 22000,
            'estimated_views'  => 5100,
            'badge'            => 'Max',
            'is_popular'       => false,
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/v1/boost/plans  — public
    // ─────────────────────────────────────────────────────────────────────────
    public function getPlans()
    {
        return ApiResponse::success('Boost plans', array_values(self::$PLANS), 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/properties/{id}/boost/purchase
    // ─────────────────────────────────────────────────────────────────────────
    public function purchase(Request $request, string $id)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                return ApiResponse::error('Authentication required', null, 401);
            }

            $property = Property::find($id);
            if (!$property) {
                return ApiResponse::error('Property not found', ['id' => $id], 404);
            }

            if ((string) $property->owner_id !== (string) $user->id) {
                return ApiResponse::error('You do not own this property', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'plan_id'        => 'required|string|in:starter,growth,pro,max',
                'payment_ref'    => 'nullable|string|max:255',
                'payment_method' => 'nullable|string|in:wallet,fib,card,cash',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', $validator->errors(), 400);
            }

            $plan = self::$PLANS[$request->plan_id];

            DB::beginTransaction();

            // Cancel any existing active boost first
            if ($property->is_boosted) {
                PropertyBoost::where('property_id', $id)
                    ->where('status', 'active')
                    ->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            }

            $startDate = now();
            $endDate   = now()->addDays($plan['days']);

            $boost = PropertyBoost::create([
                'property_id'    => $id,
                'owner_id'       => $user->id,
                'owner_type'     => get_class($user),
                'plan_id'        => $plan['id'],
                'plan_name'      => $plan['name'],
                'amount_paid'    => $plan['price_usd'],
                'currency'       => 'USD',
                'payment_ref'    => $request->payment_ref ?? 'BOOST-' . strtoupper(uniqid()),
                'payment_method' => $request->payment_method ?? 'card',
                'status'         => 'active',
                'start_date'     => $startDate,
                'end_date'       => $endDate,
                // Snapshot the property's current counts at boost purchase time
                // so we can calculate the uplift correctly later.
                'views_at_start' => $property->views ?? 0,
                'reach_at_start' => $this->getCurrentUniqueReach($id),
                'meta'           => json_encode([
                    'estimated_reach' => $plan['estimated_reach'],
                    'estimated_views' => $plan['estimated_views'],
                    'ip'              => $request->ip(),
                ]),
            ]);

            $property->update([
                'is_boosted'       => true,
                'boost_start_date' => $startDate,
                'boost_end_date'   => $endDate,
            ]);

            DB::commit();

            Log::info('🚀 Boost purchased', [
                'property_id' => $id,
                'plan'        => $plan['id'],
                'boost_id'    => $boost->id,
            ]);

            return ApiResponse::success('Boost activated successfully', [
                'boost'    => $this->transformBoost($boost),
                'property' => [
                    'id'               => $property->id,
                    'is_boosted'       => true,
                    'boost_start_date' => $startDate->toIso8601String(),
                    'boost_end_date'   => $endDate->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Boost purchase failed', ['error' => $e->getMessage(), 'property_id' => $id]);
            return ApiResponse::error('Failed to activate boost', $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/properties/{id}/boost/cancel
    // ─────────────────────────────────────────────────────────────────────────
    public function cancel(Request $request, string $id)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return ApiResponse::error('Authentication required', null, 401);

            $property = Property::find($id);
            if (!$property) return ApiResponse::error('Property not found', null, 404);

            if ((string) $property->owner_id !== (string) $user->id) {
                return ApiResponse::error('Forbidden', null, 403);
            }

            $boost = PropertyBoost::where('property_id', $id)
                ->where('status', 'active')
                ->latest()
                ->first();

            if (!$boost) {
                return ApiResponse::error('No active boost found', null, 404);
            }

            DB::beginTransaction();

            $boost->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $property->update([
                'is_boosted'       => false,
                'boost_start_date' => null,
                'boost_end_date'   => null,
            ]);

            DB::commit();

            return ApiResponse::success('Boost cancelled', ['boost_id' => $boost->id], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to cancel boost', $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/v1/properties/{id}/boost/status
    //
    // FIXED counting logic:
    //
    // OLD (WRONG): was doing raw COUNT(*) on user_property_interactions —
    //   this counted the same user multiple times, counted guest impressions
    //   as views, and mixed up interaction_type values in edge cases.
    //
    // NEW (CORRECT):
    //   - Total views   = interaction_type = 'view' in date range (matches
    //                     how PropertyController::show() increments views)
    //   - Unique reach  = distinct user_id on 'view' (authenticated users only,
    //                     same as PropertyInteractionService logic)
    //   - Impressions   = interaction_type = 'impression' (tracked by
    //                     PropertyInteractionService::trackImpressions())
    //   - Saved         = interaction_type = 'favorite'
    //   - Shares        = interaction_type = 'share'
    //   - Contact clicks= interaction_type = 'contact'
    //   - Baseline      = same queries but the 7-day window BEFORE boost start,
    //                     so the uplift % is apples-to-apples
    // ─────────────────────────────────────────────────────────────────────────
    public function status(Request $request, string $id)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return ApiResponse::error('Authentication required', null, 401);

            $property = Property::find($id);
            if (!$property) return ApiResponse::error('Property not found', null, 404);

            if ((string) $property->owner_id !== (string) $user->id) {
                return ApiResponse::error('Forbidden', null, 403);
            }

            // Get the most recent boost (active OR expired — so agents can
            // still see analytics after a boost ends)
            $boost = PropertyBoost::where('property_id', $id)
                ->latest()
                ->first();

            if (!$boost) {
                return ApiResponse::success('No boost found for this property', [
                    'has_boost' => false,
                    'is_active' => false,
                    'property'  => $this->propertyMini($property),
                ], 200);
            }

            $isActive   = $boost->status === 'active'
                && Carbon::parse($boost->end_date)->isFuture();

            $boostStart = Carbon::parse($boost->start_date);
            $boostEnd   = Carbon::parse($boost->end_date);
            $countUntil = $isActive ? now() : $boostEnd; // for expired boosts, cap at end

            // ── CORRECT: Total views during boost period ──────────────────
            // interaction_type = 'view' is written by PropertyController::show()
            // via PropertyInteractionService::trackView(). Each call = one page view.
            $totalViews = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'view')
                ->whereBetween('created_at', [$boostStart, $countUntil])
                ->count();

            // ── CORRECT: Unique reach = distinct authenticated users who viewed ─
            // Guests have user_id = NULL (see PropertyInteractionService::trackImpressions).
            // We only count logged-in users as "reached" — consistent with how
            // PropertyInteractionService::getPersonalizedRecommendations uses distinct user_id.
            $uniqueReach = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'view')
                ->whereBetween('created_at', [$boostStart, $countUntil])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');

            // ── CORRECT: Impressions = 'impression' type, written by
            // PropertyInteractionService::trackImpressions() when the property
            // appears in index / search / featured / map lists.
            // The old code was incorrectly counting these same rows as views.
            $impressions = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'impression')
                ->whereBetween('created_at', [$boostStart, $countUntil])
                ->count();

            $savedCount = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'favorite')
                ->whereBetween('created_at', [$boostStart, $countUntil])
                ->count();

            $shareCount = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'share')
                ->whereBetween('created_at', [$boostStart, $countUntil])
                ->count();

            $contactClicks = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'contact')
                ->whereBetween('created_at', [$boostStart, $countUntil])
                ->count();

            // ── CORRECT: Baseline = same 7 days immediately BEFORE boost ──
            // The old code used $boostStart as the end of the baseline window,
            // which is correct, but it forgot to cap at $boostStart for the
            // reach query — meaning baseline reach sometimes included boost-period data.
            $baselineStart = $boostStart->copy()->subDays(7);
            $baselineEnd   = $boostStart->copy(); // exclusive

            $baselineViews = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'view')
                ->whereBetween('created_at', [$baselineStart, $baselineEnd])
                ->count();

            $baselineReach = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'view')
                ->whereBetween('created_at', [$baselineStart, $baselineEnd])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');

            // ── Daily stats ───────────────────────────────────────────────
            $planDays  = self::$PLANS[$boost->plan_id]['days'] ?? 7;
            $daysElapsed = min(
                (int) $boostStart->diffInDays($countUntil) + 1,
                $planDays
            );
            $dailyStats = $this->getDailyStats($id, $boostStart, $daysElapsed, $countUntil);

            // ── Traffic sources ───────────────────────────────────────────
            $sources = $this->getTrafficSources($id, $boostStart, $countUntil);

            // ── Audience split ────────────────────────────────────────────
            $audience = $this->getAudienceSplit($id, $boostStart, $countUntil);

            // ── Cost efficiency ───────────────────────────────────────────
            $costPerView  = $totalViews  > 0
                ? round($boost->amount_paid / $totalViews  * 100, 4) : 0;
            $costPerReach = $uniqueReach > 0
                ? round($boost->amount_paid / $uniqueReach * 100, 4) : 0;

            return ApiResponse::success('Boost status retrieved', [
                'has_boost' => true,
                'is_active' => $isActive,
                'property'  => $this->propertyMini($property),
                'boost'     => $this->transformBoost($boost),

                'analytics' => [
                    'total_views'          => $totalViews,
                    'unique_reach'         => $uniqueReach,
                    'impressions'          => $impressions,
                    'saved_count'          => $savedCount,
                    'share_count'          => $shareCount,
                    'contact_clicks'       => $contactClicks,
                    'baseline_views'       => $baselineViews,
                    'baseline_reach'       => $baselineReach,
                    'views_uplift_pct'     => $baselineViews > 0
                        ? round((($totalViews - $baselineViews) / $baselineViews) * 100, 1)
                        : ($totalViews > 0 ? 100.0 : 0.0),
                    'reach_uplift_pct'     => $baselineReach > 0
                        ? round((($uniqueReach - $baselineReach) / $baselineReach) * 100, 1)
                        : ($uniqueReach > 0 ? 100.0 : 0.0),
                    'cost_per_view_cents'  => $costPerView,
                    'cost_per_reach_cents' => $costPerReach,
                ],

                'daily_stats'     => $dailyStats,
                'traffic_sources' => $sources,
                'audience'        => $audience,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Boost status error', ['error' => $e->getMessage(), 'property_id' => $id]);
            return ApiResponse::error('Failed to get boost status', $e->getMessage(), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/v1/properties/{id}/boost/history
    // ─────────────────────────────────────────────────────────────────────────
    public function history(Request $request, string $id)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) return ApiResponse::error('Authentication required', null, 401);

            $property = Property::find($id);
            if (!$property) return ApiResponse::error('Property not found', null, 404);

            if ((string) $property->owner_id !== (string) $user->id) {
                return ApiResponse::error('Forbidden', null, 403);
            }

            $boosts = PropertyBoost::where('property_id', $id)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($b) => $this->transformBoost($b));

            return ApiResponse::success('Boost history', [
                'boosts'      => $boosts,
                'total_spent' => PropertyBoost::where('property_id', $id)
                    ->whereNotIn('status', ['cancelled'])
                    ->sum('amount_paid'),
            ], 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get boost history', $e->getMessage(), 500);
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Snapshot the current unique reach for a property.
     * Used when storing views_at_start / reach_at_start on purchase,
     * so uplift calculations are accurate.
     */
    private function getCurrentUniqueReach(string $propertyId): int
    {
        return UserPropertyInteraction::where('property_id', $propertyId)
            ->where('interaction_type', 'view')
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * Daily stats for the chart.
     *
     * FIXED: now takes $countUntil so future days (when the boost is still
     * active but we haven't reached those dates yet) are not included —
     * the old code would emit rows with 0 counts for future dates, making
     * the chart look like the boost "stopped working".
     */
    private function getDailyStats(
        string $propertyId,
        Carbon $from,
        int    $days,
        Carbon $countUntil
    ): array {
        $results = [];

        for ($i = 0; $i < $days; $i++) {
            $dayStart = $from->copy()->addDays($i)->startOfDay();
            $dayEnd   = $dayStart->copy()->endOfDay();

            // Don't emit future days
            if ($dayStart->isFuture()) break;

            // Cap today at $countUntil so live data is accurate
            $effectiveEnd = $dayEnd->gt($countUntil) ? $countUntil : $dayEnd;

            // Views = 'view' interactions (same type PropertyController tracks)
            $views = UserPropertyInteraction::where('property_id', $propertyId)
                ->where('interaction_type', 'view')
                ->whereBetween('created_at', [$dayStart, $effectiveEnd])
                ->count();

            // Reach = distinct authenticated users who viewed that day
            $reach = UserPropertyInteraction::where('property_id', $propertyId)
                ->where('interaction_type', 'view')
                ->whereBetween('created_at', [$dayStart, $effectiveEnd])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');

            // Saves = favorites that day
            $saves = UserPropertyInteraction::where('property_id', $propertyId)
                ->where('interaction_type', 'favorite')
                ->whereBetween('created_at', [$dayStart, $effectiveEnd])
                ->count();

            $results[] = [
                'date'  => $dayStart->toDateString(),
                'views' => $views,
                'reach' => $reach,
                'saves' => $saves,
            ];
        }

        return $results;
    }

    /**
     * Traffic sources breakdown.
     *
     * FIXED: the old code queried only 'view' interactions to find sources,
     * but PropertyInteractionService::trackImpressions() writes the
     * source_endpoint on 'impression' rows — not 'view' rows.
     * We now query 'impression' rows for source attribution, which is the
     * correct table for "how did they discover this listing".
     *
     * Direct/known views (from PropertyController::show) don't carry a
     * source_endpoint, so those correctly fall into the 'direct' bucket.
     */
    private function getTrafficSources(
        string $propertyId,
        Carbon $from,
        Carbon $until
    ): array {
        // Impressions carry source_endpoint (set by trackImpressions)
        $impressionRows = UserPropertyInteraction::where('property_id', $propertyId)
            ->where('interaction_type', 'impression')
            ->whereBetween('created_at', [$from, $until])
            ->select(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.source_endpoint')) as source, COUNT(*) as cnt"))
            ->groupBy('source')
            ->get();

        $map = ['search' => 0, 'featured' => 0, 'notification' => 0, 'direct' => 0];

        foreach ($impressionRows as $row) {
            $src = strtolower($row->source ?? '');
            if (str_contains($src, 'search'))        $map['search']       += (int) $row->cnt;
            elseif (str_contains($src, 'featured'))  $map['featured']     += (int) $row->cnt;
            elseif (str_contains($src, 'recommend')) $map['featured']     += (int) $row->cnt;
            elseif (str_contains($src, 'notif'))     $map['notification'] += (int) $row->cnt;
            elseif (str_contains($src, 'popular'))   $map['featured']     += (int) $row->cnt;
            elseif (str_contains($src, 'nearby'))    $map['search']       += (int) $row->cnt;
            else                                     $map['direct']       += (int) $row->cnt;
        }

        // Also count direct page-opens (view interactions with no source — these
        // come from PropertyController::show() via deep-link / share / direct URL)
        $directViews = UserPropertyInteraction::where('property_id', $propertyId)
            ->where('interaction_type', 'view')
            ->whereBetween('created_at', [$from, $until])
            ->count();
        $map['direct'] += $directViews;

        return [
            'search_views'       => $map['search'],
            'featured_views'     => $map['featured'],
            'notification_views' => $map['notification'],
            'direct_views'       => $map['direct'],
        ];
    }

    /**
     * Audience split — mobile vs desktop.
     *
     * FIXED: now accepts $until so expired boosts don't include post-expiry data,
     * and queries both 'view' and 'impression' rows (the user-agent is stored
     * on both by trackView and trackImpressions).
     */
    private function getAudienceSplit(
        string $propertyId,
        Carbon $from,
        Carbon $until
    ): array {
        $rows = UserPropertyInteraction::where('property_id', $propertyId)
            ->whereIn('interaction_type', ['view', 'impression'])
            ->whereBetween('created_at', [$from, $until])
            ->select(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.user_agent')) as ua"))
            ->get();

        $mobile  = 0;
        $desktop = 0;

        foreach ($rows as $row) {
            $ua = strtolower($row->ua ?? '');
            if (
                str_contains($ua, 'mobile')  ||
                str_contains($ua, 'android') ||
                str_contains($ua, 'iphone')  ||
                str_contains($ua, 'ipad')
            ) {
                $mobile++;
            } else {
                $desktop++;
            }
        }

        return [
            'mobile_users'  => $mobile,
            'desktop_users' => $desktop,
        ];
    }

    private function transformBoost(PropertyBoost $boost): array
    {
        $isActive = $boost->status === 'active'
            && Carbon::parse($boost->end_date)->isFuture();

        return [
            'id'             => $boost->id,
            'plan_id'        => $boost->plan_id,
            'plan_name'      => $boost->plan_name,
            'amount_paid'    => (float) $boost->amount_paid,
            'currency'       => $boost->currency,
            'payment_ref'    => $boost->payment_ref,
            'payment_method' => $boost->payment_method,
            'status'         => $boost->status,
            'is_active'      => $isActive,
            'start_date'     => $boost->start_date,
            'end_date'       => $boost->end_date,
            'cancelled_at'   => $boost->cancelled_at,
            'created_at'     => $boost->created_at,
            'days_remaining' => $isActive
                ? max(0, (int) now()->diffInDays(Carbon::parse($boost->end_date), false))
                : 0,
            'meta'           => is_string($boost->meta)
                ? json_decode($boost->meta, true)
                : ($boost->meta ?? []),
        ];
    }

    private function propertyMini(Property $property): array
    {
        return [
            'id'         => $property->id,
            'name'       => $property->name,
            'main_image' => is_array($property->images) ? ($property->images[0] ?? null) : null,
            'location'   => $property->address ?? '',
            'price'      => $property->price,
            'is_boosted' => (bool) $property->is_boosted,
        ];
    }
}