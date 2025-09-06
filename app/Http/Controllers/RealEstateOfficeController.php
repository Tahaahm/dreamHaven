<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\RealEstateOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
}
