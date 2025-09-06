<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Agent;
use App\Models\RealEstateOffice;


class AuthController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created user in storage.
     */
   public function store(Request $request)
{
    Log::info('User Validation');

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'role' => 'required|in:user,agent,admin',  // Changed this
        'office_id' => 'nullable|exists:real_estate_offices,office_id'
    ]);

    if ($validator->fails()) {
        Log::error('Validation failed: ', $validator->errors()->toArray());
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 400);
    }

    try {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'office_id' => $request->office_id,
            'is_verified' => false
        ]);

        Log::info('User Created: ', $user->toArray());

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);

    } catch (\Exception $e) {
        Log::error('User creation failed: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'User creation failed',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $validator = Validator::make($request->all(), [
        'name' => 'string|max:255',
        'email' => 'string|email|max:255|unique:users,email,' . $user->user_id . ',user_id',  // Explicitly mention the primary key
        'password' => 'nullable|string|min:8',
        'role' => 'in:user,agent,real_estate_office',
        'office_id' => 'nullable|exists:real_estate_offices,office_id'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    // Update user attributes except password
    $user->update($request->only('name', 'email', 'role', 'office_id', 'is_verified'));

    // Handle password update separately
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
        $user->save();
    }

    return response()->json(['message' => 'User updated successfully', 'user' => $user]);
}



    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }


    //
    public function loginRealEstateOffice(Request $request)
    {
        $credentials = $request->only('admin_email', 'password');

        // Find the RealEstateOffice by email
        $office = RealEstateOffice::where('admin_email', $request->admin_email)->first();

        if ($office && Hash::check($request->password, $office->password)) {
            // Generate token (using Sanctum)
            $token = $office->createToken('authToken')->plainTextToken;

            return response()->json([
                'token' => $token,
                'office' => $office
            ]);
        } else {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }


    // Login for Agents
    public function loginAgent(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Find the agent by email
        $agent = Agent::where('email', $request->email)->first();

        if ($agent && Hash::check($request->password, $agent->password)) {
            $token = $agent->createToken('authToken')->plainTextToken;

            return response()->json([
                'token' => $token,
                'agent' => $agent
            ]);
        } else {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }

    // Login for regular Users

}
