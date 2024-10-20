<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
   // Get a list of all agents
    /**
     * Display a listing of agents.
     */
    public function index()
    {
        $agents = Agent::all();
        return ApiResponse::success(
            ResponseDetails::successMessage('Agents retrieved successfully'),
            $agents,
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Store a newly created agent in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_name' => 'required|string|max:255',
            'email' => 'required|email|unique:agents',
            'phone' => 'required|string|max:20',
            'office_id' => 'nullable|exists:real_estate_offices,office_id',
            'profile_photo' => 'nullable|url',
            'is_verified' => 'boolean'
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
    /**
     * Display the specified agent.
     */
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


    /**
     * Update the specified agent in storage.
     */
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
            'email' => 'email|unique:agents,email,' . $agent->agent_id . ',agent_id',
            'phone' => 'string|max:20',
            'office_id' => 'nullable|exists:real_estate_offices,office_id',
            'profile_photo' => 'nullable|url',
            'is_verified' => 'boolean'
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

    /**
     * Remove the specified agent from storage.
     */
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
}
