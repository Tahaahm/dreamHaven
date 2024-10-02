<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
   /**
     * User registration (sign-up).
     * Handles the creation of a new user.
     */
    public function register(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:user,agent,real_estate_office',
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Encrypt the password
            'role' => $request->role, // Either 'user', 'agent', or 'real_estate_office'
        ]);

        // Generate a token for the user
        $token = $user->createToken('authToken')->plainTextToken;

        // Return success response with token
        return response()->json(['message' => 'User registered successfully', 'token' => $token], 201);
    }

    /**
     * User login.
     * Handles user authentication.
     */
    public function login(Request $request)
    {
        // Validate the incoming request
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Attempt to log the user in
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Generate a token for the user
        $token = $user->createToken('authToken')->plainTextToken;

        // Return success response with token
        return response()->json(['message' => 'Login successful', 'token' => $token], 200);
    }

    /**
     * Logout the user.
     * Revoke all user tokens.
     */
    public function logout(Request $request)
    {
        // Revoke all tokens for the authenticated user
        $request->user()->tokens()->delete();

        // Return success response
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
