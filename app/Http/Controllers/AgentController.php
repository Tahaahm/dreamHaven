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
            $user = \App\Models\User::findOrFail($request->user_id);

            if (Agent::where('subscriber_id', $user->id)->exists()) {
                return back()->withErrors(['user_id' => 'An agent already exists for this user.']);
            }

            $priority = $request->input('priority', 'user');
            $transferData = $request->input('transfer_data', false);

            // Map user data to agent
            $userMappedData = [
                'agent_name' => $user->username,
                'agent_bio' => $user->about_me ?? null,
                'profile_image' => $user->photo_image ?? null,
                'primary_email' => $user->email,
                'primary_phone' => $user->phone ?? null,
                'subscriber_id' => $user->id,
                'password' => $user->password, // preserve hashed password
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

            $agent = Agent::create($agentData);

            if ($transferData) {
                $this->transferUserDataToAgent($user, $agent);
            }

            $user->tokens()->delete();
            $userId = $user->id;
            $user->delete();

            DB::commit();

            // ✅ Log in the new agent immediately
            Auth::guard('agent')->login($agent);
            $request->session()->regenerate();

            Log::info('User converted to agent and logged in', [
                'user_id' => $userId,
                'agent_id' => $agent->id
            ]);

            // Redirect to profile page
            return redirect()->route('agent.office.profile', $agent->company_id)
                ->with('success', 'Your account has been converted to an agent and you are now logged in!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to convert user to agent', [
                'user_id' => $request->user_id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to become an agent: ' . $e->getMessage()])->withInput();
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

            // ✅ 1. Transfer Properties
            $propertiesTransferred = Property::where('owner_id', $user->id)
                ->where('owner_type', 'App\\Models\\User')
                ->update([
                    'owner_id' => $agent->id,
                    'owner_type' => 'App\\Models\\Agent'
                ]);

            Log::info('Properties transferred', ['count' => $propertiesTransferred]);

            // ✅ 2. Transfer Favorites
            if (class_exists(\App\Models\Support\UserFavoriteProperty::class)) {
                $favoritesTransferred = \App\Models\Support\UserFavoriteProperty::where('user_id', $user->id)
                    ->update(['user_id' => $agent->id]);

                Log::info('Favorites transferred', ['count' => $favoritesTransferred]);
            }

            // ✅ 3. Transfer Appointments - Convert user appointments to agent appointments
            if (class_exists(\App\Models\Appointment::class)) {
                $appointmentsTransferred = \App\Models\Appointment::where('user_id', $user->id)
                    ->update([
                        'agent_id' => $agent->id,
                        'user_id' => null // Clear user_id since they're now an agent
                    ]);

                Log::info('Appointments transferred', ['count' => $appointmentsTransferred]);
            }

            // ✅ 4. Transfer User Notifications to Agent Notifications
            if (class_exists(\App\Models\Support\UserNotificationReference::class)) {
                $userNotifications = \App\Models\Support\UserNotificationReference::where('user_id', $user->id)->get();

                foreach ($userNotifications as $userNotif) {
                    \App\Models\Support\AgentNotification::create([
                        'agent_id' => $agent->id,
                        'notification_id' => $userNotif->notification_id,
                        'title' => $userNotif->title,
                        'message' => $userNotif->message,
                        'type' => $userNotif->type,
                        'is_read' => $userNotif->notification_status === 'read',
                        'read_at' => $userNotif->notification_status === 'read' ? $userNotif->notification_date : null,
                        'created_at' => $userNotif->notification_date,
                        'updated_at' => now(),
                    ]);
                }

                // Delete old user notifications
                $userNotifications->each->delete();

                Log::info('User notifications converted to agent notifications', ['count' => $userNotifications->count()]);
            }

            Log::info('Data transfer completed successfully', [
                'agent_id' => $agent->id,
                'properties' => $propertiesTransferred,
                'appointments' => $appointmentsTransferred ?? 0,
                'favorites' => $favoritesTransferred ?? 0,
                'notifications' => $userNotifications->count() ?? 0
            ]);
        } catch (\Exception $e) {
            Log::error('Error during data transfer', [
                'user_id' => $user->id,
                'agent_id' => $agent->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
}