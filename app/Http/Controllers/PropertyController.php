<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
     /**
     * Get all properties or filter by agent/office.
     */
    public function index(Request $request)
    {
        // Filter properties by agent or office if provided
        if ($request->has('agent_id')) {
            $properties = Property::where('agent_id', $request->agent_id)->get();
        } elseif ($request->has('office_id')) {
            $properties = Property::where('office_id', $request->office_id)->get();
        } else {
            // Retrieve all properties
            $properties = Property::all();
        }

        return response()->json($properties, 200);
    }

    /**
     * Get a specific property by ID.
     */
    public function show($id)
    {
        // Find the property by ID
        $property = Property::find($id);

        if (!$property) {
            return response()->json(['message' => 'Property not found'], 404);
        }

        return response()->json($property, 200);
    }

    /**
     * Create a new property for either an agent or real estate office.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'address' => 'required|string',
            'location' => 'required|json',  // GeoPoint as JSON
            'property_type' => 'required|string|in:apartment,house,commercial',
            'bedrooms' => 'required|integer',
            'bathrooms' => 'required|integer',
            'kitchen_rooms' => 'required|integer',
            'reception_rooms' => 'required|integer',
            'area' => 'required|numeric',
            'images' => 'nullable|json',  // List of image URLs
            'video_tour' => 'nullable|url',
            'amenities' => 'nullable|json',  // List of amenities
            'status' => 'required|in:available,sold,rented',
            'listing_type' => 'required|in:rent,sell',
            'agent_id' => 'nullable|exists:agents,agent_id',  // Reference to agent
            'office_id' => 'nullable|exists:real_estate_offices,office_id',  // Reference to office
        ]);

        // Create the property
        $property = Property::create($validatedData);

        return response()->json(['message' => 'Property created successfully', 'property' => $property], 201);
    }
}
