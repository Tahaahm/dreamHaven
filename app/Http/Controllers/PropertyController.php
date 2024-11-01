<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
     /**
     * Display a listing of properties.
     */
    public function index(Request $request)
    {
        if ($request->has('agent_id')) {
            $properties = Property::where('agent_id', $request->agent_id)->get();
        } elseif ($request->has('office_id')) {
            $properties = Property::where('office_id', $request->office_id)->get();
        } else {
            $properties = Property::all();
        }

        return ApiResponse::success(
            ResponseDetails::successMessage('Properties retrieved successfully'),
            $properties,
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Store a newly created property in storage.
     */
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'required|numeric',
        'address' => 'required|string',
        'location' => 'required|json',
        'nameLocation' => 'nullable|string|max:255', // Added new field for descriptive location
        'property_type' => 'required|string|in:apartment,house,commercial',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'kitchen_rooms' => 'integer',
        'reception_rooms' => 'integer',
        'area' => 'required|numeric',
        'images' => 'nullable|json',
        'video_tour' => 'nullable|url',
        'amenities' => 'nullable|json',
        'project_name' => 'nullable|string',
        'project_description' => 'nullable|string',
        'status' => 'required|in:available,sold,rented',
        'listing_type' => 'required|in:rent,sell',
        'rating' => 'numeric',
        'views' => 'integer',
        'favorites_count' => 'integer',
        'availability' => 'boolean',
        'isBoosted' => 'boolean', // Added new field for promotion status
        'agent_id' => 'nullable|exists:agents,agent_id',
        'office_id' => 'nullable|exists:real_estate_offices,office_id'
    ]);

    if ($validator->fails()) {
        return ApiResponse::error(
            ResponseDetails::validationErrorMessage(),
            $validator->errors(),
            ResponseDetails::CODE_VALIDATION_ERROR
        );
    }

    // Create the property
    $property = Property::create($request->all());

    return ApiResponse::success(
        ResponseDetails::successMessage('Property created successfully'),
        $property,
        ResponseDetails::CODE_SUCCESS
    );
}


    /**
     * Display the specified property.
     */
    public function show($id)
    {
        $property = Property::find($id);
        if (!$property) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Property not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        return ApiResponse::success(
            ResponseDetails::successMessage('Property retrieved successfully'),
            $property,
            ResponseDetails::CODE_SUCCESS
        );
    }

    /**
     * Update the specified property in storage.
     */
    public function update(Request $request, $id)
{
    $property = Property::find($id);
    if (!$property) {
        return ApiResponse::error(
            ResponseDetails::notFoundMessage('Property not found'),
            null,
            ResponseDetails::CODE_NOT_FOUND
        );
    }

    $validator = Validator::make($request->all(), [
        'title' => 'string|max:255',
        'description' => 'string',
        'price' => 'numeric',
        'address' => 'string',
        'location' => 'json',
        'nameLocation' => 'nullable|string|max:255', // Added validation for nameLocation
        'property_type' => 'string|in:apartment,house,commercial',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'kitchen_rooms' => 'integer',
        'reception_rooms' => 'integer',
        'area' => 'numeric',
        'images' => 'nullable|json',
        'video_tour' => 'nullable|url',
        'amenities' => 'nullable|json',
        'project_name' => 'string|nullable',
        'project_description' => 'string|nullable',
        'status' => 'in:available,sold,rented',
        'listing_type' => 'in:rent,sell',
        'rating' => 'numeric',
        'views' => 'integer',
        'favorites_count' => 'integer',
        'availability' => 'boolean',
        'isBoosted' => 'boolean', // Added validation for isBoosted
        'agent_id' => 'nullable|exists:agents,agent_id',
        'office_id' => 'nullable|exists:real_estate_offices,office_id'
    ]);

    if ($validator->fails()) {
        return ApiResponse::error(
            ResponseDetails::validationErrorMessage(),
            $validator->errors(),
            ResponseDetails::CODE_VALIDATION_ERROR
        );
    }

    // Update the property with validated data
    $property->update($request->all());

    return ApiResponse::success(
        ResponseDetails::successMessage('Property updated successfully'),
        $property,
        ResponseDetails::CODE_SUCCESS
    );
}

    /**
     * Remove the specified property from storage.
     */
    public function destroy($id)
    {
        $property = Property::find($id);
        if (!$property) {
            return ApiResponse::error(
                ResponseDetails::notFoundMessage('Property not found'),
                null,
                ResponseDetails::CODE_NOT_FOUND
            );
        }

        $property->delete();

        return ApiResponse::success(
            ResponseDetails::successMessage('Property deleted successfully'),
            null,
            ResponseDetails::CODE_SUCCESS
        );
    }
}
