<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Agent;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\RealEstateOffice;
use App\Models\Support\UserFavoriteProperty;
use Illuminate\Support\Facades\Hash;
use App\Models\Subscription\Subscription;

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
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string', // Flutter should send this
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $email = $request->email;
        $password = $request->password;
        $deviceName = $request->device_name ?? 'Unknown Device';

        // 1️⃣ Try logging in as a normal User
        $user = User::where('email', $email)->first();
        if ($user && Hash::check($password, $user->password)) {
            // Generate Sanctum Token
            $token = $user->createToken($deviceName)->plainTextToken;

            return ApiResponse::success(
                ResponseDetails::successMessage('Logged in successfully as user'),
                [
                    'token' => $token,
                    'user' => $user,
                    'role' => 'user'
                ],
                ResponseDetails::CODE_SUCCESS
            );
        }

        // 2️⃣ Try logging in as an Agent
        $agent = Agent::where('primary_email', $email)->first();
        if ($agent && Hash::check($password, $agent->password)) {
            // Generate Sanctum Token
            $token = $agent->createToken($deviceName)->plainTextToken;

            return ApiResponse::success(
                ResponseDetails::successMessage('Logged in successfully as agent'),
                [
                    'token' => $token,
                    'agent' => $agent, // Flutter expects this key based on your logs
                    'role' => 'agent'
                ],
                ResponseDetails::CODE_SUCCESS
            );
        }

        // 3️⃣ Invalid Credentials
        return ApiResponse::error(
            'Invalid credentials',
            ['email' => ['These credentials do not match our records.']],
            401 // Unauthorized
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
                // Delete old image if exists
                if ($agent->profile_image && file_exists(public_path($agent->profile_image))) {
                    @unlink(public_path($agent->profile_image));
                }

                // Create directory if not exists
                $profileDir = public_path('uploads/agents/profiles');
                if (!file_exists($profileDir)) {
                    mkdir($profileDir, 0755, true);
                }

                // Store new image
                $profileImage = $request->file('profile_image');
                $profileImageName = 'agent_profile_' . $agent->id . '_' . time() . '.' . $profileImage->extension();
                $profileImage->move($profileDir, $profileImageName);

                $updateData['profile_image'] = '/uploads/agents/profiles/' . $profileImageName;
            }

            // 8. Handle Bio Image Upload
            if ($request->hasFile('bio_image')) {
                // Delete old image if exists
                if ($agent->bio_image && file_exists(public_path($agent->bio_image))) {
                    @unlink(public_path($agent->bio_image));
                }

                // Create directory if not exists
                $bioDir = public_path('uploads/agents/bios');
                if (!file_exists($bioDir)) {
                    mkdir($bioDir, 0755, true);
                }

                // Store new image
                $bioImage = $request->file('bio_image');
                $bioImageName = 'agent_bio_' . $agent->id . '_' . time() . '.' . $bioImage->extension();
                $bioImage->move($bioDir, $bioImageName);

                $updateData['bio_image'] = '/uploads/agents/bios/' . $bioImageName;
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
            'agent_name' => 'required|string|max:255',
            'primary_email' => 'required|email|unique:agents',
            'primary_phone' => 'required|string|max:20',
            'type' => 'required|string',
            'city' => 'required|string',
            'company_id' => 'nullable|string|exists:real_estate_offices,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        // Ensure company_id is included in fillable data
        $data = $request->only([
            'agent_name',
            'primary_email',
            'primary_phone',
            'type',
            'city',
            'company_id'
        ]);

        $agent = Agent::create($data);

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
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $agent->profile_image = $path;
            $agent->save();
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
            'user_id' => 'required|uuid|exists:users,id',
            'priority' => 'sometimes|in:user,request',
            'agent_name' => 'sometimes|string|max:255',
            'agent_bio' => 'nullable|string',
            'profile_image' => 'sometimes|url',
            'type' => 'sometimes|string',
            'primary_email' => 'sometimes|email',
            'primary_phone' => 'required|string|max:20',
            'company_id' => 'sometimes|uuid|exists:real_estate_offices,id',
            'company_name' => 'sometimes|string|max:255',
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

            $priority = $request->input('priority', 'user');
            $transferData = $request->input('transfer_data', true); // Default to true

            // Map user data to agent
            $userMappedData = [
                'agent_name' => $user->username,
                'agent_bio' => $user->about_me ?? null,
                'profile_image' => $user->photo_image ?? null,
                'primary_email' => $user->email,
                'primary_phone' => $user->phone ?? null,
                'subscriber_id' => $user->id,
                'password' => $user->password, // Preserve hashed password
                'city' => $user->place ?? null,
                'latitude' => $user->lat ?? null,
                'longitude' => $user->lng ?? null,
            ];

            $requestData = $request->except(['user_id', 'priority', 'transfer_data']);

            $agentData = $priority === 'user'
                ? array_merge($requestData, array_filter($userMappedData, fn($v) => !is_null($v)))
                : array_merge(array_filter($userMappedData, fn($v) => !is_null($v)), $requestData);

            // Set defaults
            $agentData['type'] = $agentData['type'] ?? 'real_estate_official';
            $agentData['is_verified'] = false;
            $agentData['overall_rating'] = 0.00;
            $agentData['properties_uploaded_this_month'] = 0;
            $agentData['remaining_property_uploads'] = 0;
            $agentData['properties_sold'] = 0;

            // Create the agent
            $agent = Agent::create($agentData);

            // Transfer data if requested
            $transferResult = null;
            if ($transferData) {
                $transferResult = $this->transferUserDataToAgent($user, $agent);
            }

            // Delete user tokens and account
            $user->tokens()->delete();
            $userId = $user->id;
            $user->delete();

            DB::commit();

            // Log in the new agent
            Auth::guard('agent')->login($agent);
            $request->session()->regenerate();

            Log::info('User converted to agent successfully', [
                'user_id' => $userId,
                'agent_id' => $agent->id,
                'data_transferred' => $transferData,
                'transfer_result' => $transferResult
            ]);

            // Determine redirect
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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
            \Log::info('Dashboard Stats Request', [
                'authenticated_user' => $agent ? get_class($agent) : 'null',
                'user_id' => $agent ? $agent->id : 'null',
                'is_agent' => $agent instanceof \App\Models\Agent,
            ]);

            // ✅ Verify it's an Agent model
            if (!$agent || !($agent instanceof \App\Models\Agent)) {
                \Log::error('Dashboard Stats: Not an agent', [
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
            \Log::error('Dashboard Stats Error', [
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
            // Get authenticated agent
            $agent = $request->user();

            // Verify it's an Agent model
            if (!$agent || !($agent instanceof \App\Models\Agent)) {
                return response()->json([
                    'status' => false,
                    'code' => 401,
                    'message' => 'Unauthorized. Please login as an agent.',
                    'data' => null
                ], 401);
            }

            // Load relationships including subscription
            $agent->load([
                'company',
                'currentSubscription.currentPlan', // ✅ Load subscription with plan
            ]);

            // Format subscription data
            $subscriptionData = null;
            if ($agent->currentSubscription) {
                $subscriptionData = [
                    'id' => $agent->currentSubscription->id,
                    'plan_name' => $agent->currentSubscription->currentPlan->name ?? 'Unknown Plan',
                    'status' => $agent->currentSubscription->status,
                    'start_date' => $agent->currentSubscription->start_date,
                    'end_date' => $agent->currentSubscription->end_date,
                    'property_activation_limit' => $agent->currentSubscription->property_activation_limit,
                    'banner_activation_limit' => $agent->currentSubscription->banner_activation_limit,
                    'is_active' => $agent->currentSubscription->status === 'active',
                    'days_remaining' => $agent->currentSubscription->end_date
                        ? now()->diffInDays($agent->currentSubscription->end_date, false)
                        : null,
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Agent profile retrieved successfully',
                'data' => [
                    'agent' => $agent,
                    'subscription' => $subscriptionData,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get Agent Profile Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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

        return view('agent.agent-profile-page', compact('agent'));
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
}
