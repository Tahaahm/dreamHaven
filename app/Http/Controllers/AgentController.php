<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\FacadesLog;
use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Agent;
use App\Models\Appointment;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\RealEstateOffice;
use App\Models\Support\UserFavoriteProperty;
use Illuminate\Support\Facades\Hash;
use App\Models\Subscription\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\AutoSubscriptionService;
use App\Services\FCMNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::all();
        return ApiResponse::success(
            ResponseDetails::successMessage('Agents retrieved successfully'),
            $agents,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function search(Request $request)
    {
        $query = Agent::query();

        if ($request->has('name')) {
            $query->where('agent_name', 'like', '%' . $request->name . '%');
        }
        if ($request->has('city')) {
            $query->where('city', $request->city);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $agents = $query->get();
        return ApiResponse::success(
            ResponseDetails::successMessage('Search results retrieved successfully'),
            $agents,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'nullable|string',
            'fcm_token'   => 'nullable|string',  // ← ADD
            'language'    => 'nullable|in:en,ar,ku', // ← ADD
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $email      = $request->email;
        $password   = $request->password;
        $deviceName = $request->device_name ?? 'Unknown Device';
        $fcmToken   = $request->fcm_token;
        $language   = $request->language;

        // 1️⃣ Try logging in as a normal User
        $user = User::where('email', $email)->first();
        if ($user && Hash::check($password, $user->password)) {

            // Save FCM token if provided
            if ($fcmToken) {
                $user->addFCMToken($fcmToken, $deviceName);
            }

            // Save language if provided
            if ($language) {
                $user->update(['language' => $language]);
            }

            $token = $user->createToken($deviceName)->plainTextToken;

            return ApiResponse::success(
                ResponseDetails::successMessage('Logged in successfully as user'),
                ['token' => $token, 'user' => $user, 'role' => 'user'],
                ResponseDetails::CODE_SUCCESS
            );
        }

        // 2️⃣ Try logging in as an Agent
        $agent = Agent::where('primary_email', $email)->first();
        if ($agent && Hash::check($password, $agent->password)) {

            // Save FCM token if provided
            if ($fcmToken) {
                $agent->addFCMToken($fcmToken, $deviceName);
            }

            // Save language if provided
            if ($language) {
                $agent->update(['language' => $language]);
            }

            $token = $agent->createToken($deviceName)->plainTextToken;

            return ApiResponse::success(
                ResponseDetails::successMessage('Logged in successfully as agent'),
                ['token' => $token, 'agent' => $agent, 'role' => 'agent'],
                ResponseDetails::CODE_SUCCESS
            );
        }

        // 3️⃣ Invalid Credentials
        return ApiResponse::error(
            'Invalid credentials',
            ['email' => ['These credentials do not match our records.']],
            401
        );
    }

    public function getTopRated()
    {
        $agents = Agent::orderBy('overall_rating', 'desc')->limit(10)->get();
        return ApiResponse::success(
            ResponseDetails::successMessage('Top rated agents retrieved successfully'),
            $agents,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function getNearbyAgents(Request $request)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $radius = $request->input('radius', 10); // default 10km

        if (!$lat || !$lng) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                ['lat and lng are required'],
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        // Simple distance calculation (you might want to use a more sophisticated method)
        $agents = Agent::selectRaw("*,
            (6371 * acos(cos(radians(?))
            * cos(radians(latitude))
            * cos(radians(longitude) - radians(?))
            + sin(radians(?))
            * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->get();

        return ApiResponse::success(
            ResponseDetails::successMessage('Nearby agents retrieved successfully'),
            $agents,
            ResponseDetails::CODE_SUCCESS
        );
    }
    /**
     * Update agent profile (including images)
     * POST /v1/api/agents/{id} (with _method=PUT for multipart)
     */
    public function update(Request $request, $id)
    {
        try {
            // 1. Find the agent
            $agent = Agent::find($id);

            if (!$agent) {
                return response()->json([
                    'status' => false,
                    'message' => 'Agent not found'
                ], 404);
            }

            // 2. Get the currently authenticated user (Could be User OR Agent model)
            $user = $request->user();

            // 3. Robust Authorization Logic
            // Check if the logged-in entity is the Agent itself
            $isSelf = ($user->id === $agent->id) && ($user instanceof \App\Models\Agent);

            // Check if the logged-in entity is the "Owner" User (using subscriber_id)
            // We use ?? null to prevent errors if a column is missing
            $ownerId = $agent->subscriber_id ?? $agent->user_id ?? null;
            $isOwner = ($user->id === $ownerId) && ($user instanceof \App\Models\User);

            // Check if Admin
            $isAdmin = false;
            if (isset($user->role) && $user->role === 'admin') {
                $isAdmin = true;
            }

            // Log for debugging
            Log::info('AGENT UPDATE AUTH CHECK', [
                'request_user_id' => $user->id,
                'target_agent_id' => $agent->id,
                'is_self' => $isSelf,
                'is_owner' => $isOwner,
                'is_admin' => $isAdmin
            ]);

            // Gatekeeper
            if (!$isSelf && !$isOwner && !$isAdmin) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to update this profile'
                ], 403);
            }

            // 4. Validation rules
            $validator = Validator::make($request->all(), [
                // Basic Info
                'agent_name' => 'sometimes|string|max:255',
                'primary_phone' => 'sometimes|string|max:20',
                'whatsapp_number' => 'nullable|string|max:20',
                'years_experience' => 'nullable|integer|min:0|max:50',

                // Bio & Overview
                'agent_bio' => 'nullable|string|max:1000',
                'agent_overview' => 'nullable|string|max:2000',

                // Professional
                'license_number' => 'nullable|string|max:100',

                // Location
                'office_address' => 'nullable|string|max:500',
                'district' => 'nullable|string|max:100',
                // Accept string or numeric for lat/lng because Flutter might send "36.123" string
                'latitude' => 'nullable',
                'longitude' => 'nullable',
                'city_id' => 'nullable',
                'area_id' => 'nullable',

                // Fees
                'commission_rate' => 'nullable|string|max:10',
                'consultation_fee' => 'nullable|string|max:20',

                // Working Hours (Accepts JSON string or Array)
                'working_hours' => 'nullable',

                // Images
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'bio_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 5. Start transaction
            DB::beginTransaction();

            // Prepare data for update
            $updateData = $request->only([
                'agent_name',
                'primary_phone',
                'whatsapp_number',
                'years_experience',
                'agent_bio',
                'agent_overview',
                'license_number',
                'office_address',
                'district',
                'latitude',
                'longitude',
                'city_id',
                'area_id',
                'commission_rate',
                'consultation_fee'
            ]);

            // 6. Handle Working Hours (Array vs String fix)
            if ($request->has('working_hours')) {
                $workingHours = $request->working_hours;

                // If it's already an array (from Flutter Map), encode it for DB
                if (is_array($workingHours)) {
                    $updateData['working_hours'] = json_encode($workingHours);
                }
                // If it's a JSON string, decode then re-encode to ensure validity, or save as is
                else if (is_string($workingHours)) {
                    // Optional: Validate it's real JSON
                    $decoded = json_decode($workingHours, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $updateData['working_hours'] = $workingHours;
                    }
                }
            }

            // 7. Handle Profile Image Upload
            if ($request->hasFile('profile_image')) {

                if ($agent->profile_image) {
                    Storage::disk('public')->delete(ltrim($agent->profile_image, '/'));
                }

                $path = $this->compressAgentImage(
                    $request->file('profile_image'),
                    'agents/profiles'
                );

                if ($path) {
                    $updateData['profile_image'] = $path;
                }
            }

            // 8. Handle Bio Image Upload
            if ($request->hasFile('bio_image')) {

                Log::info('Bio image upload started', ['agent_id' => $agent->id]);

                if ($agent->bio_image) {
                    Storage::disk('public')->delete(ltrim($agent->bio_image, '/'));
                }

                $path = $this->compressAgentImage(
                    $request->file('bio_image'),
                    'agents/bios'
                );

                if ($path) {
                    $updateData['bio_image'] = $path;
                }
            }

            // 9. Update the agent
            $agent->update($updateData);

            // 10. Commit transaction
            DB::commit();

            // 11. Reload agent with relationships
            $agent->load(['branch', 'area', 'subscription']);

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'agent' => $agent
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Agent Update Error: ' . $e->getMessage(), [
                'agent_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Server Error',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while updating profile'
            ], 500);
        }
    }


    /**
     * Get properties for a specific agent (Public Endpoint)
     * GET /v1/api/agents/{id}/properties
     */
    public function getAgentProperties($id)
    {
        $agent = Agent::find($id);

        if (!$agent) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Agent not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        // Fetch properties owned by this agent
        $properties = \App\Models\Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\\Models\\Agent')
            ->where('status', 'available') // Only show available/active properties
            ->latest()
            ->get()
            ->map(function ($prop) {
                // Ensure JSON fields are decoded properly for the API response
                $arrayFields = [
                    'name',
                    'description',
                    'images',
                    'availability',
                    'type',
                    'price',
                    'rooms',
                    'features',
                    'amenities',
                    'locations',
                    'address_details',
                    'floor_details',
                    'construction_details',
                    'energy_details',
                    'virtual_tour_details',
                    'additional_media',
                    'view_analytics',
                    'favorites_analytics',
                    'legal_information',
                    'investment_analysis',
                    'furnishing_details',
                    'seo_metadata',
                    'nearby_amenities'
                ];

                foreach ($arrayFields as $field) {
                    $prop->$field = is_string($prop->$field) ? json_decode($prop->$field) : $prop->$field;
                }

                return $prop;
            });

        return ApiResponse::success(
            ResponseDetails::successMessage('Agent properties retrieved successfully'),
            $properties, // Just return the list directly or wrapped in ['properties' => ...] based on your preference
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function getAgentsByCompany($companyId)
    {
        $agents = Agent::where('company_id', $companyId)->get();

        if ($agents->isEmpty()) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage("No agents found for company ID: $companyId"),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        return ApiResponse::success(
            ResponseDetails::successMessage("Agents for company retrieved successfully"),
            $agents,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function show($id)
    {
        $agent = Agent::find($id);
        if (!$agent) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Agent not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        return ApiResponse::success(
            ResponseDetails::successMessage('Agent retrieved successfully'),
            $agent,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_name'    => 'required|string|max:255',
            'primary_email' => 'required|email|unique:agents',
            'primary_phone' => 'required|string|max:20',
            'type'          => 'required|string',
            'city'          => 'required|string',
            'company_id'    => 'nullable|string|exists:real_estate_offices,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $data = $request->only([
            'agent_name',
            'primary_email',
            'primary_phone',
            'type',
            'city',
            'company_id',
        ]);

        $agent = Agent::create($data);

        // ── AUTO-SUBSCRIBE new agent to default 6-month plan ─────────────────────
        app(AutoSubscriptionService::class)->assignDefaultAgentSubscription($agent);

        return ApiResponse::success(
            ResponseDetails::successMessage('Agent created successfully'),
            $agent,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function updateAgentProfile(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);

        $request->validate([
            'agent_name' => 'required|string|max:255',
            'primary_email' => 'required|email|unique:agents,primary_email,' . $id,
            'primary_phone' => 'required|string|max:20',
            'type' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:6048',
        ]);

        $agent->update($request->only(['agent_name', 'primary_email', 'primary_phone', 'type', 'city']));

        if ($request->hasFile('profile_image')) {

            if ($agent->profile_image) {
                Storage::disk('public')->delete(ltrim($agent->profile_image, '/'));
            }

            $path = $this->compressAgentImage(
                $request->file('profile_image'),
                'agents/profiles'
            );

            if ($path) {
                $updateData['profile_image'] = $path;
            }
        }

        return redirect()->route('agent.edit', $agent->id)
            ->with('success', 'Agent updated successfully!');
    }


    public function destroy($id)
    {
        $agent = Agent::find($id);
        if (!$agent) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Agent not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $agent->delete();

        return ApiResponse::success(
            ResponseDetails::successMessage('Agent deleted successfully'),
            null,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function toggleVerification($id)
    {
        $agent = Agent::find($id);
        if (!$agent) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Agent not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $agent->is_verified = !$agent->is_verified;
        $agent->save();

        return ApiResponse::success(
            ResponseDetails::successMessage('Agent verification status updated'),
            $agent,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function removeFromCompany($id)
    {
        $agent = Agent::find($id);
        if (!$agent) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Agent not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $agent->company_id = null;
        $agent->save();

        return ApiResponse::success(
            ResponseDetails::successMessage('Agent removed from company'),
            $agent,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function createFromUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|uuid|exists:users,id',
            'priority'      => 'sometimes|in:user,request',
            'agent_name'    => 'sometimes|string|max:255',
            'agent_bio'     => 'nullable|string',
            'profile_image' => 'sometimes|url',
            'type'          => 'sometimes|string',
            'primary_email' => 'sometimes|email',
            'primary_phone' => 'required|string|max:20',
            'company_id'    => 'sometimes|uuid|exists:real_estate_offices,id',
            'company_name'  => 'sometimes|string|max:255',
            'transfer_data' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $user = User::findOrFail($request->user_id);

            // Check if agent already exists
            if (Agent::where('subscriber_id', $user->id)->exists()) {
                return back()->withErrors(['user_id' => 'An agent already exists for this user.']);
            }

            $priority     = $request->input('priority', 'user');
            $transferData = $request->input('transfer_data', true);

            $userMappedData = [
                'agent_name'    => $user->username,
                'agent_bio'     => $user->about_me ?? null,
                'profile_image' => $user->photo_image ?? null,
                'primary_email' => $user->email,
                'primary_phone' => $user->phone ?? null,
                'subscriber_id' => $user->id,
                'password'      => $user->password,
                'city'          => $user->place ?? null,
                'latitude'      => $user->lat ?? null,
                'longitude'     => $user->lng ?? null,
            ];

            $requestData = $request->except(['user_id', 'priority', 'transfer_data']);

            $agentData = $priority === 'user'
                ? array_merge($requestData, array_filter($userMappedData, fn($v) => !is_null($v)))
                : array_merge(array_filter($userMappedData, fn($v) => !is_null($v)), $requestData);

            $agentData['type']                              = $agentData['type'] ?? 'real_estate_official';
            $agentData['is_verified']                       = false;
            $agentData['overall_rating']                    = 0.00;
            $agentData['properties_uploaded_this_month']    = 0;
            $agentData['remaining_property_uploads']        = 0;
            $agentData['properties_sold']                   = 0;

            $agent = Agent::create($agentData);

            // Transfer user data if requested (existing logic — untouched)
            $transferResult = null;
            if ($transferData) {
                $transferResult = $this->transferUserDataToAgent($user, $agent);
            }

            // Delete user tokens and account
            $user->tokens()->delete();
            $userId = $user->id;
            $user->delete();

            DB::commit();

            // ── AUTO-SUBSCRIBE converted agent to default 6-month plan ───────────
            // Called after commit so the agent row definitely exists in DB
            app(AutoSubscriptionService::class)->assignDefaultAgentSubscription($agent);

            // Log in the new agent
            Auth::guard('agent')->login($agent);
            $request->session()->regenerate();

            Log::info('User converted to agent successfully', [
                'user_id'          => $userId,
                'agent_id'         => $agent->id,
                'data_transferred' => $transferData,
                'transfer_result'  => $transferResult,
            ]);

            if ($agent->company_id) {
                return redirect()->route('agent.profile', $agent->id)
                    ->with('success', 'Successfully converted to agent! Your data has been transferred.');
            }

            return redirect()->route('agent.dashboard')
                ->with('success', 'Successfully converted to agent! Your data has been transferred.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to convert user to agent', [
                'user_id' => $request->user_id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => 'Failed to convert to agent: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit($id)
    {
        $agent = Agent::findOrFail($id);
        return view('agent.edit-agent-admin', compact('agent'));
    }




    public function showCreateFromUserForm($user_id)
    {
        $user = User::findOrFail($user_id);

        // You can pass $user to a Blade view
        return view('agent.become', [
            'user' => $user
        ]);
    }


    // In App\Http\Controllers\AgentController.php

    public function getDashboardStats(Request $request)
    {
        try {
            // ✅ Get authenticated agent from Sanctum
            $agent = $request->user(); // This gets the authenticated model (Agent or User)

            // ✅ Debug logging
            Log::info('Dashboard Stats Request', [
                'authenticated_user' => $agent ? get_class($agent) : 'null',
                'user_id' => $agent ? $agent->id : 'null',
                'is_agent' => $agent instanceof \App\Models\Agent,
            ]);

            // ✅ Verify it's an Agent model
            if (!$agent || !($agent instanceof \App\Models\Agent)) {
                Log::error('Dashboard Stats: Not an agent', [
                    'user_type' => $agent ? get_class($agent) : 'null'
                ]);

                return response()->json([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized. Please login as an agent.',
                    'data' => null
                ], 401);
            }

            // ✅ Calculate stats
            $stats = [
                'total_properties' => \App\Models\Property::where('owner_id', $agent->id)
                    ->where('owner_type', 'App\\Models\\Agent')
                    ->count(),

                'active_listings' => \App\Models\Property::where('owner_id', $agent->id)
                    ->where('owner_type', 'App\\Models\\Agent')
                    ->where('status', 'available')
                    ->count(),

                'total_views' => \App\Models\Property::where('owner_id', $agent->id)
                    ->where('owner_type', 'App\\Models\\Agent')
                    ->sum('views') ?? 0,

                'properties_sold' => \App\Models\Property::where('owner_id', $agent->id)
                    ->where('owner_type', 'App\\Models\\Agent')
                    ->where('status', 'sold')
                    ->count(),

                'pending_appointments' => \App\Models\Appointment::where('agent_id', $agent->id)
                    ->where('status', 'pending')
                    ->count(),

                'total_revenue' => 0.0, // Calculate based on your business logic
                'revenue_growth' => 12.5, // Calculate based on your business logic
            ];

            // ✅ Get recent properties
            $recentProperties = \App\Models\Property::where('owner_id', $agent->id)
                ->where('owner_type', 'App\\Models\\Agent')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($property) {
                    // Ensure JSON fields are properly formatted
                    return [
                        'id' => $property->id,
                        'name' => is_string($property->name) ? json_decode($property->name, true) : $property->name,
                        'description' => is_string($property->description) ? json_decode($property->description, true) : $property->description,
                        'price' => is_string($property->price) ? json_decode($property->price, true) : $property->price,
                        'location' => $property->location ?? '',
                        'image' => $property->image ?? '',
                        'images' => is_string($property->images) ? json_decode($property->images, true) : ($property->images ?? []),
                        'type' => is_string($property->type) ? json_decode($property->type, true) : $property->type,
                        'status' => $property->status,
                        'created_at' => $property->created_at?->toISOString(),
                        'updated_at' => $property->updated_at?->toISOString(),
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Dashboard stats retrieved successfully',
                'data' => [
                    'stats' => $stats,
                    'recent_properties' => $recentProperties,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Dashboard Stats Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'Failed to retrieve dashboard stats',
                'data' => null
            ], 500);
        }
    }



    public function showOfficeProfile()
    {
        // Get logged-in agent ID from session
        $agentId = session('agent_id');

        $agent = Agent::find($agentId);

        if (!$agent) {
            abort(404, 'Agent not found');
        }

        if (!$agent->company_id) {
            return redirect()->route('agent.real-estate-office')
                ->with('error', 'You must register an office first.');
        }

        // Load the office
        $office = RealEstateOffice::find($agent->company_id);

        if (!$office) {
            abort(404, 'Office not found');
        }

        return view('agent.real-estate-office-profile', compact('office'));
    }

    private function transferUserDataToAgent(User $user, Agent $agent)
    {
        try {
            Log::info('Starting data transfer from user to agent', [
                'user_id' => $user->id,
                'agent_id' => $agent->id
            ]);

            // ✅ 1. Transfer Properties (Polymorphic Relationship)
            $propertiesTransferred = Property::where('owner_id', $user->id)
                ->where('owner_type', 'App\\Models\\User')
                ->update([
                    'owner_id' => $agent->id,
                    'owner_type' => 'App\\Models\\Agent'
                ]);

            Log::info('Properties transferred', ['count' => $propertiesTransferred]);

            // ✅ 2. Transfer Favorites
            $favoritesTransferred = 0;
            if (class_exists(\App\Models\Support\UserFavoriteProperty::class)) {
                $favoritesTransferred = \App\Models\Support\UserFavoriteProperty::where('user_id', $user->id)
                    ->update(['user_id' => $agent->id]);

                Log::info('Favorites transferred', ['count' => $favoritesTransferred]);
            }

            // ✅ 3. Transfer Appointments
            // Keep user_id but add agent_id so agent can manage these appointments
            $appointmentsTransferred = 0;
            if (class_exists(\App\Models\Appointment::class)) {
                // For appointments where user was the client, assign them to this new agent
                $appointmentsTransferred = \App\Models\Appointment::where('user_id', $user->id)
                    ->whereNull('agent_id') // Only appointments without assigned agent
                    ->update([
                        'agent_id' => $agent->id,
                    ]);

                Log::info('Appointments assigned to agent', ['count' => $appointmentsTransferred]);
            }

            // ✅ 4. Transfer Notifications
            // Convert user notifications to agent notifications
            $notificationsTransferred = 0;
            if (class_exists(\App\Models\Notification::class)) {
                $notificationsTransferred = \App\Models\Notification::where('user_id', $user->id)
                    ->whereNull('agent_id') // Only user-specific notifications
                    ->update([
                        'agent_id' => $agent->id,
                        'user_id' => null, // Clear user_id since they're now an agent
                    ]);

                Log::info('Notifications transferred to agent', ['count' => $notificationsTransferred]);
            }

            // ✅ 5. Transfer Legacy User Notification References (if exists)
            $legacyNotificationsCount = 0;
            if (class_exists(\App\Models\Support\UserNotificationReference::class)) {
                $userNotifications = \App\Models\Support\UserNotificationReference::where('user_id', $user->id)->get();

                foreach ($userNotifications as $userNotif) {
                    // Create new notification for agent
                    \App\Models\Notification::create([
                        'agent_id' => $agent->id,
                        'title' => $userNotif->title ?? 'Migrated Notification',
                        'message' => $userNotif->message ?? '',
                        'type' => $userNotif->type ?? 'info',
                        'is_read' => $userNotif->notification_status === 'read',
                        'read_at' => $userNotif->notification_status === 'read' ? $userNotif->notification_date : null,
                        'sent_at' => $userNotif->notification_date ?? now(),
                        'created_at' => $userNotif->notification_date ?? now(),
                        'updated_at' => now(),
                    ]);
                }

                // Delete old user notifications
                $legacyNotificationsCount = $userNotifications->count();
                \App\Models\Support\UserNotificationReference::where('user_id', $user->id)->delete();

                Log::info('Legacy user notifications migrated', ['count' => $legacyNotificationsCount]);
            }

            // ✅ 6. Transfer Sessions (Optional - delete old sessions)
            if (class_exists(\App\Models\Session::class)) {
                \App\Models\Session::where('user_id', $user->id)->delete();
                Log::info('User sessions cleared');
            }

            // ✅ 7. Summary Log
            Log::info('Data transfer completed successfully', [
                'agent_id' => $agent->id,
                'summary' => [
                    'properties' => $propertiesTransferred,
                    'favorites' => $favoritesTransferred,
                    'appointments' => $appointmentsTransferred,
                    'notifications' => $notificationsTransferred,
                    'legacy_notifications' => $legacyNotificationsCount,
                ]
            ]);

            return [
                'success' => true,
                'transferred' => [
                    'properties' => $propertiesTransferred,
                    'favorites' => $favoritesTransferred,
                    'appointments' => $appointmentsTransferred,
                    'notifications' => $notificationsTransferred + $legacyNotificationsCount,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error during data transfer', [
                'user_id' => $user->id,
                'agent_id' => $agent->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // Re-throw to trigger rollback
        }
    }
    public function showProfile($id)
    {
        $agent = Agent::with([
            'company',
            'specializations',
            'ownedProperties' => function ($query) {
                $query->where('is_active', 1)
                    ->where('published', 1)
                    ->orderBy('created_at', 'desc');
            },
            'clientReviews' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        return view('agent-profile', compact('agent'));
    }


    // In AgentController.php

    // In AgentController.php

    public function getAgentProfile(Request $request)
    {
        try {

            Log::info('STEP 1: Method started');

            $agent = $request->user();
            Log::info('STEP 2: User retrieved', ['agent_id' => $agent?->id]);

            if (!$agent || !($agent instanceof \App\Models\Agent)) {
                Log::warning('STEP 3: Unauthorized access');
                return response()->json([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized. Please login as an agent.',
                    'data' => null
                ], 401);
            }

            Log::info('STEP 4: Loading relationships');

            $agent->load([
                'company:id,name',
                'currentSubscription.currentPlan:id,name',
                'branch:id,name',
                'area:id,name',
            ]);

            Log::info('STEP 5: Relationships loaded successfully');

            // TEST working_hours specifically
            Log::info('STEP 6: Working hours raw value', [
                'working_hours' => $agent->working_hours
            ]);

            /*
        |--------------------------------------------------------------------------
        | SERIALIZE AGENT
        |--------------------------------------------------------------------------
        */

            Log::info('STEP 7: Building agent data array');

            $agentData = [
                'id' => $agent->id,
                'agent_name' => $agent->agent_name,
                'agent_bio' => $agent->agent_bio,
                'bio_image' => $agent->bio_image,
                'profile_image' => $agent->profile_image,
                'type' => $agent->type,
                'subscriber_id' => $agent->subscriber_id,
                'is_verified' => $agent->is_verified,
                'status' => $agent->status ?? 'active',
                'overall_rating' => $agent->overall_rating,
                'current_plan' => optional($agent->currentSubscription?->currentPlan)->name,
                'properties_uploaded_this_month' => $agent->properties_uploaded_this_month,
                'remaining_property_uploads' => $agent->remaining_property_uploads,
                'primary_email' => $agent->primary_email,
                'primary_phone' => $agent->primary_phone,
                'whatsapp_number' => $agent->whatsapp_number,
                'office_address' => $agent->office_address,
                'latitude' => $agent->latitude,
                'longitude' => $agent->longitude,
                'city' => $agent->city,
                'district' => $agent->district,
                'city_id' => $agent->city_id,
                'area_id' => $agent->area_id,
                'branch' => $agent->branch ? [
                    'id' => $agent->branch->id,
                    'name' => $agent->branch->name,
                ] : null,
                'area' => $agent->area ? [
                    'id' => $agent->area->id,
                    'name' => $agent->area->name,
                ] : null,
                'properties_sold' => $agent->properties_sold,
                'years_experience' => $agent->years_experience,
                'license_number' => $agent->license_number,
                'company' => $agent->company ? [
                    'id' => $agent->company->id,
                    'name' => $agent->company->name,
                ] : null,
                'employment_status' => $agent->employment_status,
                'agent_overview' => $agent->agent_overview,
                'working_hours' => $agent->working_hours,
                'commission_rate' => $agent->commission_rate,
                'consultation_fee' => $agent->consultation_fee,
                'currency' => $agent->currency,
            ];

            Log::info('STEP 8: Agent data array built successfully');

            /*
        |--------------------------------------------------------------------------
        | SUBSCRIPTION
        |--------------------------------------------------------------------------
        */

            $subscriptionData = null;

            if ($agent->currentSubscription) {

                Log::info('STEP 9: Building subscription');

                $subscription = $agent->currentSubscription;

                $subscriptionData = [
                    'id' => $subscription->id,
                    'plan_name' => optional($subscription->currentPlan)->name ?? 'Unknown Plan',
                    'status' => $subscription->status,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date,
                    'property_activation_limit' => $subscription->property_activation_limit,
                    'banner_activation_limit' => $subscription->banner_activation_limit,
                    'remaining_activations' => $subscription->remaining_activations,
                    'properties_activated_this_month' => $subscription->properties_activated_this_month,
                    'is_active' => $subscription->status === 'active'
                        && $subscription->end_date > now(),
                    'days_remaining' => $subscription->end_date
                        ? (int) now()->diffInDays($subscription->end_date, false)
                        : null,
                ];

                Log::info('STEP 10: Subscription built successfully');
            }

            Log::info('STEP 11: Returning JSON response');

            return response()->json([
                'status' => true,
                'message' => 'Agent profile retrieved successfully',
                'data' => [
                    'agent' => $agentData,
                    'subscription' => $subscriptionData,
                ]
            ], 200);
        } catch (\Throwable $e) {

            Log::error('❌ CRASH DETECTED', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'Failed to retrieve agent profile',
                'data' => null
            ], 500);
        }
    }

    public function updateAgentProfileNew(Request $request)
    {
        $request->validate([
            'agent_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:agents,email,' . auth()->guard('agent')->id(),
            'phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
        ]);

        auth()->guard('agent')->user()->update([
            'agent_name' => $request->agent_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'license_number' => $request->license_number,
            'bio' => $request->bio,
        ]);

        return redirect()->route('agent.profile.page')->with('success', 'Profile updated successfully');
    }
    // AgentController.php - showProfilePage method
    public function showProfilePage()
    {
        $agent = Auth::guard('agent')->user();

        if (!$agent) {
            return redirect()->route('login-page')->with('error', 'Please log in');
        }

        return view('agent.agent-profile', compact('agent'));
    }
    public function updateAgentPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, auth()->guard('agent')->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        auth()->guard('agent')->user()->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('agent.profile.page')->with('success', 'Password updated successfully');
    }


    // App/Http/Controllers/AgentController.php

    public function getMyProperties(Request $request)
    {
        // 1. Authenticate Agent
        $agent = Auth::guard('sanctum')->user();

        if (!$agent) {
            return ApiResponse::error('Unauthorized', [], 401);
        }

        // 2. Fetch Properties
        $properties = \App\Models\Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->latest()
            ->get()
            ->map(function ($prop) {
                // List of all fields that are cast as 'array' in your Property Model
                $arrayFields = [
                    'name',
                    'description',
                    'images',
                    'availability',
                    'type',
                    'price',
                    'rooms',
                    'features',
                    'amenities',
                    'locations',
                    'address_details',
                    'floor_details',
                    'construction_details',
                    'energy_details',
                    'virtual_tour_details',
                    'additional_media',
                    'view_analytics',
                    'favorites_analytics',
                    'legal_information',
                    'investment_analysis',
                    'furnishing_details',
                    'seo_metadata',
                    'nearby_amenities'
                ];

                // Loop through fields and ensure they are decoded
                // (Note: Since you have $casts in your model, Laravel usually handles this automatically,
                // but this loop guarantees safety if raw strings are ever returned)
                foreach ($arrayFields as $field) {
                    $prop->$field = is_string($prop->$field) ? json_decode($prop->$field) : $prop->$field;
                }

                return $prop;
            });

        return ApiResponse::success(
            ResponseDetails::successMessage('Agent properties retrieved successfully'),
            ['properties' => $properties],
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function getSubscriptionDetails(Request $request)
    {
        try {
            // 1. Get Authenticated Agent
            $agent = $request->user();

            // Ensure we have a valid agent
            if (!$agent || !($agent instanceof \App\Models\Agent)) {
                Log::error('API: Unauthorized access or not an agent');
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Please login as an agent.',
                    'data' => null
                ], 401);
            }

            Log::info('API: Fetching subscription details', [
                'agent_id' => $agent->id,
                'agent_name' => $agent->agent_name ?? 'N/A'
            ]);

            // 2. Fetch Current Active Subscription
            $currentSubscription = \App\Models\Subscription\Subscription::where('user_id', $agent->id)
                ->where('status', 'active')
                ->where('end_date', '>=', now())
                ->with('currentPlan') // Eager load the plan
                ->latest()
                ->first();

            // 3. ✅ NEW: Get Live Property Count directly here
            // This counts how many active properties the agent currently has
            $activePropertiesCount = \App\Models\Property::where('owner_id', $agent->id)
                ->where('owner_type', 'App\\Models\\Agent')
                ->count();

            Log::info('API: Subscription query result', [
                'found' => $currentSubscription ? 'YES' : 'NO',
                'subscription_id' => $currentSubscription?->id ?? 'N/A',
                'active_properties_count' => $activePropertiesCount
            ]);

            // 4. Fetch Available Plans (Rich Data)
            $plans = \App\Models\SubscriptionPlan::where('type', 'agent')
                ->where('active', 1)
                ->orderBy('sort_order', 'asc')
                ->get();

            Log::info('API: Plans fetched', ['count' => $plans->count()]);

            // 5. Format current subscription
            $formattedSubscription = null;

            if ($currentSubscription) {
                // Get the rich plan data if plan exists
                $richPlan = null;
                if ($currentSubscription->current_plan_id) {
                    $richPlan = \App\Models\SubscriptionPlan::find($currentSubscription->current_plan_id);
                }

                // Determine the limit (prioritize rich plan data, fallback to subscription record)
                $limit = $richPlan?->max_properties ?? $currentSubscription->property_activation_limit ?? 0;

                // Calculate remaining (prevent negative numbers)
                $remaining = max(0, $limit - $activePropertiesCount);

                $formattedSubscription = [
                    'id' => $currentSubscription->id,
                    'user_id' => $currentSubscription->user_id,
                    'plan_id' => $currentSubscription->current_plan_id,

                    // Use rich plan data if available
                    'plan_name' => $richPlan?->name ?? $currentSubscription->currentPlan?->name ?? 'Unknown Plan',

                    'status' => $currentSubscription->status,
                    'start_date' => $currentSubscription->start_date ?
                        $currentSubscription->start_date->toIso8601String() : null,
                    'end_date' => $currentSubscription->end_date ?
                        $currentSubscription->end_date->toIso8601String() : null,

                    'days_remaining' => $currentSubscription->end_date ?
                        now()->diffInDays($currentSubscription->end_date, false) : 0,

                    // ✅ UPDATED: Use the calculated limit and live usage
                    'property_activation_limit' => $limit,
                    'property_limit' => $limit,

                    // ✅ UPDATED: Send the actual count from the properties table
                    'properties_used' => $activePropertiesCount,
                    'remaining_activations' => $remaining,

                    'banner_activation_limit' => $currentSubscription->banner_activation_limit ?? 0,
                ];

                // Add rich plan object details
                if ($richPlan) {
                    $formattedSubscription['plan'] = [
                        'id' => $richPlan->id,
                        'name' => $richPlan->name,
                        'property_activation_limit' => $richPlan->max_properties ?? 0,
                        'banner_activation_limit' => 0,
                        'can_featured_listing' => false,
                        'can_priority_support' => false,
                        'price' => $richPlan->final_price_iqd ?? 0,
                        'duration' => $richPlan->duration_label ?? 'monthly',
                    ];
                } else {
                    $formattedSubscription['plan'] = [
                        'id' => null,
                        'name' => 'Unknown Plan',
                        'property_activation_limit' => 0,
                        'banner_activation_limit' => 0,
                        'can_featured_listing' => false,
                        'can_priority_support' => false,
                    ];
                }
            }

            // 6. Format plans with RICH data structure
            $formattedPlans = $plans->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name ?? 'Unnamed Plan',
                    'duration' => $plan->duration_label ?? 'monthly',

                    'price' => (float)($plan->final_price_iqd ?? 0),
                    'original_price' => (float)($plan->original_price_iqd ?? 0),
                    'discount' => (float)($plan->discount_iqd ?? 0),
                    'discount_percentage' => (float)($plan->discount_percentage ?? 0),

                    'property_activation_limit' => $plan->max_properties ?? 0,
                    'max_properties' => $plan->max_properties ?? 0,

                    'banner_activation_limit' => 0,
                    'can_featured_listing' => $plan->is_featured ?? false,
                    'can_priority_support' => false,
                    'active' => (bool)($plan->active ?? true),
                    'sort_order' => $plan->sort_order ?? 0,

                    'description' => $plan->description ?? '',
                    'features' => $plan->features ?? [],
                    'conditions' => $plan->conditions ?? [],
                    'note' => $plan->note ?? '',
                    'duration_months' => $plan->duration_months ?? 1,
                    'price_per_month' => (float)($plan->price_per_month_iqd ?? 0),
                ];
            })->toArray();

            $data = [
                'current_subscription' => $formattedSubscription,
                'available_plans' => $formattedPlans
            ];

            Log::info('API: Response prepared successfully', [
                'has_subscription' => $formattedSubscription !== null,
                'plans_count' => count($formattedPlans)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Subscription details retrieved successfully',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Subscription Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $errorResponse = [
                'status' => false,
                'message' => 'Failed to load subscriptions',
                'data' => null
            ];

            if (config('app.debug')) {
                $errorResponse['debug'] = [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            }

            return response()->json($errorResponse, 500);
        }
    }

    public function getAppointments(Request $request)
    {
        try {
            $agent = $request->user();

            // 1. Validation: Ensure user is an Agent
            if (!$agent || !($agent instanceof \App\Models\Agent)) {
                return ApiResponse::error('Unauthorized access', [], 401);
            }

            // 2. Build Query
            $query = Appointment::where('agent_id', $agent->id)
                ->with(['user', 'property']); // Load Client (User) and Property details

            // 3. Apply Filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // 4. Sort (Newest first)
            $appointments = $query->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->get();

            return ApiResponse::success(
                ResponseDetails::successMessage('Agent appointments retrieved'),
                $appointments,
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error('Agent Appointments Error: ' . $e->getMessage());
            return ApiResponse::error('Failed to load appointments', null, 500);
        }
    }

    /**
     * ✅ NEW: Update Appointment Status (Confirm/Cancel/Complete)
     */

    public function updateAppointmentStatus(Request $request, $id)
    {
        $agent = Auth::guard('agent')->user();

        $appointment = Appointment::where('id', $id)
            ->where('agent_id', $agent->id)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);

        $oldStatus = $appointment->status;
        $newStatus = $request->status;

        // Update appointment status using model methods
        switch ($newStatus) {
            case 'confirmed':
                $appointment->confirm();
                break;
            case 'completed':
                $appointment->complete();
                break;
            case 'cancelled':
                $appointment->cancel();
                break;
            default:
                $appointment->update(['status' => $newStatus]);
        }

        Log::info('Appointment status updated', [
            'appointment_id' => $appointment->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'agent_id' => $agent->id,
            'user_id' => $appointment->user_id
        ]);

        // Send notification to user
        $this->notifyUserAboutAppointmentStatusChange($appointment, $oldStatus, $newStatus);

        return redirect()->back()->with('success', 'Appointment status updated successfully!');
    }

    /**
     * Send multilingual notification to user about appointment status change
     */
    private function notifyUserAboutAppointmentStatusChange($appointment, $oldStatus, $newStatus)
    {
        Log::info('Starting appointment notification process', [
            'appointment_id' => $appointment->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);

        $user = $appointment->user;

        if (!$user) {
            Log::warning('Appointment has no user - notification cancelled', [
                'appointment_id' => $appointment->id
            ]);
            return;
        }

        // ✅ Get user's preferred language (default to English)
        $userLanguage = $user->language ?? 'en';

        Log::info('User language detected', [
            'user_id' => $user->id,
            'language' => $userLanguage
        ]);

        // ✅ Multilingual notification messages
        $statusMessages = [
            'confirmed' => [
                'en' => 'Your appointment has been confirmed! We look forward to seeing you.',
                'ar' => 'تم تأكيد موعدك! نتطلع لرؤيتك.',
                'ku' => 'چاوپێکەوتنەکەت پشتڕاست کرایەوە! چاوەڕوانی بینینت دەکەین.',
            ],
            'completed' => [
                'en' => 'Your appointment has been completed. Thank you for your time!',
                'ar' => 'تم إكمال موعدك. شكراً لوقتك!',
                'ku' => 'چاوپێکەوتنەکەت تەواو بوو. سوپاس بۆ کاتەکەت!',
            ],
            'cancelled' => [
                'en' => 'Your appointment has been cancelled. Please contact us if you have questions.',
                'ar' => 'تم إلغاء موعدك. يرجى الاتصال بنا إذا كان لديك أسئلة.',
                'ku' => 'چاوپێکەوتنەکەت هەڵوەشێنرایەوە. تکایە پەیوەندیمان پێوە بکە ئەگەر پرسیارت هەیە.',
            ],
            'pending' => [
                'en' => 'Your appointment is pending confirmation.',
                'ar' => 'موعدك في انتظار التأكيد.',
                'ku' => 'چاوپێکەوتنەکەت چاوەڕوانی پشتڕاستکردنەوەیە.',
            ],
        ];

        $titles = [
            'confirmed' => [
                'en' => 'Appointment Confirmed',
                'ar' => 'تم تأكيد الموعد',
                'ku' => 'چاوپێکەوتن پشتڕاست کرایەوە',
            ],
            'completed' => [
                'en' => 'Appointment Completed',
                'ar' => 'تم إكمال الموعد',
                'ku' => 'چاوپێکەوتن تەواو بوو',
            ],
            'cancelled' => [
                'en' => 'Appointment Cancelled',
                'ar' => 'تم إلغاء الموعد',
                'ku' => 'چاوپێکەوتن هەڵوەشێنرایەوە',
            ],
            'pending' => [
                'en' => 'Appointment Pending',
                'ar' => 'الموعد قيد الانتظار',
                'ku' => 'چاوپێکەوتن چاوەڕێیە',
            ],
        ];

        // ✅ Get message in user's language
        $title = $titles[$newStatus][$userLanguage] ?? $titles[$newStatus]['en'] ?? 'Appointment Update';
        $message = $statusMessages[$newStatus][$userLanguage] ?? $statusMessages[$newStatus]['en'] ?? 'Your appointment status has been updated.';

        Log::info('Notification messages prepared', [
            'language' => $userLanguage,
            'title' => $title,
            'message_preview' => substr($message, 0, 50) . '...'
        ]);

        // Get appointment details
        $propertyName = 'Property Viewing';
        if ($appointment->property) {
            $propertyName = is_array($appointment->property->name)
                ? ($appointment->property->name[$userLanguage] ?? $appointment->property->name['en'] ?? 'Property Viewing')
                : $appointment->property->name;
        }

        $appointmentDate = $appointment->appointment_date->format('M d, Y');

        try {
            $appointmentTime = $appointment->appointment_time instanceof \Carbon\Carbon
                ? $appointment->appointment_time->format('h:i A')
                : \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A');
        } catch (\Exception $e) {
            $appointmentTime = $appointment->appointment_time ?? 'N/A';
            Log::warning('Failed to parse appointment time', [
                'appointment_id' => $appointment->id,
                'raw_time' => $appointment->appointment_time,
                'error' => $e->getMessage()
            ]);
        }

        // Prepare notification data
        $notificationData = [
            'appointment_id' => $appointment->id,
            'property_name' => $propertyName,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'agent_name' => $appointment->agent->agent_name ?? 'Agent',
            'language' => $userLanguage,
        ];

        Log::info('Notification data prepared', $notificationData);

        try {
            $fcmService = app(FCMNotificationService::class);

            Log::info('Calling FCM service to send notification', [
                'user_id' => $user->id,
                'appointment_id' => $appointment->id
            ]);

            $result = $fcmService->createAndSendNotification(
                $user,
                $title,
                $message,
                'appointment_status',
                $notificationData
            );

            if ($result['success']) {
                $fcmSent = $result['fcm_result']['success'] ?? false;
                $sentCount = $result['fcm_result']['sent_count'] ?? 0;
                $totalTokens = $result['fcm_result']['total_tokens'] ?? 0;

                Log::info('✅ Appointment notification sent successfully', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'appointment_id' => $appointment->id,
                    'status' => $newStatus,
                    'language' => $userLanguage,
                    'database_notification_created' => true,
                    'fcm_notification_sent' => $fcmSent,
                    'fcm_tokens_sent_to' => $sentCount,
                    'fcm_total_tokens' => $totalTokens,
                    'notification_id' => $result['notification']->id ?? null
                ]);
            } else {
                Log::warning('⚠️ Notification created in database but FCM failed', [
                    'user_id' => $user->id,
                    'appointment_id' => $appointment->id,
                    'fcm_error' => $result['fcm_result']['error'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('❌ Failed to send appointment notification', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'appointment_id' => $appointment->id,
                'status' => $newStatus,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function updateFCMToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token'     => 'required|string',
            'old_fcm_token' => 'nullable|string',
            'device_name'   => 'nullable|string',
            'language'      => 'nullable|in:en,ar,ku',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $user = $request->user();

        if (!$user) {
            return ApiResponse::error(
                ResponseDetails::unauthorizedMessage(),
                null,
                ResponseDetails::CODE_UNAUTHORIZED
            );
        }

        // Remove old token if provided (token rotation)
        if ($request->old_fcm_token) {
            $user->removeFCMToken($request->old_fcm_token);
        }

        // Add new token
        $user->addFCMToken($request->fcm_token, $request->device_name ?? 'Unknown Device');

        // Update language if provided
        if ($request->language) {
            $user->update(['language' => $request->language]);
        }

        Log::info('FCM token updated', [
            'user_id' => $user->id,
            'role'    => $user instanceof \App\Models\Agent ? 'agent' : 'user',
        ]);

        return ApiResponse::success(
            ResponseDetails::successMessage('FCM token updated successfully'),
            null,
            ResponseDetails::CODE_SUCCESS
        );
    }


    public function updateProfile(Request $request)
    {
        $agent = Auth::guard('agent')->user();

        Log::info('Agent updateProfile request received', [
            'all_request' => $request->all(),
        ]);

        $request->validate([
            'agent_name'       => 'required|string|max:255',
            'primary_phone'    => 'required|string|max:20',
            'whatsapp_number'  => 'nullable|string|max:20',
            'city'             => 'required|string',
            'district'         => 'nullable|string',
            'license_number'   => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
            'agent_bio'        => 'nullable|string|max:1000',
            'office_address'   => 'nullable|string',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'profile_image'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'bio_image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'working_hours'    => 'nullable|string',
            'language'         => 'nullable|string|in:en,ar,ku',
        ]);

        try {

            // =========================
            // PROFILE IMAGE
            // =========================
            if ($request->hasFile('profile_image')) {

                Log::info('Agent profile image upload detected');

                $path = $this->compressAgentImage(
                    $request->file('profile_image'),
                    'agents/profiles'
                );

                if ($path) {
                    $agent->profile_image = $this->storageUrl($path);
                }
            }

            // =========================
            // BIO IMAGE
            // =========================
            if ($request->hasFile('bio_image')) {

                Log::info('Agent bio image upload detected');

                $path = $this->compressAgentImage(
                    $request->file('bio_image'),
                    'agents/bio'
                );

                if ($path) {
                    $agent->bio_image = $this->storageUrl($path);
                }
            }

            // =========================
            // FIELDS
            // =========================
            $agent->agent_name       = $request->agent_name;
            $agent->primary_phone    = $request->primary_phone;
            $agent->whatsapp_number  = $request->whatsapp_number;
            $agent->city             = $request->city;
            $agent->district         = $request->district;
            $agent->license_number   = $request->license_number;
            $agent->years_experience = $request->years_experience;
            $agent->agent_bio        = $request->agent_bio;
            $agent->office_address   = $request->office_address;
            $agent->latitude         = $request->latitude;
            $agent->longitude        = $request->longitude;

            if ($request->filled('language')) {
                $agent->language = $request->language;
            }

            // =========================
            // WORKING HOURS
            // =========================
            if ($request->filled('working_hours')) {
                $decoded = json_decode($request->working_hours, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $agent->working_hours = $decoded;
                }
            }

            $agent->save();

            return response()->json([
                'status'  => true,
                'message' => 'Profile updated successfully',
                'data'    => [
                    'agent' => $agent->fresh()
                ]
            ]);
        } catch (\Exception $e) {

            Log::error('Agent Update Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateLanguage(Request $request, $id)
    {
        $request->validate([
            'language' => 'required|string|in:en,ar,ku',
        ]);

        $agent = Agent::findOrFail($id);

        $agent->language = $request->language;
        $agent->save();

        return response()->json([
            'status' => true,
            'message' => 'Language updated successfully',
            'data' => $agent
        ]);
    }

    private function compressAgentImage($file, string $folder): ?string
    {
        try {
            $mime = $file->getMimeType();
            $sourcePath = $file->getRealPath();

            $image = match ($mime) {
                'image/jpeg', 'image/jpg' => imagecreatefromjpeg($sourcePath),
                'image/png'               => imagecreatefrompng($sourcePath),
                'image/webp'              => imagecreatefromwebp($sourcePath),
                default                   => null,
            };

            if (!$image) {
                Log::error('Unsupported image type', ['mime' => $mime]);
                return null;
            }

            $width  = imagesx($image);
            $height = imagesy($image);

            $max = 1280;

            $ratio = min($max / $width, $max / $height);
            $newW = $ratio < 1 ? (int)($width * $ratio) : $width;
            $newH = $ratio < 1 ? (int)($height * $ratio) : $height;

            $newImage = imagecreatetruecolor($newW, $newH);

            if ($mime === 'image/png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }

            imagecopyresampled(
                $newImage,
                $image,
                0,
                0,
                0,
                0,
                $newW,
                $newH,
                $width,
                $height
            );

            $relativePath = $folder . '/agent_' . uniqid() . '.jpg';
            $fullPath = storage_path('app/public/' . $relativePath);

            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            imagejpeg($newImage, $fullPath, 75);

            imagedestroy($image);
            imagedestroy($newImage);

            Log::info('Agent image compressed', [
                'path' => $relativePath
            ]);

            return $relativePath;
        } catch (\Throwable $e) {
            Log::error('Agent image compression failed', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }
    private function storageUrl(string $path): string
    {
        return rtrim(config('app.url'), '/') . '/storage/' . ltrim($path, '/');
    }
}
