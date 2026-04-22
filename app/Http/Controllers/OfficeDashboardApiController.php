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


    public function getProfile(Request $request): JsonResponse
    {
        try {
            $office = Auth::guard('sanctum')->user()
                ?? Auth::guard('office')->user();

            if (! $office) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // Load subscription with its plan
            $office->loadMissing('subscription.currentPlan');
            $subscription = $office->subscription;
            $currentPlan  = $subscription?->currentPlan;

            $propertyLimitInfo = method_exists($office, 'getPropertyLimitInfo')
                ? $office->getPropertyLimitInfo()
                : [
                    'used'         => 0,
                    'limit'        => 0,
                    'remaining'    => 0,
                    'is_unlimited' => false,
                ];

            $subscriptionData = [
                'status'         => $subscription?->status ?? 'none',
                'plan_name'      => $currentPlan?->name ?? 'No Plan',
                'plan_type'      => $currentPlan?->type ?? null,
                'end_date'       => $subscription?->end_date?->toDateString(),
                'expires_at'     => $subscription?->end_date?->toIso8601String(),
                'days_remaining' => $subscription?->end_date
                    ? max(0, now()->diffInDays($subscription->end_date, false))
                    : 0,
                'is_active'      => method_exists($office, 'hasActiveSubscription')
                    ? $office->hasActiveSubscription()
                    : ($subscription?->status === 'active'),
                'property_limit' => $propertyLimitInfo,
            ];

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'                    => $office->id,
                    'company_name'          => $office->company_name,
                    'company_bio'           => $office->company_bio,
                    'company_bio_image'     => $office->company_bio_image,
                    'profile_image'         => $office->profile_image,
                    'account_type'          => $office->account_type ?? 'real_estate_official',
                    'current_plan'          => $currentPlan?->name,
                    'subscription_id'       => $office->subscription_id ?? $subscription?->id,
                    'is_verified'           => (bool) ($office->is_verified ?? false),
                    'average_rating'        => $office->average_rating ?? '0.00',
                    'email_address'         => $office->email ?? $office->email_address,
                    'phone_number'          => $office->phone_number,
                    'office_address'        => $office->office_address ?? $office->address,
                    'latitude'              => $office->latitude,
                    'longitude'             => $office->longitude,
                    'city'                  => $office->city,
                    'district'              => $office->district,
                    'properties_sold'       => $office->properties_sold ?? 0,
                    'years_experience'      => $office->years_experience ?? 0,
                    'about_company'         => $office->about_company,
                    'availability_schedule' => $office->availability_schedule,
                    'status'                => $office->status ?? 'active',
                    'subscription'          => $subscriptionData,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[OfficeDashboardApi::getProfile] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load office profile.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function getSubscriptionStatus(Request $request): JsonResponse
    {
        try {
            // 1. Get the authenticated office
            $office = $request->user();

            if (!$office) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            // 2. Load subscription data
            $office->loadMissing('subscription.currentPlan');
            $subscription = $office->subscription;
            $currentPlan = $subscription?->currentPlan;

            // 3. Determine if the subscription is active
            $isActive = method_exists($office, 'hasActiveSubscription')
                ? $office->hasActiveSubscription()
                : ($subscription?->status === 'active');

            // If not active, return 403 so the Flutter app knows to block publishing
            if (!$isActive) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need an active subscription to publish properties.',
                    'status' => $subscription?->status ?? 'none',
                    'is_unlimited' => false
                ], 403);
            }

            // 4. Calculate property limits
            $totalProperties = Property::where('owner_type', 'App\Models\RealEstateOffice')
                ->where('owner_id', $office->id)
                ->count();

            $propertyLimitInfo = method_exists($office, 'getPropertyLimitInfo')
                ? $office->getPropertyLimitInfo()
                : [
                    'used' => $totalProperties,
                    'limit' => $currentPlan?->property_limit ?? 0,
                    'remaining' => max(0, ($currentPlan?->property_limit ?? 0) - $totalProperties),
                    'is_unlimited' => ($currentPlan?->property_limit === -1 || $currentPlan?->property_limit === null)
                ];

            // 5. Block if limit is reached
            if (!$propertyLimitInfo['is_unlimited'] && $propertyLimitInfo['remaining'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property limit reached. Please upgrade your subscription plan to add more properties.',
                    'status' => 'limit_reached',
                    'is_unlimited' => false,
                    'limits' => $propertyLimitInfo
                ], 403); // Returning 403 triggers the Flutter app's block mechanism
            }

            // 6. Success! They are active and have remaining properties
            return response()->json([
                'success' => true,
                'message' => 'Subscription verified.',
                'status' => 'active',
                'is_unlimited' => $propertyLimitInfo['is_unlimited'],
                'plan_name' => $currentPlan?->name ?? 'Unknown Plan',
                'limits' => $propertyLimitInfo
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('API Get Office Subscription Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to check subscription status.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }



    /**
     * Update office general profile
     * PUT/POST /api/v1/office/profile
     *
     * Mirrors AgentController::update() pattern.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            // 1. Resolve authenticated office
            $office = Auth::guard('sanctum')->user()
                ?? Auth::guard('office')->user();

            if (! $office) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // 2. Validation
            $validator = Validator::make($request->all(), [
                // Basic Info
                'company_name'      => 'sometimes|string|max:255',
                'company_bio'       => 'nullable|string|max:2000',
                'about_company'     => 'nullable|string|max:5000',
                'phone_number'      => 'sometimes|string|max:20',

                // Location
                'office_address'    => 'nullable|string|max:500',
                'city'              => 'nullable|string|max:100',
                'district'          => 'nullable|string|max:100',
                // Accept string or numeric because Flutter may send "36.123"
                'latitude'          => 'nullable',
                'longitude'         => 'nullable',

                // Experience / Stats
                'years_experience'  => 'nullable|integer|min:0|max:100',

                // Availability Schedule (JSON string or array)
                'availability_schedule' => 'nullable',

                // Images
                'profile_image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'company_bio_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // 3. Start transaction
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 4. Build scalar update payload
            $updateData = $request->only([
                'company_name',
                'company_bio',
                'about_company',
                'phone_number',
                'office_address',
                'city',
                'district',
                'latitude',
                'longitude',
                'years_experience',
            ]);

            // 5. Handle availability_schedule (same as agent working_hours)
            if ($request->has('availability_schedule')) {
                $schedule = $request->availability_schedule;

                if (is_array($schedule)) {
                    // Flutter sent a Map/List — encode for DB
                    $updateData['availability_schedule'] = json_encode($schedule);
                } elseif (is_string($schedule)) {
                    // Validate it is real JSON before storing
                    $decoded = json_decode($schedule, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $updateData['availability_schedule'] = $schedule;
                    }
                }
            }

            // 6. Handle profile_image upload
            if ($request->hasFile('profile_image')) {
                // Delete old file if it exists on disk
                if ($office->profile_image && file_exists(public_path($office->profile_image))) {
                    @unlink(public_path($office->profile_image));
                }

                $dir = public_path('uploads/offices/profiles');
                if (! file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                $file     = $request->file('profile_image');
                $filename = 'office_profile_' . $office->id . '_' . time() . '.' . $file->extension();
                $file->move($dir, $filename);

                $updateData['profile_image'] = '/uploads/offices/profiles/' . $filename;
            }

            // 7. Handle company_bio_image upload
            if ($request->hasFile('company_bio_image')) {
                if ($office->company_bio_image && file_exists(public_path($office->company_bio_image))) {
                    @unlink(public_path($office->company_bio_image));
                }

                $dir = public_path('uploads/offices/bio');
                if (! file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                $file     = $request->file('company_bio_image');
                $filename = 'office_bio_' . $office->id . '_' . time() . '.' . $file->extension();
                $file->move($dir, $filename);

                $updateData['company_bio_image'] = '/uploads/offices/bio/' . $filename;
            }

            // 8. Persist
            $office->update($updateData);

            \Illuminate\Support\Facades\DB::commit();

            // 9. Return fresh model
            $office->refresh();

            Log::info('Office profile updated', [
                'office_id'    => $office->id,
                'company_name' => $office->company_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data'    => [
                    'id'                    => $office->id,
                    'company_name'          => $office->company_name,
                    'company_bio'           => $office->company_bio,
                    'company_bio_image'     => $office->company_bio_image,
                    'profile_image'         => $office->profile_image,
                    'phone_number'          => $office->phone_number,
                    'office_address'        => $office->office_address,
                    'city'                  => $office->city,
                    'district'              => $office->district,
                    'latitude'              => $office->latitude,
                    'longitude'             => $office->longitude,
                    'years_experience'      => $office->years_experience,
                    'about_company'         => $office->about_company,
                    'availability_schedule' => $office->availability_schedule,
                ],
            ], 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            Log::error('[OfficeDashboardApi::updateProfile] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update office language preference
     * PUT /api/v1/office/language
     *
     * Mirrors AgentController::updateLanguage() pattern.
     */
    public function updateLanguage(Request $request): JsonResponse
    {
        try {
            // 1. Resolve authenticated office
            $office = Auth::guard('sanctum')->user()
                ?? Auth::guard('office')->user();

            if (! $office) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // 2. Validate
            $validator = Validator::make($request->all(), [
                'language' => 'required|string|in:en,ar,ku',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // 3. Update
            $office->update(['language' => $request->language]);

            Log::info('Office language updated', [
                'office_id' => $office->id,
                'language'  => $request->language,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Language updated successfully',
                'data'    => [
                    'id'       => $office->id,
                    'language' => $office->language,
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('[OfficeDashboardApi::updateLanguage] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update language.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update FCM token for push notifications
     * POST /api/v1/office/fcm-token
     *
     * Mirrors AgentController::updateFCMToken() pattern.
     */
    public function updateFCMToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token'     => 'required|string',
            'old_fcm_token' => 'nullable|string',
            'device_name'   => 'nullable|string',
            'language'      => 'nullable|in:en,ar,ku',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $office = Auth::guard('sanctum')->user()
            ?? Auth::guard('office')->user();

        if (! $office) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Remove old token if provided (token rotation)
        if ($request->old_fcm_token) {
            $office->removeFCMToken($request->old_fcm_token);
        }

        // Add / update new token
        $office->addFCMToken($request->fcm_token, $request->device_name ?? 'Unknown Device');

        // Optionally persist language in the same call
        if ($request->filled('language')) {
            $office->update(['language' => $request->language]);
        }

        Log::info('Office FCM token updated', ['office_id' => $office->id]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token updated successfully',
        ], 200);
    }
}
