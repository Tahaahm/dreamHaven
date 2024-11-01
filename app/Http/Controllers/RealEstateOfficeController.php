<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;

use App\Models\Agent;
use App\Models\RealEstateOffice;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

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



    /**
     * Display the specified real estate office.
     */
    public function show($id)
    {
        $office = RealEstateOffice::find($id);
        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage(),
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

    /**
     * Update the specified real estate office in storage.
     */
    public function update(Request $request, $id)
    {
        $agent = Agent::find($id);
        if (!$agent) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Agent not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $validator = Validator::make($request->all(), [
            'agent_name' => 'string|max:255',
            'email' => 'email|unique:agents,email,' . $agent->agent_id . ',agent_id',
            'phone' => 'string|max:20',
            'office_id' => 'nullable|exists:real_estate_offices,office_id',
            'profile_photo' => 'nullable|url',
            'is_verified' => 'boolean'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $agent->update($request->all());

        return ApiResponse::success(
            ResponseDetails::successMessage('Agent updated successfully'),
            $agent,
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
                ResponseDetails::notFoundMessage(),
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

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_email' => 'required|email',
            'password' => 'required|string',
        ]);


        if ($validator->fails()) {
            return ApiResponse::error(
                ResponseDetails::validationErrorMessage(),
                $validator->errors(),
                ResponseDetails::CODE_VALIDATION_ERROR
            );
        }

        $office = RealEstateOffice::where('admin_email', $request->admin_email)->first();
        if (!$office) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage(),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        if (!Hash::check($request->password, $office->password)) {
            Log::error('Password mismatch', [
                'provided_password' => $request->password,
                'stored_hashed_password' => $office->password
            ]);

            return ApiResponse::error(
                ResponseDetails::unauthorizedMessage(),
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
    }

    public function fetchProperties($id)
{

    Log::info("Fetching properties for office ID: $id");
    $office = RealEstateOffice::with('properties')->find($id);

    if (!$office) {
        return ApiResponse::error(
            ResponseDetails::notFoundMessage('Office not found'),
            null,
            ResponseDetails::CODE_NOT_FOUND
        );
    }

    return ApiResponse::success(
        ResponseDetails::successMessage('Properties retrieved successfully'),
        $office->properties,
        ResponseDetails::CODE_SUCCESS
    );
}

}
