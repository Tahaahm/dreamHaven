<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Agent;
use Illuminate\Http\Request;
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
}