<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\RealEstateOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Agent;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RealEstateOfficeController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('auth:agent');
    // }


    /**
     * Display a listing of the real estate offices.
     */
    public function index()
    {
        $offices = RealEstateOffice::all();
        return ApiResponse::success(
            ResponseDetails::successMessage('Offices retrieved successfully'),
            $offices,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function store(Request $request)
    {
        // This is safe because the route uses 'auth:agent'
        $agent = Auth::guard('agent')->user();

        if (!$agent) {
            return redirect()->route('agent.login')->with('error', 'Unauthorized access');
        }

        // Now create the office
        $office = RealEstateOffice::create([
            'company_name' => $request->company_name,
            'city' => $request->city,
            'district' => $request->district,

        ]);

        // Set agent's company_id
        $agent->company_id = $office->id;
        $agent->save();

        // Redirect to the profile page
        return redirect()->route('agent.office.profile', ['id' => $office->id])
            ->with('success', 'Office created successfully!');
    }


    /**
     * Display the specified real estate office.
     */
    public function show($id)
    {
        // First, try without relationships to see if basic fetch works
        $office = RealEstateOffice::find($id);

        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Office not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        return ApiResponse::success(
            ResponseDetails::successMessage('Office retrieved successfully'),
            $office,
            ResponseDetails::CODE_SUCCESS
        );
    }

    public function update(Request $request, $id)
    {
        $office = RealEstateOffice::find($id);
        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Office not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $validator = Validator::make($request->all(), [
            'company_name' => 'sometimes|string|max:255',
            'email_address' => 'sometimes|email|unique:real_estate_offices,email_address,' . $id,
            'phone_number' => 'sometimes|string|max:20',
            'office_address' => 'nullable|string',
            'profile_image' => 'nullable|string',
            'company_bio' => 'nullable|string',
            'about_company' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'years_experience' => 'nullable|integer|min:0',
            'properties_sold' => 'nullable|integer|min:0',
            'availability_schedule' => 'nullable|array',
            'current_plan' => 'nullable|in:starter,professional,enterprise',
            'is_verified' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        // Check if verification status changed
        $wasVerified = $office->is_verified;
        $updateData = $request->only([
            'company_name',
            'email_address',
            'phone_number',
            'office_address',
            'profile_image',
            'company_bio',
            'about_company',
            'city',
            'district',
            'latitude',
            'longitude',
            'years_experience',
            'properties_sold',
            'availability_schedule',
            'current_plan',
            'is_verified'
        ]);

        $office->update($updateData);

        // Send verification notification if office got verified
        if (!$wasVerified && $office->is_verified) {
            app(NotificationController::class)->sendOfficeVerificationNotification($office->id);
        }

        return ApiResponse::success(
            ResponseDetails::successMessage('Office updated successfully'),
            $office->fresh(),
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Remove the specified real estate office from storage.
     */
    public function destroy($id)
    {
        $office = RealEstateOffice::find($id);
        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Office not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $office->delete();

        return ApiResponse::success(
            ResponseDetails::successMessage('Real Estate Office deleted successfully'),
            null,
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Login method - Note: This requires password field in migration
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_address' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $office = RealEstateOffice::where('email_address', $request->email_address)->first();
        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Office not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        // Note: This requires password field in migration and HasApiTokens trait in model
        /*
        if (!Hash::check($request->password, $office->password)) {
            return ApiResponse::error(
                ResponseDetails::unauthorizedMessage('Invalid credentials'),
                null,
                ResponseDetails::CODE_UNAUTHORIZED
            );
        }

        $token = $office->createToken('authToken')->plainTextToken;

        return ApiResponse::success(
            ResponseDetails::successMessage('Login successful'),
            ['token' => $token, 'office' => $office],
            ResponseDetails::CODE_SUCCESS
        );
        */

        return ApiResponse::error(
            ResponseDetails::errorMessage('Login functionality requires password field in migration and HasApiTokens trait'),
            null,
            ResponseDetails::CODE_SERVER_ERROR
        );
    }

    /**
     * Fetch properties for a specific office
     */
    public function fetchProperties($id)
    {
        Log::info("Fetching properties for office ID: $id");

        $office = RealEstateOffice::with(['propertyListings.property', 'ownedProperties'])->find($id);

        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Office not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        // Get both listed properties and owned properties
        $listedProperties = $office->propertyListings->pluck('property');
        $ownedProperties = $office->ownedProperties;

        $allProperties = $listedProperties->merge($ownedProperties)->unique('id');

        return ApiResponse::success(
            ResponseDetails::successMessage('Properties retrieved successfully'),
            $allProperties,
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Get available time slots for a specific office and date
     */




    /**
     * zana's code ----------------------------------------------------------------------------------------------------------------------------------------------------
     */












    public function showRealEstateOfficePage()
    {
        $user = auth()->user();

        // Only admin or agent
        if (!in_array($user->role, ['admin', 'agent'])) {
            abort(403, 'Unauthorized');
        }

        return view('agent.real-estate-office');
    }

    // Your existing store function stays as is






    /**
     * Display the real estate office dashboard
     */
    public function dashboard($id)
    {
        $office = RealEstateOffice::with(['agents' => function ($query) {
            $query->take(6); // Get first 6 agents for initial display
        }])->findOrFail($id);

        // Get all agents count
        $totalAgents = Agent::where('company_id', $id)->count();

        // Get properties through agents
        $agentIds = Agent::where('company_id', $id)->pluck('id');

        $properties = Property::where('owner_type', 'App\Models\Agent')
            ->whereIn('owner_id', $agentIds)
            ->with('owner')
            ->latest()
            ->take(8)
            ->get();

        $totalProperties = Property::where('owner_type', 'App\Models\Agent')
            ->whereIn('owner_id', $agentIds)
            ->count();

        // Calculate statistics
        $stats = [
            'total_agents' => $totalAgents,
            'total_properties' => $totalProperties,
            'active_listings' => Property::where('owner_type', 'App\Models\Agent')
                ->whereIn('owner_id', $agentIds)
                ->where('status', 'available')
                ->count(),
            'sold_properties' => Property::where('owner_type', 'App\Models\Agent')
                ->whereIn('owner_id', $agentIds)
                ->where('status', 'sold')
                ->count(),
        ];

        return view('office.dashboard', compact('office', 'properties', 'stats', 'totalAgents', 'totalProperties'));
    }

    public function profile($id)
    {
        // Load office with agents only (the relationship that actually exists)
        $office = RealEstateOffice::with(['agents'])->findOrFail($id);

        // Get all agent IDs from this office
        $agentIds = $office->agents->pluck('id')->toArray();

        // Get properties owned by these agents
        $properties = collect();

        if (!empty($agentIds)) {
            $properties = Property::where('owner_type', 'App\Models\Agent')
                ->whereIn('owner_id', $agentIds)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('agent.real-estate-office-profile', compact('office', 'properties'));
    }

    /**
     * Helper function to load property owner
     */
    private function loadPropertyOwner($property)
    {
        if ($property->owner_type === 'App\Models\Agent') {
            return Agent::find($property->owner_id);
        } elseif ($property->owner_type === 'App\Models\User') {
            return \App\Models\User::find($property->owner_id);
        } elseif ($property->owner_type === 'App\Models\RealEstateOffice') {
            return RealEstateOffice::find($property->owner_id);
        }
        return null;
    }

    /**
     * Load more agents via AJAX
     */
    public function loadMoreAgents(Request $request, $id)
    {
        $offset = $request->get('offset', 0);

        $agents = Agent::where('company_id', $id)
            ->skip($offset)
            ->take(6)
            ->get();

        return response()->json([
            'success' => true,
            'agents' => $agents,
            'hasMore' => Agent::where('company_id', $id)->count() > ($offset + 6)
        ]);
    }

    /**
     * Load more properties via AJAX
     */
    public function loadMoreProperties(Request $request, $id)
    {
        $offset = $request->get('offset', 0);

        $agentIds = Agent::where('company_id', $id)->pluck('id')->toArray();

        $properties = Property::where('owner_type', 'App\Models\Agent')
            ->whereIn('owner_id', $agentIds)
            ->with(['owner' => function ($query) {
                // Eager load owner
            }])
            ->latest()
            ->skip($offset)
            ->take(9)
            ->get()
            ->map(function ($property) {
                // Ensure all data is properly formatted for JSON
                return [
                    'id' => $property->id,
                    'name' => $property->name,
                    'description' => $property->description,
                    'images' => $property->images ?? [],
                    'price' => $property->price,
                    'listing_type' => $property->listing_type,
                    'rental_period' => $property->rental_period,
                    'rooms' => $property->rooms,
                    'area' => $property->area,
                    'address_details' => $property->address_details,
                    'is_boosted' => $property->is_boosted ?? false,
                    'status' => $property->status,
                ];
            });

        $totalProperties = Property::where('owner_type', 'App\Models\Agent')
            ->whereIn('owner_id', $agentIds)
            ->count();

        return response()->json([
            'success' => true,
            'properties' => $properties,
            'hasMore' => $totalProperties > ($offset + 9)
        ]);
    }


    public function create()
    {
        return view('agent.real-estate-office');
    }

    public function showProfilePage($id)
    {
        $office = RealEstateOffice::findOrFail($id);
        return view('office.office-profile-page', compact('office'));
    }
    public function updateOfficeProfile(Request $request, $id)
    {
        $office = RealEstateOffice::findOrFail($id);

        $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:real_estate_offices,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
        ]);

        $office->update([
            'company_name' => $request->company_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'license_number' => $request->license_number,
            'address' => $request->address,
            'description' => $request->description,
        ]);

        return redirect()->route('office.profile.page', $id)->with('success', 'Profile updated successfully');
    }
    public function updateOfficePassword(Request $request, $id)
    {
        $office = RealEstateOffice::findOrFail($id);

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $office->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $office->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('office.profile.page', $id)->with('success', 'Password updated successfully');
    }
    public function updateProfile(Request $request)
    {
        $office = auth('office')->user();

        $request->validate([
            'company_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'company_bio' => 'nullable|string',
            'about_company' => 'nullable|string',
            'properties_sold' => 'nullable|integer|min:0',
            'years_experience' => 'nullable|integer|min:0',
            'current_plan' => 'nullable|in:starter,professional,enterprise',
            'office_address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'availability_schedule' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'company_bio_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only([
            'company_name',
            'phone_number',
            'company_bio',
            'about_company',
            'properties_sold',
            'years_experience',
            'current_plan',
            'office_address',
            'city',
            'district',
            'latitude',
            'longitude',
            'availability_schedule',
        ]);

        // ✅ Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($office->profile_image && Storage::disk('public')->exists($office->profile_image)) {
                Storage::disk('public')->delete($office->profile_image);
            }

            // Store new image in office_profiles folder
            $data['profile_image'] = $request->file('profile_image')->store('office_profiles', 'public');
        }

        // ✅ Handle company bio image upload
        if ($request->hasFile('company_bio_image')) {
            // Delete old image if exists
            if ($office->company_bio_image && Storage::disk('public')->exists($office->company_bio_image)) {
                Storage::disk('public')->delete($office->company_bio_image);
            }

            // Store new image in office_bio_images folder
            $data['company_bio_image'] = $request->file('company_bio_image')->store('office_bio_images', 'public');
        }

        $office->update($data);

        return redirect()->route('office.profile')->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $office = auth('office')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        // Check if current password is correct
        if (!Hash::check($request->current_password, $office->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        // Update password
        $office->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('office.profile')->with('success', 'Password changed successfully!');
    }
}
