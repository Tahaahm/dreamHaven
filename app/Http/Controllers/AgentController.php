<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;

class AgentController extends Controller
{
   // Get a list of all agents
   public function index()
   {
       $agents = Agent::all();
       return response()->json($agents);
   }

   // Create a new agent
   public function store(Request $request)
   {
       $validatedData = $request->validate([
           'agent_name' => 'required|string|max:255',
           'email' => 'required|email|unique:agents',
           'phone' => 'required|string|max:20',
           'office_id' => 'required|exists:real_estate_offices,office_id',
           'profile_photo' => 'nullable|url',
           'is_verified' => 'boolean',
       ]);

       $agent = Agent::create($validatedData);
       return response()->json(['message' => 'Agent created successfully', 'agent' => $agent], 201);
   }

   // Show details of a specific agent
   public function show($id)
   {
       $agent = Agent::find($id);
       if (!$agent) {
           return response()->json(['message' => 'Agent not found'], 404);
       }
       return response()->json($agent);
   }

   // Update agent information
   public function update(Request $request, $id)
   {
       $agent = Agent::find($id);
       if (!$agent) {
           return response()->json(['message' => 'Agent not found'], 404);
       }

       $validatedData = $request->validate([
           'agent_name' => 'string|max:255',
           'email' => 'email|unique:agents,email,'.$agent->agent_id.',agent_id', // Ignore unique check for current agent
           'phone' => 'string|max:20',
           'office_id' => 'exists:real_estate_offices,office_id',
           'profile_photo' => 'nullable|url',
           'is_verified' => 'boolean',
       ]);

       $agent->update($validatedData);
       return response()->json(['message' => 'Agent updated successfully', 'agent' => $agent]);
   }

   // Delete an agent
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
