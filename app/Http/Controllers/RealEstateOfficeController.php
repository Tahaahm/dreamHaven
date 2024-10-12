<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\RealEstateOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RealEstateOfficeController extends Controller
{
    /**
     * Display a listing of the real estate offices.
     */
    public function index()
    {
        $offices = RealEstateOffice::all();
        return response()->json($offices);
    }

    /**
     * Store a newly created real estate office in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'office_name' => 'required|string|max:255',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:real_estate_offices',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'profile_photo' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $office = RealEstateOffice::create($request->all());

        return response()->json(['message' => 'Real Estate Office created successfully', 'office' => $office], 201);
    }

    /**
     * Display the specified real estate office.
     */
    public function show($id)
    {
        $office = RealEstateOffice::find($id);
        if (!$office) {
            return response()->json(['message' => 'Office not found'], 404);
        }
        return response()->json($office);
    }

    /**
     * Update the specified real estate office in storage.
     */
    public function update(Request $request, $id)
{
    // Find the agent by the agent_id
    $agent = Agent::find($id);
    if (!$agent) {
        return response()->json(['message' => 'Agent not found'], 404);
    }

    // Validate the request, excluding the current agent's email from the uniqueness check
    $validator = Validator::make($request->all(), [
        'agent_name' => 'string|max:255',
        'email' => 'email|unique:agents,email,' . $agent->agent_id . ',agent_id', // Update here
        'phone' => 'string|max:20',
        'office_id' => 'nullable|exists:real_estate_offices,office_id',
        'profile_photo' => 'nullable|url',
        'is_verified' => 'boolean'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    // Update the agent's data
    $agent->update($request->all());

    return response()->json(['message' => 'Agent updated successfully', 'agent' => $agent]);
}



    /**
     * Remove the specified real estate office from storage.
     */
    public function destroy($id)
    {
        $office = RealEstateOffice::find($id);
        if (!$office) {
            return response()->json(['message' => 'Office not found'], 404);
        }

        $office->delete();

        return response()->json(['message' => 'Real Estate Office deleted successfully']);
    }
}
