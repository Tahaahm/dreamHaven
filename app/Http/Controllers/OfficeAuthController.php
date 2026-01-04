<?php

namespace App\Http\Controllers;

use App\Models\RealEstateOffice;
use App\Models\Property;
use App\Models\Agent;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class OfficeAuthController extends Controller
{
    /**
     * Show the office login form
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to dashboard
        if (Auth::guard('office')->check()) {
            return redirect()->route('office.dashboard');
        }

        return view('office.login');
    }

    /**
     * Handle office login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $credentials = [
            'email_address' => $request->email,
            'password' => $request->password,
        ];

        $remember = $request->filled('remember');

        if (Auth::guard('office')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('office.dashboard'))
                ->with('success', 'Welcome back, ' . Auth::guard('office')->user()->company_name . '!');
        }

        return redirect()->back()
            ->withErrors(['email' => 'Invalid credentials'])
            ->withInput($request->only('email'));
    }

    /**
     * Show office registration form
     */
    public function showRegisterForm()
    {
        return view('office.register');
    }

    /**
     * Handle office registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|unique:real_estate_offices,email_address',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'office_address' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $office = RealEstateOffice::create([
            'company_name' => $request->company_name,
            'email_address' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'city' => $request->city,
            'district' => $request->district,
            'office_address' => $request->office_address,
            'years_experience' => $request->years_experience ?? 0,
            'account_type' => 'real_estate_official',
            'is_verified' => false,
        ]);

        // Auto-login after registration
        Auth::guard('office')->login($office);

        return redirect()->route('office.dashboard')
            ->with('success', 'Registration successful! Welcome to Dream Haven!');
    }

    /**
     * Handle office logout
     */
    public function logout(Request $request)
    {
        Auth::guard('office')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('office.login')
            ->with('success', 'Logged out successfully!');
    }

    /**
     * Show office dashboard
     */
    public function showDashboard()
    {
        $office = Auth::guard('office')->user();

        // Get statistics
        $totalAgents = Agent::where('company_id', $office->id)->count();

        $agentIds = Agent::where('company_id', $office->id)->pluck('id');

        $totalProperties = Property::where('owner_type', 'App\Models\Agent')
            ->whereIn('owner_id', $agentIds)
            ->count();

        $activeListings = Property::where('owner_type', 'App\Models\Agent')
            ->whereIn('owner_id', $agentIds)
            ->where('status', 'available')
            ->count();

        $soldProperties = Property::where('owner_type', 'App\Models\Agent')
            ->whereIn('owner_id', $agentIds)
            ->where('status', 'sold')
            ->count();

        $totalAppointments = Appointment::where('office_id', $office->id)->count();
        $pendingAppointments = Appointment::where('office_id', $office->id)
            ->where('status', 'pending')
            ->count();

        // Recent properties
        $recentProperties = Property::where('owner_type', 'App\Models\Agent')
            ->whereIn('owner_id', $agentIds)
            ->latest()
            ->take(6)
            ->get();

        // Recent appointments
        $recentAppointments = Appointment::where('office_id', $office->id)
            ->with(['user', 'agent', 'property'])
            ->latest()
            ->take(5)
            ->get();

        // Top agents
        $topAgents = Agent::where('company_id', $office->id)
            ->withCount('properties')
            ->orderBy('properties_count', 'desc')
            ->take(5)
            ->get();

        $stats = [
            'total_agents' => $totalAgents,
            'total_properties' => $totalProperties,
            'active_listings' => $activeListings,
            'sold_properties' => $soldProperties,
            'total_appointments' => $totalAppointments,
            'pending_appointments' => $pendingAppointments,
        ];

        return view('office.dashboard', compact(
            'office',
            'stats',
            'recentProperties',
            'recentAppointments',
            'topAgents'
        ));
    }

    /**
     * Show office profile page
     */
    public function showProfilePage()
    {
        $office = Auth::guard('office')->user();

        if (!$office) {
            return redirect()->route('office.login')->with('error', 'Please log in');
        }

        return view('office.profile-page', compact('office'));
    }

    /**
     * Update office profile
     */
    public function updateProfile(Request $request)
    {
        $office = Auth::guard('office')->user();

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'email_address' => 'required|email|unique:real_estate_offices,email_address,' . $office->id,
            'phone_number' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'office_address' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
            'properties_sold' => 'nullable|integer|min:0',
            'company_bio' => 'nullable|string',
            'about_company' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($office->profile_image) {
                Storage::disk('public')->delete($office->profile_image);
            }

            $imagePath = $request->file('profile_image')->store('office-profiles', 'public');
            $office->profile_image = $imagePath;
        }

        // Update office information
        $office->update([
            'company_name' => $request->company_name,
            'email_address' => $request->email_address,
            'phone_number' => $request->phone_number,
            'city' => $request->city,
            'district' => $request->district,
            'office_address' => $request->office_address,
            'years_experience' => $request->years_experience ?? 0,
            'properties_sold' => $request->properties_sold ?? 0,
            'company_bio' => $request->company_bio,
            'about_company' => $request->about_company,
        ]);

        return redirect()->route('office.profile.page')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update office password
     */
    public function updatePassword(Request $request)
    {
        $office = Auth::guard('office')->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        // Check if current password is correct
        if (!Hash::check($request->current_password, $office->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect']);
        }

        // Update password
        $office->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('office.profile.page')
            ->with('success', 'Password updated successfully!');
    }
}
