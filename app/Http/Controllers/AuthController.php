<?php

namespace App\Http\Controllers;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Validator;
>>>>>>> myproject/main

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
<<<<<<< HEAD
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

=======

class AuthController extends Controller
{
    // User creation method
    public function userCreate(Request $request)
    {
        try {
            // Log the input data for debugging
            Log::info('Input data for user creation:', $request->all());

            // Trim phone input to remove whitespace
            $request->merge(['phone' => trim($request->input('phone'))]);

            // Validate the request data
            $request->validate([
                'name' => 'required|unique:users,name|max:255|regex:/^[a-zA-Z0-9_ ]+$/',
                'password' => 'required|min:6|confirmed',
                'phone' => 'required|digits_between:10,15|unique:users,phone',
                'email' => 'required|email|unique:users,email|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
                'role' => 'nullable|in:user,agent,real_estate_office',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:20',
                'bio' => 'nullable|string',
                'website' => 'nullable|url|max:255',
                'facebook' => 'nullable|url|max:255',
                'twitter' => 'nullable|url|max:255',
                'instagram' => 'nullable|url|max:255',
            ]);

            // Set default values
            $data['is_verified'] = false; // Make sure the user is not verified at creation
            $data['active'] = true;

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = uniqid('user_image_') . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('user_images'), $filename);
                $data['image'] = 'user_images/' . $filename;
            }

            // Hash the password
            $data['password'] = Hash::make($request->input('password'));

            // Create the user
            $user = User::create(array_merge($data, [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
            ]));

            // Log in the user after registration
            Auth::login($user);

            // Redirect to /newindex with success message
            return redirect('/newindex')->with('success', 'User created successfully!');
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            // Log validation errors
            Log::error('Validation error creating user: ' . json_encode($validationException->errors()));
            return redirect('/login-page')->withInput()->withErrors($validationException->errors())->with('toggleRegisterSection', true);
        } catch (\Illuminate\Database\QueryException $queryException) {
            // Log database query errors
            Log::error('Database error creating user: ' . $queryException->getMessage());
            $errorMessage = 'Error creating user. Please check your input and try again.';
            return redirect('/login-page')->withInput()->withErrors(['error' => $errorMessage])->with('toggleRegisterSection', true);
        } catch (\Exception $e) {
            // Log any other unexpected exceptions
            Log::error('Unexpected error creating user: ' . $e->getMessage());
            $errorMessage = 'An unexpected error occurred. Please try again later.';
            return redirect('/login-page')->withInput()->withErrors(['error' => $errorMessage])->with('toggleRegisterSection', true);
        }
    }

    // Edit user method
    public function updateUser(Request $request, $user_id)
{
    // Fetch the user using user_id
    $user = User::where('user_id', $user_id)->firstOrFail();

    // Validate input data (excluding password initially)
    $request->validate([
        'name' => 'required|max:255|regex:/^[a-zA-Z0-9_ ]+$/|unique:users,name,' . $user->user_id . ',user_id',
        'email' => 'required|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
        'phone' => 'required|numeric|digits_between:10,15|unique:users,phone,' . $user->user_id . ',user_id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
    ]);

    // Update password only if provided
    if ($request->filled('password')) {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);
        $user->password = Hash::make($request->input('password'));
    }

    // Handle image upload and remove old image if applicable
    if ($request->hasFile('image')) {
        // Delete old image if it exists
        if ($user->image && file_exists(public_path($user->image))) {
            unlink(public_path($user->image));
        }

        // Save new image
        $image = $request->file('image');
        $filename = uniqid('user_image_') . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('user_images'), $filename);
        $user->image = 'user_images/' . $filename;
    }

    // Update other user details
    $user->name = $request->input('name');
    $user->email = $request->input('email');
    $user->phone = $request->input('phone');

    // Save updated user data
    $user->save();

    // Redirect back with a success message
    return redirect()->back()->with('success', 'User profile updated successfully!');
}


    

     // Show the profile page
     public function showProfile()
     {
         $user = Auth::user();
         return view('agent.profile', compact('user'));
     }
 
     // Show the admin property list
     public function adminPropertyList()
     {
         return view('agent.admin-property-list');
     }
 
     // Show the admin dashboard
     public function adminDashboard()
     {
         return view('agent.admin-dashboard');
     }
 


     public function login(Request $request)
     {
         try {
             $validator = Validator::make($request->all(), [
                 'email' => 'required|email',
                 'password' => 'required',
             ]);
 
             if ($validator->fails()) {
                 return redirect('/login-page')
                     ->withErrors(['error' => 'Invalid email address or password.'])
                     ->withInput()
                     ->with('active_form', 'login-section');
             }
 
             $credentials = $request->only('email', 'password');
 
             if (Auth::attempt($credentials)) {
                 $request->session()->regenerate();
                 Log::info('Authentication successful for email: ' . $request->input('email'));
                 return redirect('/newindex');
             }
 
             Log::info('Authentication failed for email: ' . $request->input('email'));
             return redirect('/login-page')
                 ->withErrors(['error' => 'Invalid email address or password.'])
                 ->withInput()
                 ->with('active_form', 'login-section');
         } catch (\Exception $e) {
             Log::error('Error during login: ' . $e->getMessage());
             return redirect('/login-page')
                 ->withErrors(['error' => 'An unexpected error occurred. Please try again later.'])
                 ->with('active_form', 'login-section');
         }
     }
 

     // Logout method
     public function logout(Request $request)
     {
         try {
             Auth::logout();
             $request->session()->invalidate();
             $request->session()->regenerateToken();
             return redirect('/');
         } catch (\Exception $e) {
             Log::error('Error during logout: ' . $e->getMessage());
             return redirect('/')->withErrors(['error' => 'An unexpected error occurred. Please try again later.']);
         }
     }
 
     // Show the reviews page
     public function showReviews()
     {
         return view('agent.reviews');
     }
 
     // Edit user method
     public function editUser($id)
     {
         $user = User::findOrFail($id);
         return view('agent.edit-agent-admin', compact('user'));
     }
 
>>>>>>> myproject/main
}
