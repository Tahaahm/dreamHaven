<?php

namespace App\Http\Controllers;

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
        // Filter properties by agent or office if provided
        if ($request->has('agent_id')) {
            $properties = Property::where('agent_id', $request->agent_id)->get();
        } elseif ($request->has('office_id')) {
            $properties = Property::where('office_id', $request->office_id)->get();
        } else {
            $properties = Property::all();
        }

        return response()->json($properties);
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
            'location' => 'required|json', // (latitude, longitude) as JSON
            'property_type' => 'required|string|in:apartment,house,commercial',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'kitchen_rooms' => 'integer',
            'reception_rooms' => 'integer',
            'area' => 'required|numeric',
            'images' => 'nullable|json', // List of image URLs as JSON
            'video_tour' => 'nullable|url',
            'amenities' => 'nullable|json', // List of amenities as JSON
            'project_name' => 'nullable|string',
            'project_description' => 'nullable|string',
            'status' => 'required|in:available,sold,rented',
            'listing_type' => 'required|in:rent,sell',
            'rating' => 'numeric',
            'views' => 'integer',
            'favorites_count' => 'integer',
            'availability' => 'boolean',
            'agent_id' => 'nullable|exists:agents,agent_id',
            'office_id' => 'nullable|exists:real_estate_offices,office_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $property = Property::create($request->all());

        return response()->json(['message' => 'Property created successfully', 'property' => $property], 201);
    }

    /**
     * Display the specified property.
     */
    public function show($id)
    {
        $property = Property::find($id);
        if (!$property) {
            return response()->json(['message' => 'Property not found'], 404);
        }
        return response()->json($property);
    }

    /**
     * Update the specified property in storage.
     */
    public function update(Request $request, $id)
    {
        $property = Property::find($id);
        if (!$property) {
            return response()->json(['message' => 'Property not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric',
            'address' => 'string',
            'location' => 'json', // (latitude, longitude) as JSON
            'property_type' => 'string|in:apartment,house,commercial',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'kitchen_rooms' => 'integer',
            'reception_rooms' => 'integer',
            'area' => 'numeric',
            'images' => 'nullable|json', // List of image URLs as JSON
            'video_tour' => 'nullable|url',
            'amenities' => 'nullable|json', // List of amenities as JSON
            'project_name' => 'string|nullable',
            'project_description' => 'string|nullable',
            'status' => 'in:available,sold,rented',
            'listing_type' => 'in:rent,sell',
            'rating' => 'numeric',
            'views' => 'integer',
            'favorites_count' => 'integer',
            'availability' => 'boolean',
            'agent_id' => 'nullable|exists:agents,agent_id',
            'office_id' => 'nullable|exists:real_estate_offices,office_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $property->update($request->all());

        return response()->json(['message' => 'Property updated successfully', 'property' => $property]);
    }

    /**
     * Remove the specified property from storage.
     */
    public function destroy($id)
    {
        $property = Property::find($id);
        if (!$property) {
            return response()->json(['message' => 'Property not found'], 404);
        }

        $property->delete();

        return response()->json(['message' => 'Property deleted successfully']);
    }
}
