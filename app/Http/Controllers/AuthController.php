<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Agent;
use App\Models\RealEstateOffice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Http\Controllers\URL;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use App\Models\AdminAnalytics;

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
    $validator = Validator::make($request->all(), [
        'username' => 'required|string|max:255|unique:users',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'role' => 'required|in:user,agent,admin',
        'phone' => 'nullable|string|max:20',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Generate a consistent numeric 6-digit code
    $code = str_pad(strval(mt_rand(0, 999999)), 6, '0', STR_PAD_LEFT);

    // Create user properly
    $user = User::create([
        'username' => $request->username,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'phone' => $request->phone,
        'verification_code' => $code,
        'is_verified' => false,
    ]);

    Log::info("Verification code for {$user->email}: {$code}");

    return redirect()->route('verify.email', ['id' => $user->id])
        ->with('success', 'Thanks for signing up! Enter the verification code sent to your email.');
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
        'username' => 'string|max:255',
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











    // zana's function ------------------------------------------------------------------------------------
 // Login for regular users
   

    // Logout function (optional)
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully');
    }



    // Show the reviews page
public function showReviews()
{
    $agentId = session('agent_id');

    if (!$agentId) {
        abort(403, 'Unauthorized — only agents can access this page.');
    }

    $agent = \App\Models\Agent::find($agentId);

    return view('agent.reviews', compact('agent'));
}


 
     // Edit user method
     public function editUser($id)
     {
         $user = User::findOrFail($id);
         return view('agent.edit-agent-admin', compact('user'));
     }




    public function adminDashboard()
    {
        // Fetch the latest analytics record
        $analytics = AdminAnalytics::latest()->first();

        // You can also calculate totals dynamically if needed:
        $totalUsers = \App\Models\User::count();
        $totalAgents = \App\Models\Agent::count();
        $totalOffices = \App\Models\RealEstateOffice::count();
        $totalProperties = \App\Models\Property::count();
        $activeBanners = \App\Models\BannerAd::where('is_active', true)->count();

        return view('agent.admin-dashboard', [
            'analytics' => $analytics,
            'totals' => [
                'users' => $totalUsers,
                'agents' => $totalAgents,
                'offices' => $totalOffices,
                'properties' => $totalProperties,
                'activeBanners' => $activeBanners
            ]
        ]);
    }


       // Show the profile page
    
     // Show the profile page
public function showProfile()
{
    $user = Auth::user();
    $agent = Auth::guard('agent')->user();

    return view('agent.profile', compact('user', 'agent'));
}


 
     // Show the admin property list
     public function adminPropertyList()
     {
         return view('agent.admin-property-list');
     }
 
     // Show the admin dashboard

 
  // Show all users + agents
// Show all users and agents
  // Show all users and agents
public function usersList(Request $request)
{
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }

    // Fetch users and agents
    $users = User::select('id', 'username as name', 'email', 'role', 'is_suspended')
        ->get()
        ->map(function ($user) {
            $user->type = 'User';
            return $user;
        });

    $agents = Agent::select('id', 'agent_name as name', 'primary_email as email', 'type', 'status')
        ->get()
        ->map(function ($agent) {
            $agent->role = $agent->type ?? null;
            $agent->is_suspended = strtolower($agent->status ?? '') !== 'active';
            $agent->type = 'Agent';
            return $agent;
        });

    // Merge users and agents
    $allEntities = $users->merge($agents)->sortByDesc('id')->values();

    // Filter by type if requested
    $filter = $request->query('filter'); // 'User', 'Agent', or null
    if ($filter === 'User') {
        $allEntities = $allEntities->where('type', 'User')->values();
    } elseif ($filter === 'Agent') {
        $allEntities = $allEntities->where('type', 'Agent')->values();
    }

    // Manual pagination: 5 per page
    $perPage = 5;
    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    $currentPageItems = $allEntities->slice(($currentPage - 1) * $perPage, $perPage)->values();

    $paginatedEntities = new LengthAwarePaginator(
        $currentPageItems,
        $allEntities->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    return view('Admin.users-list', ['entities' => $paginatedEntities, 'filter' => $filter]);
}




public function updateProfile(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return back()->with('error', 'User not found.');
    }

    // Validation
    $validator = Validator::make($request->all(), [
        'username' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'phone' => 'nullable|string|max:20',
        'password' => 'nullable|string|min:8|confirmed',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:6048',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Handle profile image upload (if any)
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('profile_images', 'public');
        $user->image = 'storage/' . $imagePath;
    }

    // Update fields
    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone = $request->phone;

    if (!empty($request->password)) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return back()->with('success', 'Profile updated successfully.');
}

public function showLoginForm()
{
    return view('login-page'); // points to resources/views/login-page.blade.php
}


public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $email = $request->email;
    $password = $request->password;

    // 1️⃣ Try logging in as a normal User
    $user = User::where('email', $email)->first();
    if ($user && Hash::check($password, $user->password)) {
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('newindex')
            ->with('success', 'Logged in successfully as user!');
    }

    // 2️⃣ Try logging in as an Agent using agent guard
    $agent = Agent::where('primary_email', $email)->first();
    if ($agent && Hash::check($password, $agent->password)) {

        Auth::guard('agent')->login($agent);
        $request->session()->regenerate();

        return redirect()->route('newindex')
            ->with('success', 'Logged in successfully as agent!');
    }

    // 3️⃣ Invalid login
    return redirect()->route('login-page')
        ->withInput(['email' => $email])
        ->with('error', 'Invalid credentials for both user and agent');
}


public function sendEmailVerificationNotification()
{
    // Generate the URL manually
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $this->id, 'hash' => sha1($this->email)]
    );

    Log::info("Email Verification Link: " . $verificationUrl);

    // (Optional) If you want to disable real emails completely:
    // parent::sendEmailVerificationNotification();
}




public function showVerifyEmail(Request $request)
{
    $user = Auth::user(); // logged-in user

    return view('auth.verify-email', compact('user'));
}





public function verifyCode(Request $request)
{
    $request->validate([
        'code' => 'required|digits:6',
    ]);

    // Detect logged-in account
    $user = Auth::user(); // web guard
    $agent = Auth::guard('agent')->user(); // agent guard

    $account = $user ?? $agent;

    if (!$account) {
        return redirect()->route('login-page')
            ->with('error', 'You must be logged in to verify your account.');
    }

    // Check code
    if ($account->verification_code !== $request->code) {
        return back()->withErrors(['code' => 'Invalid verification code.']);
    }

    // Mark as verified
    $account->is_verified = true;
    $account->verification_code = null; // optional: clear code
    $account->save();

    return redirect()->route('newindex')->with('success', 'Your account has been verified!');
}

public function resendCode(Request $request)
{
    // Determine logged-in account
    $user = Auth::user(); // web guard
    $agent = Auth::guard('agent')->user(); // agent guard

    $account = $user ?? $agent;

    if (!$account) {
        return redirect()->route('login-page')
            ->with('error', 'You must be logged in to resend code.');
    }

    // Generate new code
    $account->verification_code = str_pad(strval(mt_rand(0, 999999)), 6, '0', STR_PAD_LEFT);
    $account->save();

    // Log or send email
    $email = $account instanceof User ? $account->email : $account->primary_email;
    Log::info("New verification code for {$email}: {$account->verification_code}");

    return back()->with('success', 'A new verification code has been sent.');
}




}



