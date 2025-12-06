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
            // ...other fields...
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

    /**
     * Display the real estate office profile page
     */
    public function profile($id)
    {
        // Debug guard
        Log::info('--- Agent Debug Start ---');
        Log::info('Auth check:', [
            'guard_check' => Auth::guard('agent')->check(),
            'guard_user' => Auth::guard('agent')->user(),
        ]);

        // Debug default auth
        Log::info('Default Auth:', [
            'default_check' => Auth::check(),
            'default_user' => Auth::user(),
        ]);

        // Debug session
        Log::info('Session Keys:', session()->all());

        // Debug cookies
        Log::info('Cookies:', request()->cookies->all());

        // Proceed with existing code
        $office = RealEstateOffice::with(['agents' => function ($query) {
            $query->take(6);
        }])->findOrFail($id);

        $totalAgents = Agent::where('company_id', $id)->count();

        $agentIds = Agent::where('company_id', $id)->pluck('id')->toArray();

        $properties = Property::where('owner_type', 'App\Models\Agent')
            ->whereIn('owner_id', $agentIds)
            ->with(['owner'])
            ->latest()
            ->take(9)
            ->get();

        $totalProperties = Property::where('owner_type', 'App\Models\Agent')
            ->whereIn('owner_id', $agentIds)
            ->count();

        Log::info('--- Agent Debug End ---');

        return view('agent.real-estate-office-profile', compact('office', 'properties', 'totalAgents', 'totalProperties'));
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
}
