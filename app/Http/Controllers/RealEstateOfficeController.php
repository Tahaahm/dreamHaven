<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
<<<<<<< HEAD
use App\Models\RealEstateOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
=======

use App\Models\Agent;
use App\Models\RealEstateOffice;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
>>>>>>> myproject/main

class RealEstateOfficeController extends Controller
{
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

<<<<<<< HEAD
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'email_address' => 'required|email|unique:real_estate_offices,email_address',
            'phone_number' => 'required|string|max:20',
            'office_address' => 'nullable|string',
            'profile_image' => 'nullable|string',
            'company_bio' => 'nullable|string',
            'about_company' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'years_experience' => 'nullable|integer|min:0',
            'availability_schedule' => 'nullable|array',
            'current_plan' => 'nullable|in:starter,professional,enterprise',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $office = RealEstateOffice::create([
            'company_name' => $request->company_name,
            'email_address' => $request->email_address,
            'phone_number' => $request->phone_number,
            'office_address' => $request->office_address,
            'profile_image' => $request->profile_image,
            'company_bio' => $request->company_bio,
            'about_company' => $request->about_company,
            'city' => $request->city,
            'district' => $request->district,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'years_experience' => $request->years_experience ?? 0,
            'availability_schedule' => $request->availability_schedule,
            'current_plan' => $request->current_plan,
        ]);

        // Send office creation notification to relevant users/agents in the area
        if ($office->latitude && $office->longitude) {
            app(NotificationController::class)->sendNewOfficeNotification($office->id);
        }

        return ApiResponse::success(
            ResponseDetails::successMessage('Real Estate Office created successfully'),
            $office,
            ResponseDetails::CODE_SUCCESS
        );
    }
=======
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
        'profile_photo' => 'nullable|url',
        'description' => 'nullable|string',  // Add description validation
        'location' => 'nullable|json',       // Add location validation
        'password' => 'required|string|min:8',
    ]);

    if ($validator->fails()) {
        return ApiResponse::error(
            ResponseDetails::validationErrorMessage(),
            $validator->errors(),
            ResponseDetails::CODE_VALIDATION_ERROR
        );
    }

    $office = RealEstateOffice::create([
        'office_name' => $request->office_name,
        'admin_name' => $request->admin_name,
        'admin_email' => $request->admin_email,
        'phone' => $request->phone,
        'address' => $request->address,
        'profile_photo' => $request->profile_photo,
        'description' => $request->description,   // Save description
        'location' => $request->location,         // Save location
        'password' => $request->password,
    ]);

    return ApiResponse::success(
        ResponseDetails::successMessage('Real Estate Office created successfully'),
        $office,
        ResponseDetails::CODE_SUCCESS
    );
}



>>>>>>> myproject/main
    /**
     * Display the specified real estate office.
     */
    public function show($id)
    {
<<<<<<< HEAD
        // First, try without relationships to see if basic fetch works
        $office = RealEstateOffice::find($id);

        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Office not found'),
=======
        $office = RealEstateOffice::find($id);
        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage(),
>>>>>>> myproject/main
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }
<<<<<<< HEAD

=======
>>>>>>> myproject/main
        return ApiResponse::success(
            ResponseDetails::successMessage('Office retrieved successfully'),
            $office,
            ResponseDetails::CODE_SUCCESS
        );
    }

<<<<<<< HEAD
    public function update(Request $request, $id)
    {
        $office = RealEstateOffice::find($id);
        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Office not found'),
=======
    /**
     * Update the specified real estate office in storage.
     */
    public function update(Request $request, $id)
    {
        $agent = Agent::find($id);
        if (!$agent) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Agent not found'),
>>>>>>> myproject/main
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $validator = Validator::make($request->all(), [
<<<<<<< HEAD
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
=======
            'agent_name' => 'string|max:255',
            'email' => 'email|unique:agents,email,' . $agent->agent_id . ',agent_id',
            'phone' => 'string|max:20',
            'office_id' => 'nullable|exists:real_estate_offices,office_id',
            'profile_photo' => 'nullable|url',
            'is_verified' => 'boolean'
>>>>>>> myproject/main
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

<<<<<<< HEAD
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

=======
        $agent->update($request->all());

        return ApiResponse::success(
            ResponseDetails::successMessage('Agent updated successfully'),
            $agent,
            ResponseDetails::CODE_SUCCESS
        );
    }
>>>>>>> myproject/main
    /**
     * Remove the specified real estate office from storage.
     */
    public function destroy($id)
    {
        $office = RealEstateOffice::find($id);
        if (!$office) {
            return ApiResponse::error(
<<<<<<< HEAD
                ResponseDetails::notFoundMessage('Office not found'),
=======
                ResponseDetails::notFoundMessage(),
>>>>>>> myproject/main
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

<<<<<<< HEAD
    /**
     * Login method - Note: This requires password field in migration
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_address' => 'required|email',
            'password' => 'required|string',
        ]);

=======
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_email' => 'required|email',
            'password' => 'required|string',
        ]);


>>>>>>> myproject/main
        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

<<<<<<< HEAD
        $office = RealEstateOffice::where('email_address', $request->email_address)->first();
        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Office not found'),
=======
        $office = RealEstateOffice::where('admin_email', $request->admin_email)->first();
        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage(),
>>>>>>> myproject/main
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

<<<<<<< HEAD
        // Note: This requires password field in migration and HasApiTokens trait in model
        /*
        if (!Hash::check($request->password, $office->password)) {
            return ApiResponse::error(
                ResponseDetails::unauthorizedMessage('Invalid credentials'),
=======
        if (!Hash::check($request->password, $office->password)) {
            Log::error('Password mismatch', [
                'provided_password' => $request->password,
                'stored_hashed_password' => $office->password
            ]);

            return ApiResponse::error(
                ResponseDetails::unauthorizedMessage(),
>>>>>>> myproject/main
                null,
                ResponseDetails::CODE_UNAUTHORIZED
            );
        }

<<<<<<< HEAD
=======

>>>>>>> myproject/main
        $token = $office->createToken('authToken')->plainTextToken;

        return ApiResponse::success(
            ResponseDetails::successMessage('Login successful'),
            ['token' => $token, 'office' => $office],
            ResponseDetails::CODE_SUCCESS
        );
<<<<<<< HEAD
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
=======
    }
>>>>>>> myproject/main
}
