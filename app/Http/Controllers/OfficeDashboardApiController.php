<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Appointment;
use App\Models\Project;
use App\Models\Property;
use App\Models\Subscription\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class OfficeDashboardApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            // ── Resolve authenticated office ──────────────────────────────────
            // Works with both Sanctum (API token) and the 'office' session guard
            $office = Auth::guard('sanctum')->user()
                ?? Auth::guard('office')->user();

            if (! $office) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $officeId    = $office->id;
            $officeType  = 'App\Models\RealEstateOffice';

            // ── Core Counts ───────────────────────────────────────────────────
            $totalProperties = Property::where('owner_type', $officeType)
                ->where('owner_id', $officeId)
                ->count();

            $activeProperties = Property::where('owner_type', $officeType)
                ->where('owner_id', $officeId)
                ->where('status', 'available')
                ->count();

            $soldProperties = Property::where('owner_type', $officeType)
                ->where('owner_id', $officeId)
                ->where('status', 'sold')
                ->count();

            $rentedProperties = Property::where('owner_type', $officeType)
                ->where('owner_id', $officeId)
                ->where('status', 'rented')
                ->count();

            $totalAgents = Agent::where('company_id', $officeId)->count();

            $totalProjects = Project::where('developer_type', $officeType)
                ->where('developer_id', $officeId)
                ->count();

            // ── Appointments ──────────────────────────────────────────────────
            $totalAppointments = Appointment::where('office_id', $officeId)->count();

            $pendingAppointments = Appointment::where('office_id', $officeId)
                ->where('status', 'pending')
                ->count();

            $todayAppointments = Appointment::where('office_id', $officeId)
                ->whereDate('appointment_date', today())
                ->count();

            $confirmedAppointments = Appointment::where('office_id', $officeId)
                ->where('status', 'confirmed')
                ->count();

            // ── Revenue ───────────────────────────────────────────────────────
            $allSold = Property::where('owner_type', $officeType)
                ->where('owner_id', $officeId)
                ->where('status', 'sold')
                ->get();

            $totalRevenue = $allSold->sum(function ($p) {
                $price = is_array($p->price) ? $p->price : json_decode($p->price, true);
                return $price['usd'] ?? 0;
            });

            // ── Growth (30-day vs prior 30-day) ───────────────────────────────
            $propertiesThisMonth = Property::where('owner_type', $officeType)
                ->where('owner_id', $officeId)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            $propertiesLastMonth = Property::where('owner_type', $officeType)
                ->where('owner_id', $officeId)
                ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
                ->count();

            $propertyGrowth = $propertiesLastMonth > 0
                ? round((($propertiesThisMonth - $propertiesLastMonth) / $propertiesLastMonth) * 100, 1)
                : ($propertiesThisMonth > 0 ? 100 : 0);

            $agentsThisMonth = Agent::where('company_id', $officeId)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            $agentsLastMonth = Agent::where('company_id', $officeId)
                ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
                ->count();

            $agentGrowth = $agentsLastMonth > 0
                ? round((($agentsThisMonth - $agentsLastMonth) / $agentsLastMonth) * 100, 1)
                : ($agentsThisMonth > 0 ? 100 : 0);

            $revenueThisMonth = Property::where('owner_type', $officeType)
                ->where('owner_id', $officeId)
                ->where('status', 'sold')
                ->where('updated_at', '>=', now()->subDays(30))
                ->get()
                ->sum(fn($p) => (is_array($p->price) ? $p->price : json_decode($p->price, true))['usd'] ?? 0);

            $revenueLastMonth = Property::where('owner_type', $officeType)
                ->where('owner_id', $officeId)
                ->where('status', 'sold')
                ->whereBetween('updated_at', [now()->subDays(60), now()->subDays(30)])
                ->get()
                ->sum(fn($p) => (is_array($p->price) ? $p->price : json_decode($p->price, true))['usd'] ?? 0);

            $revenueGrowth = $revenueLastMonth > 0
                ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
                : ($revenueThisMonth > 0 ? 100 : 0);

            // ── Recent Properties (latest 6) ──────────────────────────────────
            $recentProperties = Property::where('owner_type', $officeType)
                ->where('owner_id', $officeId)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get()
                ->map(fn($p) => [
                    'id'           => $p->id,
                    'name'         => is_array($p->name) ? ($p->name['en'] ?? '') : $p->name,
                    'status'       => $p->status,
                    'listing_type' => $p->listing_type,
                    'price'        => is_array($p->price) ? $p->price : json_decode($p->price, true),
                    'image'        => is_array($p->images) ? ($p->images[0] ?? null)
                        : (json_decode($p->images, true)[0] ?? null),
                    'created_at'   => $p->created_at?->toISOString(),
                ]);

            // ── Recent Appointments (latest 5) ────────────────────────────────
            $recentAppointments = Appointment::with(['user', 'property'])
                ->where('office_id', $officeId)
                ->orderBy('appointment_date', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($a) => [
                    'id'               => $a->id,
                    'status'           => $a->status,
                    'appointment_date' => $a->appointment_date?->toDateString(),
                    'appointment_time' => $a->appointment_time,
                    'client_name'      => $a->user?->name ?? 'Unknown',
                    'property_name'    => $a->property
                        ? (is_array($a->property->name)
                            ? ($a->property->name['en'] ?? '')
                            : $a->property->name)
                        : 'N/A',
                ]);

            // ── Top Agents ────────────────────────────────────────────────────
            $topAgents = Agent::where('company_id', $officeId)
                ->withCount(['ownedProperties' => fn($q) => $q->where('status', 'available')])
                ->orderBy('owned_properties_count', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($a) => [
                    'id'               => $a->id,
                    'name'             => $a->agent_name ?? $a->name ?? 'Agent',
                    'email'            => $a->primary_email ?? $a->email ?? '',
                    'profile_image'    => $a->profile_image ?? null,
                    'properties_count' => $a->owned_properties_count,
                    'is_verified'      => (bool) ($a->is_verified ?? false),
                ]);

            // ── Subscription ──────────────────────────────────────────────────
            $office->loadMissing('subscription.currentPlan');
            $subscription      = $office->subscription;
            $currentPlan       = $subscription?->currentPlan;
            $propertyLimitInfo = method_exists($office, 'getPropertyLimitInfo')
                ? $office->getPropertyLimitInfo()
                : ['used' => $totalProperties, 'limit' => 0, 'remaining' => 0, 'is_unlimited' => false];

            $subscriptionData = [
                'status'          => $subscription?->status ?? 'none',
                'plan_name'       => $currentPlan?->name ?? 'No Plan',
                'plan_type'       => $currentPlan?->type ?? null,
                'end_date'        => $subscription?->end_date?->toDateString(),
                'days_remaining'  => $subscription?->end_date
                    ? max(0, now()->diffInDays($subscription->end_date, false))
                    : 0,
                'is_active'       => method_exists($office, 'hasActiveSubscription')
                    ? $office->hasActiveSubscription()
                    : ($subscription?->status === 'active'),
                'property_limit'  => $propertyLimitInfo,
            ];

            // ── Monthly chart data (last 6 months) ────────────────────────────
            $chartData = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $count = Property::where('owner_type', $officeType)
                    ->where('owner_id', $officeId)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                $chartData[] = [
                    'month' => $month->format('M'),
                    'year'  => $month->year,
                    'count' => $count,
                ];
            }

            // ── Final Response ────────────────────────────────────────────────
            return response()->json([
                'success' => true,
                'data'    => [
                    'office' => [
                        'id'              => $office->id,
                        'company_name'    => $office->company_name,
                        'logo'            => $office->logo,
                        'profile_image'   => $office->profile_image,
                        'city'            => $office->city,
                        'is_verified'     => (bool) ($office->is_verified ?? false),
                    ],
                    'stats' => [
                        'total_properties'   => $totalProperties,
                        'active_properties'  => $activeProperties,
                        'sold_properties'    => $soldProperties,
                        'rented_properties'  => $rentedProperties,
                        'total_agents'       => $totalAgents,
                        'total_projects'     => $totalProjects,
                        'total_appointments' => $totalAppointments,
                        'pending_appointments'   => $pendingAppointments,
                        'confirmed_appointments' => $confirmedAppointments,
                        'today_appointments' => $todayAppointments,
                        'total_revenue_usd'  => $totalRevenue,
                        'revenue_this_month' => $revenueThisMonth,
                    ],
                    'growth' => [
                        'property_growth' => $propertyGrowth,
                        'agent_growth'    => $agentGrowth,
                        'revenue_growth'  => $revenueGrowth,
                    ],
                    'recent_properties'  => $recentProperties,
                    'recent_appointments' => $recentAppointments,
                    'top_agents'         => $topAgents,
                    'subscription'       => $subscriptionData,
                    'chart_data'         => $chartData,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[OfficeDashboardApi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    public function getAppointments(Request $request)
    {
        try {
            // Get the authenticated office from the Sanctum API token
            $office = $request->user();

            if (!$office) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Fetch appointments with related user, agent, and property data
            $appointments = Appointment::with(['user', 'agent', 'property'])
                ->where('office_id', $office->id)
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->get();

            // Calculate stats (optional, but good if you want to show them in the app later)
            $stats = [
                'total' => Appointment::where('office_id', $office->id)->count(),
                'pending' => Appointment::where('office_id', $office->id)->where('status', 'pending')->count(),
                'confirmed' => Appointment::where('office_id', $office->id)->where('status', 'confirmed')->count(),
                'completed' => Appointment::where('office_id', $office->id)->where('status', 'completed')->count(),
                'cancelled' => Appointment::where('office_id', $office->id)->where('status', 'cancelled')->count(),
            ];

            // Return clean JSON for Flutter
            return response()->json([
                'success' => true,
                'data' => $appointments,
                'stats' => $stats
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Get Appointments Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load appointments: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getProperties(Request $request)
    {
        try {
            // Get the authenticated office
            $office = $request->user();

            if (!$office) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Base query to get only this office's properties
            $query = \App\Models\Property::where('owner_type', 'App\Models\RealEstateOffice')
                ->where('owner_id', $office->id)
                ->orderBy('created_at', 'desc');

            // Handle the ?status= query parameter from Flutter
            if ($request->has('status')) {
                $status = $request->input('status');

                // Map Flutter's 'active' status to your database's 'available' status if needed
                if ($status === 'active') {
                    $query->where('status', 'available');
                } else {
                    $query->where('status', $status);
                }
            }

            $properties = $query->get();

            return response()->json([
                'success' => true,
                'data' => $properties
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('API Get Office Properties Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load properties: ' . $e->getMessage()
            ], 500);
        }
    }
}
