<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $agent = Agent::create($request->all());

        return ApiResponse::success(
            ResponseDetails::successMessage('Agent created successfully'),
            $agent,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function update(Request $request, $id)
    {
        $agent = Agent::find($id);
        if (!$agent) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Agent not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $validator = Validator::make($request->all(), [
            'agent_name' => 'string|max:255',
            'primary_email' => 'email|unique:agents,primary_email,' . $id,
            'primary_phone' => 'string|max:20',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $agent->update($request->all());

        return ApiResponse::success(
            ResponseDetails::successMessage('Agent updated successfully'),
            $agent,
            ResponseDetails::CODE_SUCCESS
        );
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

    /**
     * Create an agent from an existing user
     */
    /**
     * Create an agent from an existing user and delete the user
     */
    public function createFromUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,id',
            'priority' => 'sometimes|in:user,request', // Which data takes priority

            // Optional agent-specific fields that can override user data
            'agent_name' => 'sometimes|string|max:255',
            'agent_bio' => 'sometimes|string',
            'profile_image' => 'sometimes|url',
            'type' => 'sometimes|string',
            'primary_email' => 'sometimes|email',
            'primary_phone' => 'sometimes|string|max:20',
            'whatsapp_number' => 'sometimes|string|max:20',
            'office_address' => 'sometimes|string',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'city' => 'sometimes|string|max:100',
            'district' => 'sometimes|string|max:100',
            'years_experience' => 'sometimes|integer|min:0',
            'license_number' => 'sometimes|string|max:100',
            'company_id' => 'sometimes|uuid|exists:real_estate_offices,id',
            'company_name' => 'sometimes|string|max:255',
            'employment_status' => 'sometimes|in:employee,independent,partner',
            'agent_overview' => 'sometimes|string',
            'working_hours' => 'sometimes|array',
            'commission_rate' => 'sometimes|numeric|min:0|max:100',
            'consultation_fee' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'current_plan' => 'sometimes|in:starter,professional,enterprise',
            'transfer_data' => 'sometimes|boolean', // Whether to transfer user's related data to agent
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        // Start database transaction
        DB::beginTransaction();

        try {
            // Fetch the user
            $user = \App\Models\User::findOrFail($request->user_id);

            // Check if agent already exists for this user
            $existingAgent = Agent::where('subscriber_id', $user->id)->first();
            if ($existingAgent) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    ['user_id' => 'An agent already exists for this user'],
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            // Determine priority (default: user data takes priority)
            $priority = $request->input('priority', 'user');
            $transferData = $request->input('transfer_data', false);

            // Map user data to agent fields
            $userMappedData = [
                'agent_name' => $user->username,
                'agent_bio' => $user->about_me,
                'profile_image' => $user->photo_image,
                'primary_email' => $user->email,
                'primary_phone' => $user->phone,
                'office_address' => $user->place,
                'latitude' => $user->lat,
                'longitude' => $user->lng,
                'subscriber_id' => $user->id,
            ];

            // Get request data (excluding user_id, priority, and transfer_data)
            $requestData = $request->except(['user_id', 'priority', 'transfer_data']);

            // Merge data based on priority
            if ($priority === 'user') {
                // User data takes priority, request data fills in gaps
                $agentData = array_merge($requestData, array_filter($userMappedData, function ($value) {
                    return !is_null($value);
                }));
            } else {
                // Request data takes priority, user data fills in gaps
                $agentData = array_merge(array_filter($userMappedData, function ($value) {
                    return !is_null($value);
                }), $requestData);
            }

            // Set defaults for required fields if not provided
            $agentData['type'] = $agentData['type'] ?? 'real_estate_official';
            $agentData['is_verified'] = false;
            $agentData['overall_rating'] = 0.00;
            $agentData['properties_uploaded_this_month'] = 0;
            $agentData['remaining_property_uploads'] = 0;
            $agentData['properties_sold'] = 0;
            $agentData['years_experience'] = $agentData['years_experience'] ?? 0;
            $agentData['consultation_fee'] = $agentData['consultation_fee'] ?? 0.00;
            $agentData['currency'] = $agentData['currency'] ?? 'USD';

            // Create the agent
            $agent = Agent::create($agentData);

            // Transfer or handle user's related data if requested
            if ($transferData) {
                $this->transferUserDataToAgent($user, $agent);
            }

            // Revoke all user's tokens
            $user->tokens()->delete();

            // Delete the user
            $userId = $user->id;
            $userEmail = $user->email;
            $user->delete();

            // Commit transaction
            DB::commit();

            Log::info('User converted to agent and deleted', [
                'user_id' => $userId,
                'user_email' => $userEmail,
                'agent_id' => $agent->id,
                'priority_used' => $priority,
                'data_transferred' => $transferData
            ]);

            return ApiResponse::success(
                ResponseDetails::successMessage('Agent created successfully and user account deleted'),
                [
                    'agent' => $agent->fresh(),
                    'deleted_user_id' => $userId,
                    'priority_used' => $priority,
                    'data_transferred' => $transferData
                ],
                ResponseDetails::CODE_SUCCESS
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('User not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to convert user to agent', [
                'user_id' => $request->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::error(
                ResponseDetails::errorMessage('Failed to create agent from user'),
                ['error' => $e->getMessage()],
                ResponseDetails::CODE_SERVER_ERROR
            );
        }
    }

    /**
     * Transfer user's related data to the newly created agent
     */
    private function transferUserDataToAgent($user, $agent)
    {
        try {
            // Transfer appointments (if user has appointments)
            if (method_exists($user, 'appointments')) {
                DB::table('appointments')
                    ->where('user_id', $user->id)
                    ->update(['agent_id' => $agent->id]);
            }

            // Transfer owned properties to agent (polymorphic relationship)
            if ($user->ownedProperties()->exists()) {
                $user->ownedProperties()->update([
                    'owner_type' => Agent::class,
                    'owner_id' => $agent->id
                ]);
            }

            // Note: Favorite properties and notifications will be deleted with cascade
            // You might want to handle these differently based on your business logic

            Log::info('User data transferred to agent', [
                'user_id' => $user->id,
                'agent_id' => $agent->id
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to transfer some user data to agent', [
                'user_id' => $user->id,
                'agent_id' => $agent->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw exception - we want agent creation to succeed
            // even if data transfer partially fails
        }
    }
}
