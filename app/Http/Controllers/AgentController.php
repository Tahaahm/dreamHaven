<?php

namespace App\Http\Controllers;

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
        return response()->json($agents);
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
            return response()->json($validator->errors(), 400);
        }

        $agent = Agent::create($request->all());

        return response()->json(['message' => 'Agent created successfully', 'agent' => $agent], 201);
    }

    /**
     * Display the specified agent.
     */
    public function show($id)
    {
        $agent = Agent::find($id);
        if (!$agent) {
            return response()->json(['message' => 'Agent not found'], 404);
        }
        return response()->json($agent);
    }

    /**
     * Update the specified agent in storage.
     */
    public function update(Request $request, $id)
{
    $agent = Agent::find($id);
    if (!$agent) {
        return response()->json(['message' => 'Agent not found'], 404);
    }

    $validator = Validator::make($request->all(), [
        'agent_name' => 'string|max:255',
        'email' => 'email|unique:agents,email,' . $agent->agent_id . ',agent_id', // Changed here
        'phone' => 'string|max:20',
        'office_id' => 'nullable|exists:real_estate_offices,office_id',
        'profile_photo' => 'nullable|url',
        'is_verified' => 'boolean'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $agent->update($request->all());

    return response()->json(['message' => 'Agent updated successfully', 'agent' => $agent]);
}


    /**
     * Remove the specified agent from storage.
     */
    public function destroy($id)
    {
        $agent = Agent::find($id);
        if (!$agent) {
            return response()->json(['message' => 'Agent not found'], 404);
        }

        $agent->delete();

        return response()->json(['message' => 'Agent deleted successfully']);
    }
}
