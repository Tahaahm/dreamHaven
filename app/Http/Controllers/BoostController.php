<?php

// app/Http/Controllers/BoostController.php
// Dream Mulk — Property Boost API
// Endpoints:
//   POST   /api/v1/properties/{id}/boost/purchase     → buy a boost plan
//   POST   /api/v1/properties/{id}/boost/cancel       → cancel active boost
//   GET    /api/v1/properties/{id}/boost/status       → full boost status + analytics
//   GET    /api/v1/properties/{id}/boost/history      → all past boosts
//   GET    /api/v1/boost/plans                        → list available plans (public)

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Models\Property;
use App\Models\PropertyBoost;
use App\Models\UserPropertyInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BoostController extends Controller
{
    // ── Plan definitions (could also be stored in DB) ──────────────────────
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
    // GET /api/v1/boost/plans
    // Public — returns all available plans
    // ─────────────────────────────────────────────────────────────────────────
    public function getPlans()
    {
        return ApiResponse::success('Boost plans', array_values(self::$PLANS), 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/v1/properties/{id}/boost/purchase
    // Auth required. Body: { "plan_id": "growth" }
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

            // Ownership check — only the owner can boost
            if ((string) $property->owner_id !== (string) $user->id) {
                return ApiResponse::error('You do not own this property', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'plan_id'        => 'required|string|in:starter,growth,pro,max',
                'payment_ref'    => 'nullable|string|max:255', // FIB / wallet ref
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

            // Create boost record
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
                'views_at_start' => $property->views ?? 0,
                'reach_at_start' => 0,
                'meta'           => json_encode([
                    'estimated_reach' => $plan['estimated_reach'],
                    'estimated_views' => $plan['estimated_views'],
                    'ip'              => $request->ip(),
                ]),
            ]);

            // Update property boost flags
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
    // Returns full boost analytics dashboard data
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

            // Get active boost (or most recent)
            $boost = PropertyBoost::where('property_id', $id)
                ->latest()
                ->first();

            if (!$boost) {
                return ApiResponse::success('No boost found for this property', [
                    'has_boost'  => false,
                    'is_active'  => false,
                    'property'   => $this->propertyMini($property),
                ], 200);
            }

            $isActive  = $boost->status === 'active'
                && Carbon::parse($boost->end_date)->isFuture();

            // ── Core interaction counts ──────────────────────────────────────
            $boostStart = Carbon::parse($boost->start_date);
            $boostEnd   = Carbon::parse($boost->end_date);

            $totalViews = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'view')
                ->whereBetween('created_at', [$boostStart, now()])
                ->count();

            $uniqueReach = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'view')
                ->whereBetween('created_at', [$boostStart, now()])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');

            $impressions = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'impression')
                ->whereBetween('created_at', [$boostStart, now()])
                ->count();

            $savedCount = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'favorite')
                ->whereBetween('created_at', [$boostStart, now()])
                ->count();

            $shareCount = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'share')
                ->whereBetween('created_at', [$boostStart, now()])
                ->count();

            // Contact clicks (if you track this interaction type)
            $contactClicks = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'contact')
                ->whereBetween('created_at', [$boostStart, now()])
                ->count();

            // ── Baseline (7 days before boost) ──────────────────────────────
            $baselineStart = $boostStart->copy()->subDays(7);
            $baselineViews = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'view')
                ->whereBetween('created_at', [$baselineStart, $boostStart])
                ->count();
            $baselineReach = UserPropertyInteraction::where('property_id', $id)
                ->where('interaction_type', 'view')
                ->whereBetween('created_at', [$baselineStart, $boostStart])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');

            // ── Daily stats ──────────────────────────────────────────────────
            $daysElapsed = min(
                (int) $boostStart->diffInDays(now()) + 1,
                $boost->plan_id === 'starter' ? 3 : ($boost->plan_id === 'growth' ? 7 : ($boost->plan_id === 'pro' ? 14 : 30))
            );
            $dailyStats = $this->getDailyStats($id, $boostStart, $daysElapsed);

            // ── Traffic sources ──────────────────────────────────────────────
            $sources = $this->getTrafficSources($id, $boostStart);

            // ── Audience (mobile vs desktop) ─────────────────────────────────
            $audience = $this->getAudienceSplit($id, $boostStart);

            // ── Cost efficiency ──────────────────────────────────────────────
            $costPerView  = $totalViews > 0
                ? round($boost->amount_paid / $totalViews * 100, 4)
                : 0;
            $costPerReach = $uniqueReach > 0
                ? round($boost->amount_paid / $uniqueReach * 100, 4)
                : 0;

            return ApiResponse::success('Boost status retrieved', [
                'has_boost'  => true,
                'is_active'  => $isActive,
                'property'   => $this->propertyMini($property),
                'boost'      => $this->transformBoost($boost),

                // ── Analytics ────────────────────────────────────────────────
                'analytics' => [
                    'total_views'     => $totalViews,
                    'unique_reach'    => $uniqueReach,
                    'impressions'     => $impressions,
                    'saved_count'     => $savedCount,
                    'share_count'     => $shareCount,
                    'contact_clicks'  => $contactClicks,
                    'baseline_views'  => $baselineViews,
                    'baseline_reach'  => $baselineReach,
                    'views_uplift_pct' => $baselineViews > 0
                        ? round((($totalViews - $baselineViews) / $baselineViews) * 100, 1)
                        : 0,
                    'reach_uplift_pct' => $baselineReach > 0
                        ? round((($uniqueReach - $baselineReach) / $baselineReach) * 100, 1)
                        : 0,
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
                    ->where('status', '!=', 'cancelled')
                    ->sum('amount_paid'),
            ], 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get boost history', $e->getMessage(), 500);
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function getDailyStats(string $propertyId, Carbon $from, int $days): array
    {
        $results = [];
        for ($i = 0; $i < $days; $i++) {
            $dayStart = $from->copy()->addDays($i)->startOfDay();
            $dayEnd   = $dayStart->copy()->endOfDay();

            if ($dayStart->isFuture()) break;

            $results[] = [
                'date'  => $dayStart->toDateString(),
                'views' => UserPropertyInteraction::where('property_id', $propertyId)
                    ->where('interaction_type', 'view')
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count(),
                'reach' => UserPropertyInteraction::where('property_id', $propertyId)
                    ->where('interaction_type', 'view')
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->whereNotNull('user_id')
                    ->distinct('user_id')
                    ->count('user_id'),
                'saves' => UserPropertyInteraction::where('property_id', $propertyId)
                    ->where('interaction_type', 'favorite')
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count(),
            ];
        }
        return $results;
    }

    private function getTrafficSources(string $propertyId, Carbon $from): array
    {
        // Source is stored in metadata->source_endpoint on impressions/views
        $rows = UserPropertyInteraction::where('property_id', $propertyId)
            ->where('interaction_type', 'view')
            ->where('created_at', '>=', $from)
            ->select(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.source_endpoint')) as source, COUNT(*) as cnt"))
            ->groupBy('source')
            ->get();

        $map = ['search' => 0, 'featured' => 0, 'notification' => 0, 'direct' => 0];
        foreach ($rows as $row) {
            $src = strtolower($row->source ?? 'direct');
            if (str_contains($src, 'search'))       $map['search']       += $row->cnt;
            elseif (str_contains($src, 'featured')) $map['featured']     += $row->cnt;
            elseif (str_contains($src, 'notif'))    $map['notification'] += $row->cnt;
            else                                    $map['direct']       += $row->cnt;
        }

        return [
            'search_views'       => $map['search'],
            'featured_views'     => $map['featured'],
            'notification_views' => $map['notification'],
            'direct_views'       => $map['direct'],
        ];
    }

    private function getAudienceSplit(string $propertyId, Carbon $from): array
    {
        // User-agent is stored in metadata->user_agent
        $rows = UserPropertyInteraction::where('property_id', $propertyId)
            ->where('interaction_type', 'view')
            ->where('created_at', '>=', $from)
            ->select(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.user_agent')) as ua"))
            ->get();

        $mobile  = 0;
        $desktop = 0;
        foreach ($rows as $row) {
            $ua = strtolower($row->ua ?? '');
            if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
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