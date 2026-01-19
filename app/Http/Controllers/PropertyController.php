<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Property;
use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid pagination parameters',
                    $validator->errors(),
                    400
                );
            }

            $perPage = $request->get('per_page', 20);
            $user = auth('sanctum')->user();

            // ✅ LOG: User authentication status
            Log::info('📋 INDEX: Request started', [
                'endpoint' => 'index',
                'user_authenticated' => $user ? 'YES' : 'NO',
                'user_id' => $user?->id,
                'per_page' => $perPage,
            ]);

            $query = Property::active()
                ->published()
                ->whereNotIn('status', ['cancelled', 'pending']);

            // ✅ LOG: Check for viewed properties
            if ($user) {
                $viewedProperties = $user->recently_viewed_properties ?? [];
                $viewedCount = count($viewedProperties);

                Log::info('👤 INDEX: User viewing history', [
                    'user_id' => $user->id,
                    'has_viewed_properties' => $viewedCount > 0 ? 'YES' : 'NO',
                    'viewed_count' => $viewedCount,
                    'viewed_ids' => $viewedCount > 0 ? array_slice($viewedProperties, 0, 5) : [],
                ]);

                if ($viewedCount > 0) {
                    $query->whereNotIn('id', $viewedProperties);
                    Log::info('✅ INDEX: Exclusion applied', [
                        'reason' => 'User has viewing history',
                        'excluded_count' => $viewedCount,
                    ]);
                } else {
                    Log::info('ℹ️ INDEX: No exclusion needed', [
                        'reason' => 'User has no viewing history yet',
                    ]);
                }
            } else {
                Log::info('ℹ️ INDEX: No exclusion applied', [
                    'reason' => 'User not authenticated',
                ]);
            }

            $properties = $query->paginate($perPage);

            Log::info('✅ INDEX: Success', [
                'total_returned' => $properties->count(),
                'total_available' => $properties->total(),
                'exclusion_applied' => $user && count($user->recently_viewed_properties ?? []) > 0,
            ]);

            $transformedData = collect($properties->items())->map(function ($property) {
                return $this->transformPropertyData($property);
            });

            return ApiResponse::success(
                'Properties retrieved successfully',
                [
                    'data' => $transformedData,
                    'total' => $properties->total(),
                    'current_page' => $properties->currentPage(),
                    'per_page' => $properties->perPage(),
                    'last_page' => $properties->lastPage(),
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('❌ INDEX: Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth('sanctum')->user()?->id,
            ]);

            return ApiResponse::error(
                'Failed to retrieve properties',
                $e->getMessage(),
                500
            );
        }
    }


    // in PropertyController.php
    public function newindex()
    {
        // Optional: fetch some data for homepage
        $properties = Property::latest()->take(10)->get();

        return view('newIndex', compact('properties'));
    }




    public function search(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'search' => 'nullable|string|max:255',
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'sort' => 'in:price_asc,price_desc,area_asc,area_desc,newest,oldest,most_viewed',
                'min_price' => 'numeric|min:0',
                'max_price' => 'numeric|min:0',
                'currency' => 'in:USD,IQD',
                'bedrooms' => 'integer|min:0|max:20',
                'property_type' => 'string',
                'min_area' => 'numeric|min:0',
                'max_area' => 'numeric|min:0',
                'furnished' => 'boolean',
                'city' => 'string',
                'language' => 'in:en,ar,ku',
                'listing_type' => 'in:rent,sell',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid search parameters', $validator->errors(), 400);
            }

            $user = auth('sanctum')->user();
            $searchTerm = $request->get('search');

            // ✅ LOG: Start
            Log::info('🔍 SEARCH: Request started', [
                'endpoint' => 'search',
                'user_authenticated' => $user ? 'YES' : 'NO',
                'user_id' => $user?->id,
                'search_term' => $searchTerm,
                'filters' => array_filter($request->only([
                    'min_price',
                    'max_price',
                    'bedrooms',
                    'property_type',
                    'listing_type',
                    'city'
                ])),
            ]);

            $query = Property::query()->active()->published();

            // ✅ LOG: User exclusion logic
            if ($user) {
                $viewedProperties = $user->recently_viewed_properties ?? [];
                $viewedCount = count($viewedProperties);

                Log::info('👤 SEARCH: User viewing history', [
                    'user_id' => $user->id,
                    'has_viewed_properties' => $viewedCount > 0 ? 'YES' : 'NO',
                    'viewed_count' => $viewedCount,
                ]);

                if ($viewedCount > 0) {
                    $query->whereNotIn('id', $viewedProperties);
                    Log::info('✅ SEARCH: Exclusion applied', [
                        'excluded_count' => $viewedCount,
                        'reason' => 'User has viewing history',
                    ]);
                } else {
                    Log::info('ℹ️ SEARCH: No exclusion needed', [
                        'reason' => 'User has no viewing history',
                    ]);
                }
            } else {
                Log::info('ℹ️ SEARCH: No exclusion applied', [
                    'reason' => 'User not authenticated',
                ]);
            }

            $this->applySearchFilters($query, $request);
            $this->applySorting($query, $request->get('sort', 'newest'), $request->get('currency', 'usd'));

            $perPage = $request->get('per_page', 20);
            $properties = $query->paginate($perPage);

            Log::info('✅ SEARCH: Success', [
                'properties_found' => $properties->count(),
                'total_available' => $properties->total(),
                'search_term' => $searchTerm,
                'exclusion_applied' => $user && count($user->recently_viewed_properties ?? []) > 0,
            ]);

            $language = $request->get('language', 'en');
            $transformedData = collect($properties->items())->map(function ($property) use ($language) {
                return $this->transformPropertyForSearch($property, $language);
            });

            return ApiResponse::success(
                'Properties found',
                [
                    'data' => $transformedData,
                    'total' => $properties->total(),
                    'current_page' => $properties->currentPage(),
                    'per_page' => $properties->perPage(),
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('❌ SEARCH: Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth('sanctum')->user()?->id,
                'search_term' => $request->get('search'),
            ]);

            return ApiResponse::error('Search failed', $e->getMessage(), 500);
        }
    }
    private function getDefaultSearchPreferences()
    {
        return [
            'filters' => [
                'price_enabled' => false,
                'location_enabled' => false,
                'property_types' => [],
            ],
            'sorting' => [
                'price_enabled' => false,
                'popularity_enabled' => true,
            ]
        ];
    }


    public function show($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            // Increment views
            $property->increment('views');

            // ✅ Track user view if authenticated
            $user = auth('sanctum')->user();

            // ✅ ADD DEBUG LOGGING
            Log::info('🔍 Property View Attempt', [
                'property_id' => $id,
                'user_authenticated' => $user ? 'YES' : 'NO',
                'user_id' => $user?->id,
            ]);

            if ($user) {
                try {
                    $tracked = $this->interactionService->trackView($user->id, $property->id, [
                        'source' => request()->header('X-Source', 'app'),
                        'user_agent' => request()->header('User-Agent'),
                    ]);

                    Log::info('✅ Tracking Result', [
                        'success' => $tracked,
                        'user_id' => $user->id,
                        'property_id' => $id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('❌ Tracking Failed', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id,
                        'property_id' => $id,
                    ]);
                }
            }

            return ApiResponse::success(
                'Property retrieved successfully',
                $this->transformPropertyData($property),
                200
            );
        } catch (\Exception $e) {
            Log::error('Property show error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to retrieve property',
                $e->getMessage(),
                500
            );
        }
    }
    public function create()
    {
        return view('upload');
    }

    public function uploadImages(Request $request)
    {
        $urls = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('property_images', 'public');
                $urls[] = asset('storage/' . $path);
            }
        }

        return response()->json(['urls' => $urls]);
    }


    // ✅ REPLACE YOUR ENTIRE store() METHOD WITH THIS

    public function store(Request $request)
    {
        try {
            Log::info('🔍 Raw request received', [
                'has_files' => $request->hasFile('images'),
                'images_data' => $request->input('images'),
            ]);

            // Automatically assign logged-in agent as owner
            if (Auth::guard('agent')->check()) {
                $request->merge([
                    'owner_type' => 'Agent',
                    'owner_id' => Auth::guard('agent')->id()
                ]);
            }

            // ✅ PARSE JSON STRINGS
            $parsedData = [];
            foreach ($request->all() as $key => $value) {
                if (is_string($value) && $this->isJson($value)) {
                    $parsedData[$key] = json_decode($value, true);
                } else {
                    $parsedData[$key] = $value;
                }
            }

            Log::info('✅ Parsed data', ['parsed' => $parsedData]);

            // ✅ CHECK IF IMAGES ARE URLS (array) - NOT FILES
            if (!isset($parsedData['images']) || !is_array($parsedData['images']) || count($parsedData['images']) < 1) {
                Log::error('❌ No images provided', ['images' => $parsedData['images'] ?? 'missing']);
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'data' => ['images' => ['At least one image is required']]
                ], 400);
            }

            // ✅ VALIDATION
            $validator = Validator::make($parsedData, [
                'name' => 'required|array',
                'name.en' => 'required|string|max:255',
                'description' => 'required|array',
                'description.en' => 'required|string|min:10',
                'type' => 'required|array',
                'type.category' => 'required|string',
                'area' => 'required|numeric|min:1',
                'furnished' => 'required|boolean',
                'price' => 'required|array',
                'price.iqd' => 'required|numeric|min:1',
                'price.usd' => 'required|numeric|min:1',
                'listing_type' => 'required|in:rent,sell',
                'rooms' => 'required|array',
                'rooms.bedroom.count' => 'required|integer|min:0',
                'rooms.bathroom.count' => 'required|integer|min:0',
                'locations' => 'required|array|min:1',
                'locations.*.lat' => 'required|numeric|between:-90,90',
                'locations.*.lng' => 'required|numeric|between:-180,180',
                'address_details' => 'required|array',
                'address_details.city' => 'required|array',
                'address_details.city.en' => 'required|string|min:2',
                'images' => 'required|array|min:1',
                'images.*' => 'string|url',
                'electricity' => 'nullable|boolean',
                'water' => 'nullable|boolean',
                'internet' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                Log::error('❌ Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'data' => $validator->errors()
                ], 400);
            }

            DB::beginTransaction();

            // ✅ USE IMAGE URLs (already uploaded)
            $imageUrls = $parsedData['images'];
            Log::info('✅ Using image URLs', ['urls' => $imageUrls]);

            // ✅ BUILD PROPERTY DATA
            $propertyData = [
                'id' => $this->generateUniquePropertyId(),
                'owner_id' => $parsedData['owner_id'],
                'owner_type' => $this->getFullOwnerType($parsedData['owner_type'] ?? 'User'),

                // JSON fields
                'name' => json_encode($parsedData['name']),
                'description' => json_encode($parsedData['description']),
                'type' => json_encode($parsedData['type']),
                'price' => json_encode($parsedData['price']),
                'rooms' => json_encode($parsedData['rooms']),
                'locations' => json_encode($parsedData['locations']),
                'address_details' => json_encode($parsedData['address_details']),

                // Simple fields
                'listing_type' => $parsedData['listing_type'],
                'area' => (float) $parsedData['area'],

                // Boolean fields
                'furnished' => $parsedData['furnished'] ? 1 : 0,
                'electricity' => ($parsedData['electricity'] ?? true) ? 1 : 0,
                'water' => ($parsedData['water'] ?? true) ? 1 : 0,
                'internet' => ($parsedData['internet'] ?? false) ? 1 : 0,

                // ✅ IMAGE URLS
                'images' => json_encode($imageUrls),

                // Optional arrays
                'features' => json_encode($parsedData['features'] ?? []),
                'amenities' => json_encode($parsedData['amenities'] ?? []),

                // Optional JSON objects
                'furnishing_details' => json_encode($parsedData['furnishing_details'] ?? ['status' => 'unfurnished']),
                'floor_details' => isset($parsedData['floor_details']) && is_array($parsedData['floor_details'])
                    ? json_encode($parsedData['floor_details'])
                    : null,

                // Optional simple fields
                'rental_period' => $parsedData['rental_period'] ?? null,
                'floor_number' => isset($parsedData['floor_number']) ? (int) $parsedData['floor_number'] : null,
                'year_built' => isset($parsedData['year_built']) ? (int) $parsedData['year_built'] : null,
                'virtual_tour_url' => $parsedData['virtual_tour_url'] ?? null,
                'floor_plan_url' => $parsedData['floor_plan_url'] ?? null,
                'address' => $parsedData['address'] ?? null,

                // Availability
                'availability' => json_encode([
                    'status' => 'available',
                    'labels' => [
                        'en' => 'Available',
                        'ar' => 'متوفر',
                        'ku' => 'بەردەست'
                    ]
                ]),

                // System fields
                'verified' => 0,
                'is_active' => 1,
                'published' => 1,
                'status' => 'available',
                'views' => 0,
                'favorites_count' => 0,
                'rating' => 0,
                'is_boosted' => 0,

                // Analytics
                'view_analytics' => json_encode(['unique_views' => 0, 'returning_views' => 0]),
                'favorites_analytics' => json_encode(['last_30_days' => 0]),
            ];

            Log::info('🚀 Creating property', ['data' => $propertyData]);

            DB::table('properties')->insert($propertyData + [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $property = Property::find($propertyData['id']);

            DB::commit();

            Log::info('✅ Property created successfully', ['property_id' => $property->id]);

            return response()->json([
                'status' => true,
                'message' => 'Property created successfully',
                'redirect' => '/agent/properties',
                'data' => $property
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('❌ Error creating property', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to create property',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Helper: Check if string is valid JSON
     */
    private function isJson($string)
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        // Only update fields that exist in the form
        $updates = $request->only([
            'title',
            'description',
            'price',
            'location',
            'type',
            'photos', // or any other field your form sends
        ]);

        // Merge and preserve important fields
        $property->fill($updates);

        // Keep these always as they were (never overwrite)
        $property->owner_id = $property->owner_id ?? Auth::id();
        $property->published = $property->published ?? 1;
        $property->is_active = $property->is_active ?? 1;

        // Save back
        $property->save();

        return redirect()->route('agent.property.list')->with('success', 'Property updated successfully!');
    }

    /**
     * ✅ Dedicated Update Method for Mobile/API
     */
    /**
     * ✅ Dedicated Update Method for Mobile/API (Fixed for Casts)
     */
    public function updateMobile(Request $request, $id)
    {
        try {
            $property = Property::findOrFail($id);

            // 1. Parse JSON strings (Fix for Flutter sending nested objects as strings)
            $data = $request->all();
            $parsedData = [];

            foreach ($data as $key => $value) {
                if (is_string($value) && $this->isJson($value)) {
                    $parsedData[$key] = json_decode($value, true);
                } else {
                    $parsedData[$key] = $value;
                }
            }

            Log::info('📱 Mobile Update Request', ['id' => $id, 'parsed_data' => $parsedData]);

            // 2. Prepare Update Array
            $updatePayload = [];

            // JSON Fields: Re-encode them for the DB update
            // Note: Since we are using ->update(), if the model casts are 'array',
            // Laravel expects arrays, not JSON strings.
            // However, to be safe and explicit with raw updates or specific handling:

            if (isset($parsedData['name'])) $updatePayload['name'] = $parsedData['name'];
            if (isset($parsedData['description'])) $updatePayload['description'] = $parsedData['description'];
            if (isset($parsedData['price'])) $updatePayload['price'] = $parsedData['price'];
            if (isset($parsedData['type'])) $updatePayload['type'] = $parsedData['type'];
            if (isset($parsedData['rooms'])) $updatePayload['rooms'] = $parsedData['rooms'];
            if (isset($parsedData['locations'])) $updatePayload['locations'] = $parsedData['locations'];
            if (isset($parsedData['address_details'])) $updatePayload['address_details'] = $parsedData['address_details'];

            // ✅ Images: If sending new full list, use it directly
            if (isset($parsedData['images'])) $updatePayload['images'] = $parsedData['images'];

            // Simple fields
            if (isset($parsedData['listing_type'])) $updatePayload['listing_type'] = $parsedData['listing_type'];
            if (isset($parsedData['area'])) $updatePayload['area'] = $parsedData['area'];
            if (isset($parsedData['address'])) $updatePayload['address'] = $parsedData['address'];
            if (isset($parsedData['status'])) $updatePayload['status'] = $parsedData['status'];

            // Booleans
            if (isset($parsedData['furnished'])) $updatePayload['furnished'] = $parsedData['furnished'] ? true : false;
            if (isset($parsedData['electricity'])) $updatePayload['electricity'] = $parsedData['electricity'] ? true : false;
            if (isset($parsedData['water'])) $updatePayload['water'] = $parsedData['water'] ? true : false;
            if (isset($parsedData['internet'])) $updatePayload['internet'] = $parsedData['internet'] ? true : false;

            // Integers
            if (isset($parsedData['floor_number'])) $updatePayload['floor_number'] = (int)$parsedData['floor_number'];
            if (isset($parsedData['year_built'])) $updatePayload['year_built'] = (int)$parsedData['year_built'];

            // 3. Handle Image Removal Logic
            // If the app sends specific indexes/urls to remove, we process that here.
            // OTHERWISE, if the app sent a fresh 'images' array above, that overwrites everything.

            if (isset($parsedData['images_to_remove']) && is_array($parsedData['images_to_remove']) && !empty($parsedData['images_to_remove'])) {

                // ✅ CRITICAL FIX: Access directly as array (Laravel casts handles decoding)
                $currentImages = $property->images ?? [];

                // If mistakenly returned as string due to some raw query elsewhere
                if (is_string($currentImages)) {
                    $currentImages = json_decode($currentImages, true) ?? [];
                }

                // Remove images by Index (if integers passed) or by Value (if URL strings passed)
                foreach ($parsedData['images_to_remove'] as $removeItem) {
                    if (is_int($removeItem)) {
                        unset($currentImages[$removeItem]);
                    } else {
                        $key = array_search($removeItem, $currentImages);
                        if ($key !== false) unset($currentImages[$key]);
                    }
                }

                // Re-index array keys and save
                $updatePayload['images'] = array_values($currentImages);
            }

            // 4. Update Database
            // Because your model has $casts = ['images' => 'array', ...],
            // passing PHP arrays into update() is the correct way. Laravel will json_encode them automatically.
            $property->update($updatePayload);

            return ApiResponse::success(
                'Property updated successfully',
                $this->transformPropertyData($property->fresh()),
                200
            );
        } catch (\Exception $e) {
            Log::error('Mobile update error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return ApiResponse::error('Failed to update property', $e->getMessage(), 500);
        }
    }


    /**
     * Delete property
     */
    public function destroy($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            $property->delete();

            return ApiResponse::success(
                'Property deleted successfully',
                ['id' => $id],
                200
            );
        } catch (\Exception $e) {
            Log::error('Property delete error', [
                'message' => $e->getMessage(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to delete property',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Find nearby properties
     */
    public function nearby(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'radius' => 'integer|min:1|max:100',
                'limit' => 'integer|min:1|max:50',
                'language' => 'in:en,ar,ku'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid parameters for nearby search',
                    $validator->errors(),
                    400
                );
            }

            $lat = $request->lat;
            $lng = $request->lng;
            $radius = $request->get('radius', 10);
            $limit = $request->get('limit', 10);
            $language = $request->get('language', 'en');
            $user = auth('sanctum')->user();

            // ✅ LOG: Start
            Log::info('📍 NEARBY: Request started', [
                'endpoint' => 'nearby',
                'user_authenticated' => $user ? 'YES' : 'NO',
                'user_id' => $user?->id,
                'coordinates' => ['lat' => $lat, 'lng' => $lng],
                'radius_km' => $radius,
                'limit' => $limit,
            ]);

            $query = Property::whereRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(JSON_EXTRACT(locations, '$[0].lat'))) * cos(radians(JSON_EXTRACT(locations, '$[0].lng')) - radians(?)) + sin(radians(?)) * sin(radians(JSON_EXTRACT(locations, '$[0].lat'))))) <= ?",
                [$lat, $lng, $lat, $radius]
            )->where('is_active', true);

            // ✅ LOG: User exclusion logic
            if ($user) {
                $viewedProperties = $user->recently_viewed_properties ?? [];
                $viewedCount = count($viewedProperties);

                Log::info('👤 NEARBY: User viewing history', [
                    'user_id' => $user->id,
                    'has_viewed_properties' => $viewedCount > 0 ? 'YES' : 'NO',
                    'viewed_count' => $viewedCount,
                ]);

                if ($viewedCount > 0) {
                    $query->whereNotIn('id', $viewedProperties);
                    Log::info('✅ NEARBY: Exclusion applied', [
                        'excluded_count' => $viewedCount,
                        'reason' => 'User has viewing history',
                    ]);
                } else {
                    Log::info('ℹ️ NEARBY: No exclusion needed', [
                        'reason' => 'User has no viewing history',
                    ]);
                }
            } else {
                Log::info('ℹ️ NEARBY: No exclusion applied', [
                    'reason' => 'User not authenticated',
                ]);
            }

            $properties = $query->limit($limit)->get();

            Log::info('✅ NEARBY: Success', [
                'properties_found' => $properties->count(),
                'search_radius_km' => $radius,
                'center_point' => ['lat' => $lat, 'lng' => $lng],
                'exclusion_applied' => $user && count($user->recently_viewed_properties ?? []) > 0,
            ]);

            $transformedData = $properties->map(function ($property) use ($language, $lat, $lng) {
                $propertyLat = $property->locations[0]['lat'] ?? 0;
                $propertyLng = $property->locations[0]['lng'] ?? 0;
                $distance = $this->calculateDistance($lat, $lng, $propertyLat, $propertyLng);

                $data = $this->transformPropertyForSearch($property, $language);
                $data['distance_km'] = round($distance, 2);

                return $data;
            });

            return ApiResponse::success(
                'Nearby properties found',
                [
                    'data' => $transformedData,
                    'search_center' => ['lat' => $lat, 'lng' => $lng],
                    'radius_km' => $radius,
                    'total_found' => $transformedData->count()
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('❌ NEARBY: Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth('sanctum')->user()?->id,
                'coordinates' => ['lat' => $request->lat, 'lng' => $request->lng],
            ]);

            return ApiResponse::error(
                'Failed to find nearby properties',
                $e->getMessage(),
                500
            );
        }
    }
    /**
     * Get property statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total_properties' => Property::count(),
                'active_properties' => Property::where('is_active', true)->count(),
                'published_properties' => Property::where('published', true)->count(),
                'verified_properties' => Property::where('verified', true)->count(),
                'boosted_properties' => Property::where('is_boosted', true)->count(),

                // By listing type
                'for_rent' => Property::where('listing_type', 'rent')->count(),
                'for_sale' => Property::where('listing_type', 'sell')->count(),

                // By status
                'available' => Property::where('status', 'available')->count(),
                'sold' => Property::where('status', 'sold')->count(),
                'rented' => Property::where('status', 'rented')->count(),
                'pending' => Property::where('status', 'pending')->count(),

                // Analytics
                'total_views' => Property::sum('views'),
                'total_favorites' => Property::sum('favorites_count'),
                'average_rating' => Property::where('rating', '>', 0)->avg('rating'),

                // Time-based
                'properties_this_month' => Property::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count(),
                'properties_this_week' => Property::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),

                // Pricing
                'average_price_usd' => Property::avg(DB::raw("JSON_EXTRACT(price, '$.usd')")),
                'average_price_iqd' => Property::avg(DB::raw("JSON_EXTRACT(price, '$.iqd')")),

                // By type
                'by_type' => Property::select(
                    DB::raw("JSON_EXTRACT(type, '$.category') as property_type"),
                    DB::raw('COUNT(*) as count')
                )->groupBy('property_type')->get(),

                // Utilities
                'with_electricity' => Property::where('electricity', true)->count(),
                'with_water' => Property::where('water', true)->count(),
                'with_internet' => Property::where('internet', true)->count(),

                // Furnished stats
                'furnished' => Property::where('furnished', true)->count(),
                'unfurnished' => Property::where('furnished', false)->count(),
            ];

            return ApiResponse::success(
                'Property statistics retrieved',
                $stats,
                200
            );
        } catch (\Exception $e) {
            Log::error('Statistics error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return ApiResponse::error(
                'Failed to get statistics',
                $e->getMessage(),
                500
            );
        }
    }


    /**
     * Toggle verification status
     */
    public function toggleVerification($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            $wasVerified = $property->verified;
            $property->verified = !$property->verified;
            $property->save();

            // Send verification notification if property got verified
            if (!$wasVerified && $property->verified) {
                app(NotificationController::class)->sendPropertyVerificationNotification($property->id);
            }

            return ApiResponse::success(
                $property->verified ? 'Property verified' : 'Property verification removed',
                [
                    'id' => $property->id,
                    'verified' => $property->verified
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Property verification error', [
                'message' => $e->getMessage(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to toggle verification',
                $e->getMessage(),
                500
            );
        }
    }
    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            $property->is_active = !$property->is_active;
            $property->save();

            return ApiResponse::success(
                $property->is_active ? 'Property activated' : 'Property deactivated',
                [
                    'id' => $property->id,
                    'is_active' => $property->is_active
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Property active toggle error', [
                'message' => $e->getMessage(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to toggle active status',
                $e->getMessage(),
                500
            );
        }
    }

    // ===== PRIVATE HELPER METHODS =====

    /**
     * Transform property data for response
     */
    private function transformPropertyData($property)
    {
        return [
            'id' => $property->id,
            'owner_id' => $property->owner_id,
            'owner_type' => $property->owner_type,

            // Basic info
            'name' => $property->name,
            'description' => $property->description,
            'images' => $property->images ?? [],
            'main_image' => isset($property->images) && is_array($property->images) ? ($property->images[0] ?? null) : null,

            // Property details
            'availability' => $property->availability ?? [],
            'type' => $property->type ?? [],
            'area' => $property->area,
            'furnished' => $property->furnished,
            'furnishing_details' => $property->furnishing_details ?? [],

            // Pricing and listing
            'price' => $property->price ?? [],
            'listing_type' => $property->listing_type,
            'rental_period' => $property->rental_period,

            // Structure
            'rooms' => $property->rooms ?? [],
            'features' => $property->features ?? [],
            'amenities' => $property->amenities ?? [],

            // Location
            'locations' => $property->locations ?? [],
            'address_details' => $property->address_details ?? [],
            'address' => $property->address,

            // Building details
            'floor_number' => $property->floor_number,
            'floor_details' => $property->floor_details ?? [],
            'year_built' => $property->year_built,
            'construction_details' => $property->construction_details ?? [],

            // Energy and utilities
            'energy_rating' => $property->energy_rating,
            'energy_details' => $property->energy_details ?? [],
            'electricity' => $property->electricity,
            'water' => $property->water,
            'internet' => $property->internet,

            // Media
            'virtual_tour_url' => $property->virtual_tour_url,
            'virtual_tour_details' => $property->virtual_tour_details ?? [],
            'floor_plan_url' => $property->floor_plan_url,
            'additional_media' => $property->additional_media ?? [],

            // Status and verification
            'verified' => $property->verified,
            'verification_details' => $property->verification_details ?? [],
            'is_active' => $property->is_active,
            'published' => $property->published,
            'status' => $property->status,

            // Analytics
            'views' => $property->views,
            'view_analytics' => $property->view_analytics ?? [],
            'favorites_count' => $property->favorites_count,
            'favorites_analytics' => $property->favorites_analytics ?? [],
            'rating' => $property->rating,

            // Promotion
            'is_boosted' => $property->is_boosted,
            'boost_start_date' => $property->boost_start_date,
            'boost_end_date' => $property->boost_end_date,

            // Additional data
            'nearby_amenities' => $property->nearby_amenities ?? [],
            'legal_information' => $property->legal_information ?? [],
            'investment_analysis' => $property->investment_analysis ?? [],
            'seo_metadata' => $property->seo_metadata ?? [],

            // Timestamps
            'created_at' => $property->created_at,
            'updated_at' => $property->updated_at,
        ];
    }

    /**
     * Transform property data for search results
     */
    private function transformPropertyForSearch($property, $language = 'en')
    {
        return [
            'id' => $property->id,
            'name' => $this->getMultiLanguageField($property->name, $language),
            'description' => $this->getMultiLanguageField($property->description, $language),
            'images' => $property->images ?? [],
            'main_image' => isset($property->images) && is_array($property->images) ? ($property->images[0] ?? null) : null,

            // Pricing
            'price' => [
                'iqd' => $property->price['iqd'] ?? 0,
                'usd' => $property->price['usd'] ?? 0,
                'formatted_iqd' => $this->formatPrice($property->price['iqd'] ?? 0, 'IQD'),
                'formatted_usd' => $this->formatPrice($property->price['usd'] ?? 0, 'USD'),
            ],
            'listing_type' => $property->listing_type,
            'rental_period' => $property->rental_period,

            // Property details
            'area' => $property->area,
            'bedrooms' => $property->rooms['bedroom']['count'] ?? 0,
            'bathrooms' => $property->rooms['bathroom']['count'] ?? 0,
            'property_type' => $property->type['category'] ?? null,
            'furnished' => $property->furnished,

            // Location
            'locations' => $property->locations ?? [],
            'location' => $property->locations[0] ?? null,
            'address_details' => $property->address_details ?? [],
            'city' => $property->address_details['city'][$language] ?? $property->address_details['city']['en'] ?? null,
            'address' => $property->address,

            // Utilities
            'electricity' => $property->electricity,
            'water' => $property->water,
            'internet' => $property->internet,

            // Status
            'verified' => $property->verified,
            'is_active' => $property->is_active,
            'published' => $property->published,
            'status' => $property->status,

            // Analytics
            'views' => $property->views,
            'favorites_count' => $property->favorites_count,
            'rating' => $property->rating,

            // Media
            'virtual_tour_url' => $property->virtual_tour_url,
            'floor_plan_url' => $property->floor_plan_url,

            // Building info
            'floor_number' => $property->floor_number,
            'year_built' => $property->year_built,
            'energy_rating' => $property->energy_rating,

            // Promotion
            'is_boosted' => $property->is_boosted,
            'boost_active' => $property->isBoosted(),

            // Additional
            'features' => $property->features ?? [],
            'amenities' => $property->amenities ?? [],

            // Timestamps
            'created_at' => $property->created_at,
            'updated_at' => $property->updated_at,
        ];
    }
    /**
     * Load owner relationship
     */
    private function loadOwner($property)
    {
        if (!$property->owner_type || !$property->owner_id) {
            return null;
        }

        $ownerClass = $property->owner_type;
        if (class_exists($ownerClass)) {
            return $ownerClass::where('id', $property->owner_id)->first();
        }

        return null;
    }

    /**
     * Get owner information
     */
    private function getOwnerInfo($owner)
    {
        if (!$owner) {
            return [
                'id' => null,
                'name' => 'Unknown Agent',
                'type' => 'User',
                'email' => null,
                'phone' => null,
            ];
        }

        $baseInfo = [
            'id' => $owner->id,
            'name' => null,
            'type' => class_basename($owner),
            'email' => null,
            'phone' => null,
        ];

        switch (get_class($owner)) {
            case 'App\\Models\\User':
                return array_merge($baseInfo, [
                    'name' => $owner->username ?? $owner->name ?? 'User',
                    'email' => $owner->email,
                    'phone' => $owner->phone,
                ]);

            case 'App\\Models\\Agent':
                return array_merge($baseInfo, [
                    'name' => $owner->agent_name ?? $owner->name ?? 'Agent',
                    'email' => $owner->primary_email ?? $owner->email,
                    'phone' => $owner->primary_phone ?? $owner->phone,
                    'licenseNumber' => $owner->license_number,
                    'specialization' => $owner->specialization,
                    'address' => $owner->address,
                ]);

            case 'App\\Models\\RealEstateOffice':
                return array_merge($baseInfo, [
                    'name' => $owner->company_name ?? $owner->name ?? 'Real Estate Office',
                    'email' => $owner->email_address ?? $owner->email,
                    'phone' => $owner->phone_number ?? $owner->phone,
                    'address' => $owner->address,
                ]);

            default:
                return $baseInfo;
        }
    }

    /**
     * Convert short owner type to full class name
     */
    private function getFullOwnerType($shortType)
    {
        $mapping = [
            'User' => 'App\\Models\\User',
            'Agent' => 'App\\Models\\Agent',
            'RealEstateOffice' => 'App\\Models\\RealEstateOffice'
        ];

        return $mapping[$shortType] ?? $shortType;
    }

    /**
     * Get multi-language field value
     */
    private function getMultiLanguageField($field, $language)
    {
        if (is_string($field)) {
            return $field;
        }

        if (is_array($field)) {
            return $field[$language] ?? $field['en'] ?? $field['ar'] ?? $field['ku'] ?? '';
        }

        return '';
    }

    /**
     * Format price for display
     */
    private function formatPrice($price)
    {
        if (!isset($price['usd'])) return '';

        $amount = $price['usd'];

        if ($amount >= 1000000) {
            return '$' . number_format($amount / 1000000, 1) . 'M';
        } elseif ($amount >= 1000) {
            return '$' . number_format($amount / 1000, 0) . 'K';
        }

        return '$' . number_format($amount, 0);
    }


    private function applySorting($query, $sort, $currency = 'usd')
    {
        switch ($sort) {
            case 'price_asc':
                $query->orderByRaw("JSON_EXTRACT(price, '$.{$currency}') ASC");
                break;
            case 'price_desc':
                $query->orderByRaw("JSON_EXTRACT(price, '$.{$currency}') DESC");
                break;
            case 'area_asc':
                $query->orderBy('area', 'asc');
                break;
            case 'area_desc':
                $query->orderBy('area', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'most_viewed':
                $query->orderBy('views', 'desc');
                break;
            case 'most_favorited':
                $query->orderBy('favorites_count', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    /**
     * Get applied filters for response
     */
    private function getAppliedFilters(Request $request)
    {
        return array_filter([
            'price_range' => [
                'min' => $request->get('min_price'),
                'max' => $request->get('max_price'),
                'currency' => $request->get('currency', 'USD'),
            ],
            'bedrooms' => $request->get('bedrooms'),
            'bathrooms' => $request->get('bathrooms'),
            'property_type' => $request->get('property_type'),
            'area_range' => [
                'min' => $request->get('min_area'),
                'max' => $request->get('max_area'),
            ],
            'furnished' => $request->get('furnished'),
            'verified' => $request->get('verified'),
            'is_active' => $request->get('is_active'),
            'published' => $request->get('published'),
            'status' => $request->get('status'),
            'listing_type' => $request->get('listing_type'),
            'rental_period' => $request->get('rental_period'),
            'utilities' => [
                'electricity' => $request->get('electricity'),
                'water' => $request->get('water'),
                'internet' => $request->get('internet'),
            ],
            'boosted_only' => $request->get('boosted_only'),
            'min_rating' => $request->get('min_rating'),
            'features' => $request->get('features'),
            'amenities' => $request->get('amenities'),
            'year_built_range' => [
                'from' => $request->get('year_built_from'),
                'to' => $request->get('year_built_to'),
            ],
            'owner_id' => $request->get('owner_id'),
            'owner_type' => $request->get('owner_type'),
            'city' => $request->get('city'),
        ]);
    }

    /**
     * Calculate distance between two coordinates
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        return sqrt(pow($lat2 - $lat1, 2) + pow($lng2 - $lng1, 2));
    }



    public function getFeatured(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'integer|min:1|max:50',
                'language' => 'in:en,ar,ku',
                'strategy' => 'in:balanced,premium,engagement,recent'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid parameters',
                    $validator->errors(),
                    400
                );
            }

            $limit = $request->get('limit', 10);
            $language = $request->get('language', 'en');
            $strategy = $request->get('strategy', 'balanced');

            // Base query
            $baseQuery = Property::where('is_active', true)
                ->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented']);

            $featured = $this->getFeaturedByStrategy($baseQuery, $strategy, $limit);

            // Transform using FULL property data
            $transformedData = $featured->map(function ($property) use ($language) {
                $propertyData = $this->transformPropertyData($property);
                $propertyData['featured_reason'] = $this->getFeaturedReason($property);
                $propertyData['featured_score'] = $this->calculateFeaturedScore($property);
                return $propertyData;
            });

            return ApiResponse::success(
                'Featured properties retrieved',
                [
                    'data' => $transformedData,
                    'total' => $transformedData->count(),
                    'strategy' => $strategy
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Featured properties error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return ApiResponse::error(
                'Failed to get featured properties',
                $e->getMessage(),
                500
            );
        }
    }



    /**
     * Get featured properties based on different strategies
     */
    private function getFeaturedByStrategy($baseQuery, $strategy, $limit)
    {
        switch ($strategy) {
            case 'premium':
                return $this->getPremiumFeatured($baseQuery, $limit);

            case 'engagement':
                return $this->getEngagementFeatured($baseQuery, $limit);

            case 'recent':
                return $this->getRecentFeatured($baseQuery, $limit);

            case 'balanced':
            default:
                return $this->getBalancedFeatured($baseQuery, $limit);
        }
    }
    public function getRecommended(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'integer|min:1|max:50',
                'language' => 'in:en,ar,ku',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $limit = $request->get('limit', 20);
            $user = auth('sanctum')->user();

            // ✅ LOG: Start
            Log::info('🎯 RECOMMENDED: Request started', [
                'endpoint' => 'getRecommended',
                'user_authenticated' => $user ? 'YES' : 'NO',
                'user_id' => $user?->id,
                'limit' => $limit,
            ]);

            if ($user) {
                // ✅ LOG: Using personalized recommendations
                Log::info('👤 RECOMMENDED: Fetching personalized', [
                    'user_id' => $user->id,
                    'strategy' => 'personalized',
                ]);

                try {
                    $properties = $this->interactionService->getPersonalizedRecommendations($user->id, $limit);

                    Log::info('✅ RECOMMENDED: Personalized success', [
                        'user_id' => $user->id,
                        'properties_found' => $properties->count(),
                        'based_on_history' => true,
                    ]);
                } catch (\Exception $e) {
                    Log::error('❌ RECOMMENDED: Personalized failed, falling back to general', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);

                    // Fallback to general recommendations
                    $properties = Property::query()
                        ->where('is_active', true)
                        ->where('published', true)
                        ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                        ->selectRaw('
                        *,
                        (
                            (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                            (CASE WHEN verified = 1 THEN 20 ELSE 0 END) +
                            (LEAST(views, 100) * 0.15) +
                            (LEAST(favorites_count, 50) * 0.5) +
                            (rating * 5) +
                            (CASE
                                WHEN DATEDIFF(NOW(), created_at) <= 7 THEN 20
                                WHEN DATEDIFF(NOW(), created_at) <= 14 THEN 15
                                WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10
                                ELSE 0
                            END)
                        ) as recommendation_score
                    ')
                        ->orderByDesc('recommendation_score')
                        ->limit($limit)
                        ->get();
                }
            } else {
                // ✅ LOG: Using general recommendations
                Log::info('ℹ️ RECOMMENDED: Fetching general', [
                    'reason' => 'User not authenticated',
                    'strategy' => 'general',
                ]);

                $properties = Property::query()
                    ->where('is_active', true)
                    ->where('published', true)
                    ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                    ->selectRaw('
                    *,
                    (
                        (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                        (CASE WHEN verified = 1 THEN 20 ELSE 0 END) +
                        (LEAST(views, 100) * 0.15) +
                        (LEAST(favorites_count, 50) * 0.5) +
                        (rating * 5) +
                        (CASE
                            WHEN DATEDIFF(NOW(), created_at) <= 7 THEN 20
                            WHEN DATEDIFF(NOW(), created_at) <= 14 THEN 15
                            WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10
                            ELSE 0
                        END)
                    ) as recommendation_score
                ')
                    ->orderByDesc('recommendation_score')
                    ->limit($limit)
                    ->get();

                Log::info('✅ RECOMMENDED: General success', [
                    'properties_found' => $properties->count(),
                    'strategy_used' => 'general',
                ]);
            }

            $transformedData = $properties->map(function ($property) {
                return $this->transformPropertyData($property);
            });

            Log::info('✅ RECOMMENDED: Response ready', [
                'total_properties' => $transformedData->count(),
                'personalized' => $user ? true : false,
            ]);

            return ApiResponse::success(
                'Recommended properties retrieved',
                [
                    'data' => $transformedData,
                    'total' => $transformedData->count(),
                    'personalized' => $user ? true : false,
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('❌ RECOMMENDED: Fatal error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth('sanctum')->user()?->id,
            ]);

            return ApiResponse::error('Failed to get recommended properties', $e->getMessage(), 500);
        }
    }
    /**
     * Balanced approach - mix of all factors (MORE SELECTIVE)
     */
    private function getBalancedFeatured($baseQuery, $limit)
    {
        $totalCount = $baseQuery->count();
        $topPercentile = max(1, intval($totalCount * 0.15)); // Top 15%

        $candidates = $baseQuery
            ->select('*')
            ->selectRaw('
            (
                -- ✅ FRESHNESS BONUS (0-40 points)
                (CASE
                    WHEN DATEDIFF(NOW(), created_at) <= 1 THEN 40
                    WHEN DATEDIFF(NOW(), created_at) <= 3 THEN 35
                    WHEN DATEDIFF(NOW(), created_at) <= 7 THEN 30
                    WHEN DATEDIFF(NOW(), created_at) <= 14 THEN 20
                    WHEN DATEDIFF(NOW(), created_at) <= 30 THEN 10
                    ELSE 0
                END) +

                -- Boost score (40 points)
                (CASE WHEN is_boosted = 1 AND boost_start_date <= NOW()
                      AND (boost_end_date IS NULL OR boost_end_date >= NOW())
                 THEN 40 ELSE 0 END) +

                -- Performance score (25 points)
                (CASE
                    WHEN views >= 50 THEN 15
                    WHEN views >= 25 THEN 12
                    WHEN views >= 10 THEN 8
                    ELSE views * 0.5
                END) +
                (CASE
                    WHEN favorites_count >= 10 THEN 10
                    WHEN favorites_count >= 5 THEN 8
                    WHEN favorites_count >= 2 THEN 5
                    ELSE favorites_count * 2
                END) +

                -- Quality score (20 points)
                (CASE WHEN verified = 1 THEN 10 ELSE 0 END) +
                (CASE WHEN rating >= 4.5 THEN 8
                      WHEN rating >= 4.0 THEN 6
                      WHEN rating >= 3.5 THEN 4
                      ELSE rating END) +
                (CASE WHEN JSON_LENGTH(images) >= 5 THEN 5 ELSE 0 END) +

                -- Premium content (10 points)
                (CASE WHEN virtual_tour_url IS NOT NULL THEN 5 ELSE 0 END) +
                (CASE WHEN floor_plan_url IS NOT NULL THEN 3 ELSE 0 END) +
                (CASE WHEN energy_rating IN ("A+", "A") THEN 2 ELSE 0 END)

            ) as featured_score
        ')
            // ✅ LOWERED threshold: 15 points (was 35)
            ->having('featured_score', '>=', 15)
            ->orderByDesc('featured_score')
            ->orderByDesc('is_boosted')
            ->orderByDesc('created_at') // ✅ NEW: Recent properties first
            ->limit(min($limit, $topPercentile * 2))
            ->get();

        return $this->diversifyFeaturedProperties($candidates, $limit);
    }

    /**
     * Premium strategy - prioritize paid and high-quality
     */
    private function getPremiumFeatured($baseQuery, $limit)
    {
        return $baseQuery
            ->where(function ($query) {
                $query->where('is_boosted', true)
                    ->orWhere('verified', true)
                    ->orWhere('rating', '>=', 4);
            })
            ->orderByRaw('
            (CASE WHEN is_boosted = 1 THEN 3 ELSE 0 END) +
            (CASE WHEN verified = 1 THEN 2 ELSE 0 END) +
            (rating / 5) DESC
        ')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    /**
     * Engagement strategy - most popular properties
     */
    private function getEngagementFeatured($baseQuery, $limit)
    {
        return $baseQuery
            ->where('views', '>', 0)
            ->orderByRaw('(views * 0.7) + (favorites_count * 2) + (rating * 10) DESC')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Recent strategy - newest and recently updated
     */
    private function getRecentFeatured($baseQuery, $limit)
    {
        return $baseQuery
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->orderByDesc('is_boosted')
            ->orderByDesc('verified')
            ->limit($limit)
            ->get();
    }
    public function getRecent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'integer|min:1|max:50',
                'language' => 'in:en,ar,ku',
                'days' => 'integer|min:1|max:90',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $limit = $request->get('limit', 20);
            $days = $request->get('days', 30);
            $user = auth('sanctum')->user();

            // ✅ LOG: Start
            Log::info('🆕 RECENT: Request started', [
                'endpoint' => 'getRecent',
                'user_authenticated' => $user ? 'YES' : 'NO',
                'user_id' => $user?->id,
                'limit' => $limit,
                'days' => $days,
            ]);

            $query = Property::query()
                ->where('is_active', true)
                ->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                ->where('created_at', '>=', now()->subDays($days));

            // ✅ LOG: User exclusion logic
            if ($user) {
                $viewedProperties = $user->recently_viewed_properties ?? [];
                $viewedCount = count($viewedProperties);

                Log::info('👤 RECENT: User viewing history', [
                    'user_id' => $user->id,
                    'has_viewed_properties' => $viewedCount > 0 ? 'YES' : 'NO',
                    'viewed_count' => $viewedCount,
                ]);

                if ($viewedCount > 0) {
                    $query->whereNotIn('id', $viewedProperties);
                    Log::info('✅ RECENT: Exclusion applied', [
                        'excluded_count' => $viewedCount,
                        'reason' => 'User has viewing history',
                    ]);
                } else {
                    Log::info('ℹ️ RECENT: No exclusion needed', [
                        'reason' => 'User has no viewing history',
                    ]);
                }
            } else {
                Log::info('ℹ️ RECENT: No exclusion applied', [
                    'reason' => 'User not authenticated',
                ]);
            }

            $properties = $query
                ->orderByDesc('created_at')
                ->orderByDesc('is_boosted')
                ->limit($limit)
                ->get();

            Log::info('✅ RECENT: Success', [
                'properties_found' => $properties->count(),
                'date_range' => now()->subDays($days)->format('Y-m-d') . ' to ' . now()->format('Y-m-d'),
                'exclusion_applied' => $user && count($user->recently_viewed_properties ?? []) > 0,
            ]);

            $transformedData = $properties->map(function ($property) {
                return $this->transformPropertyData($property);
            });

            return ApiResponse::success(
                'Recent properties retrieved',
                [
                    'data' => $transformedData,
                    'total' => $transformedData->count(),
                    'days' => $days
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('❌ RECENT: Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth('sanctum')->user()?->id,
            ]);

            return ApiResponse::error('Failed to get recent properties', $e->getMessage(), 500);
        }
    }

    public function getRecentlyViewed(Request $request)
    {
        try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return ApiResponse::error('Authentication required', null, 401);
            }

            $validator = Validator::make($request->all(), [
                'limit' => 'integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $limit = $request->get('limit', 20);

            $properties = $this->interactionService->getRecentlyViewed($user->id, $limit);

            $transformedData = $properties->map(function ($property) {
                return $this->transformPropertyData($property);
            });

            return ApiResponse::success(
                'Recently viewed properties retrieved',
                [
                    'data' => $transformedData,
                    'total' => $transformedData->count(),
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Recently viewed error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to get recently viewed properties', $e->getMessage(), 500);
        }
    }

    public function getMyViewingStats(Request $request)
    {
        try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return ApiResponse::error('Authentication required', null, 401);
            }

            $stats = $user->getViewingStatistics();

            return ApiResponse::success(
                'Viewing statistics retrieved',
                $stats,
                200
            );
        } catch (\Exception $e) {
            Log::error('Viewing stats error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to get viewing statistics', $e->getMessage(), 500);
        }
    }
    /**
     * Calculate featured score for a property
     */
    private function calculateFeaturedScore($property)
    {
        $score = 0;

        // ✅ FRESHNESS BONUS (0-40 points) - NEW PROPERTIES GET PRIORITY!
        $daysSinceCreated = $property->created_at->diffInDays(now());
        if ($daysSinceCreated <= 1) {
            $score += 40; // Brand new (today)
        } elseif ($daysSinceCreated <= 3) {
            $score += 35; // Very recent (1-3 days)
        } elseif ($daysSinceCreated <= 7) {
            $score += 30; // Recent (3-7 days)
        } elseif ($daysSinceCreated <= 14) {
            $score += 20; // This week (7-14 days)
        } elseif ($daysSinceCreated <= 30) {
            $score += 10; // This month (14-30 days)
        }

        // Boost bonus (40 points)
        if (
            $property->is_boosted &&
            $property->boost_start_date <= now() &&
            (!$property->boost_end_date || $property->boost_end_date >= now())
        ) {
            $score += 40;
        }

        // Performance metrics (25 points max)
        $score += min($property->views / 10, 15); // Up to 15 for views
        $score += min($property->favorites_count * 2, 10); // Up to 10 for favorites

        // Quality indicators (20 points max)
        if ($property->verified) $score += 10;
        $score += min($property->rating, 5);
        $score += min(count($property->images ?? []), 5);

        // Content completeness (10 points max)
        if ($property->virtual_tour_url) $score += 3;
        if ($property->floor_plan_url) $score += 2;
        if (!empty($property->description['en']) && strlen($property->description['en']) > 100) $score += 3;
        if (count($property->features ?? []) >= 5) $score += 2;

        return round($score, 2);
    }
    public function getPopular(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'integer|min:1|max:50',
                'language' => 'in:en,ar,ku',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $limit = $request->get('limit', 20);
            $user = auth('sanctum')->user();

            // ✅ LOG: Start
            Log::info('🔥 POPULAR: Request started', [
                'endpoint' => 'getPopular',
                'user_authenticated' => $user ? 'YES' : 'NO',
                'user_id' => $user?->id,
                'limit' => $limit,
            ]);

            $query = Property::where('is_active', true)
                ->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                ->where(function ($q) {
                    $q->where('views', '>', 0)
                        ->orWhere('favorites_count', '>', 0);
                });

            // ✅ LOG: User exclusion logic
            if ($user) {
                $viewedProperties = $user->recently_viewed_properties ?? [];
                $viewedCount = count($viewedProperties);

                Log::info('👤 POPULAR: User viewing history', [
                    'user_id' => $user->id,
                    'has_viewed_properties' => $viewedCount > 0 ? 'YES' : 'NO',
                    'viewed_count' => $viewedCount,
                ]);

                if ($viewedCount > 0) {
                    $query->whereNotIn('id', $viewedProperties);
                    Log::info('✅ POPULAR: Exclusion applied', [
                        'excluded_count' => $viewedCount,
                        'reason' => 'User has viewing history',
                    ]);
                } else {
                    Log::info('ℹ️ POPULAR: No exclusion needed', [
                        'reason' => 'User has no viewing history',
                    ]);
                }
            } else {
                Log::info('ℹ️ POPULAR: No exclusion applied', [
                    'reason' => 'User not authenticated',
                ]);
            }

            $properties = $query
                ->orderByRaw('(views * 0.6) + (favorites_count * 2) + (rating * 10) DESC')
                ->limit($limit)
                ->get();

            Log::info('✅ POPULAR: Success', [
                'properties_found' => $properties->count(),
                'exclusion_applied' => $user && count($user->recently_viewed_properties ?? []) > 0,
            ]);

            $transformedData = $properties->map(function ($property) {
                return $this->transformPropertyData($property);
            });

            return ApiResponse::success(
                'Popular properties retrieved',
                [
                    'data' => $transformedData,
                    'total' => $transformedData->count()
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('❌ POPULAR: Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth('sanctum')->user()?->id,
            ]);

            return ApiResponse::error('Failed to get popular properties', $e->getMessage(), 500);
        }
    }
    /**
     * Get human-readable reason why property is featured
     */
    private function getFeaturedReason($property)
    {
        $reasons = [];

        // ✅ NEW: Show if property is new
        $daysSinceCreated = $property->created_at->diffInDays(now());
        if ($daysSinceCreated <= 7) {
            $reasons[] = 'New listing';
        }

        if ($property->is_boosted) {
            $reasons[] = 'Promoted listing';
        }

        if ($property->verified) {
            $reasons[] = 'Verified property';
        }

        if ($property->views > 100) {
            $reasons[] = 'High popularity';
        }

        if ($property->rating >= 4) {
            $reasons[] = 'Highly rated';
        }

        if ($property->favorites_count > 10) {
            $reasons[] = 'Frequently saved';
        }

        if (count($property->images ?? []) >= 8) {
            $reasons[] = 'Comprehensive photos';
        }

        if ($property->virtual_tour_url) {
            $reasons[] = 'Virtual tour available';
        }

        return $reasons;
    }

    /**
     * Add a property to featured (admin function)
     */
    public function addToFeatured(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'featured_until' => 'nullable|date|after:now',
                'reason' => 'nullable|string|max:255',
                'priority' => 'integer|between:1,10'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $property = Property::find($id);
            if (!$property) {
                return ApiResponse::error('Property not found', ['id' => $id], 404);
            }

            // Add to featured (you might want a separate featured_properties table)
            $property->update([
                'is_boosted' => true,
                'boost_start_date' => now(),
                'boost_end_date' => $request->get('featured_until'),
            ]);

            return ApiResponse::success('Property added to featured', [
                'id' => $property->id,
                'featured_until' => $request->get('featured_until'),
                'reason' => $request->get('reason'),
            ], 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to add to featured', $e->getMessage(), 500);
        }
    }

    /**
     * Remove from featured
     */
    public function removeFromFeatured($id)
    {
        try {
            $property = Property::find($id);
            if (!$property) {
                return ApiResponse::error('Property not found', ['id' => $id], 404);
            }

            $property->update([
                'is_boosted' => false,
                'boost_start_date' => null,
                'boost_end_date' => null,
            ]);

            return ApiResponse::success('Property removed from featured', ['id' => $property->id], 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to remove from featured', $e->getMessage(), 500);
        }
    }

    /**
     * Get featured properties analytics
     */
    public function getFeaturedAnalytics()
    {
        try {
            $analytics = [
                'total_featured' => Property::where('is_boosted', true)->count(),
                'active_featured' => Property::where('is_boosted', true)
                    ->where('boost_start_date', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('boost_end_date')
                            ->orWhere('boost_end_date', '>=', now());
                    })->count(),
                'average_views' => Property::where('is_boosted', true)->avg('views'),
                'average_favorites' => Property::where('is_boosted', true)->avg('favorites_count'),
                'performance_comparison' => [
                    'featured_avg_views' => Property::where('is_boosted', true)->avg('views'),
                    'regular_avg_views' => Property::where('is_boosted', false)->avg('views'),
                    'featured_avg_favorites' => Property::where('is_boosted', true)->avg('favorites_count'),
                    'regular_avg_favorites' => Property::where('is_boosted', false)->avg('favorites_count'),
                ]
            ];

            return ApiResponse::success('Featured analytics', $analytics, 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get featured analytics', $e->getMessage(), 500);
        }
    }

    /**
     * Ensure diversity in featured properties (avoid showing similar properties)
     */
    private function diversifyFeaturedProperties($candidates, $finalLimit)
    {
        if ($candidates->count() <= $finalLimit) {
            return $candidates;
        }

        $selected = collect();
        $cityCount = [];
        $typeCount = [];

        // Prioritize variety while maintaining quality scores
        foreach ($candidates as $candidate) {
            $city = $candidate->address_details['city']['en'] ?? 'Unknown';
            $propertyType = $candidate->type['category'] ?? 'Unknown';

            $cityLimit = max(1, intval($finalLimit * 0.4)); // Max 40% from same city
            $typeLimit = max(1, intval($finalLimit * 0.5)); // Max 50% of same type

            $canAdd = true;

            // Check city diversity
            if (($cityCount[$city] ?? 0) >= $cityLimit) {
                $canAdd = false;
            }

            // Check property type diversity
            if (($typeCount[$propertyType] ?? 0) >= $typeLimit) {
                $canAdd = false;
            }

            if ($canAdd && $selected->count() < $finalLimit) {
                $selected->push($candidate);
                $cityCount[$city] = ($cityCount[$city] ?? 0) + 1;
                $typeCount[$propertyType] = ($typeCount[$propertyType] ?? 0) + 1;
            }

            if ($selected->count() >= $finalLimit) {
                break;
            }
        }

        // If we still need more properties, add highest scoring ones regardless of diversity
        if ($selected->count() < $finalLimit) {
            $remaining = $candidates->diff($selected)->take($finalLimit - $selected->count());
            $selected = $selected->merge($remaining);
        }

        return $selected;
    }
    public function getByListingType($listingType, Request $request)
    {
        try {
            $validator = Validator::make(['listing_type' => $listingType] + $request->all(), [
                'listing_type' => 'required|in:rent,sell',
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'language' => 'in:en,ar,ku'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid parameters',
                    $validator->errors(),
                    400
                );
            }

            $perPage = $request->get('per_page', 20);
            $language = $request->get('language', 'en');

            $properties = Property::where('listing_type', $listingType)
                ->where('is_active', true)
                ->where('published', true)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $transformedData = collect($properties->items())->map(function ($property) use ($language) {
                return $this->transformPropertyForSearch($property, $language);
            });

            return ApiResponse::success(
                'Properties for ' . $listingType . ' retrieved',
                [
                    'data' => $transformedData,
                    'total' => $properties->total(),
                    'current_page' => $properties->currentPage(),
                    'per_page' => $properties->perPage(),
                    'listing_type' => $listingType
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Get by listing type error', [
                'message' => $e->getMessage(),
                'listing_type' => $listingType
            ]);

            return ApiResponse::error(
                'Failed to get properties by listing type',
                $e->getMessage(),
                500
            );
        }
    }
    public function getBoosted(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'integer|min:1|max:50',
                'language' => 'in:en,ar,ku'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid parameters',
                    $validator->errors(),
                    400
                );
            }

            $limit = $request->get('limit', 20);
            $language = $request->get('language', 'en');

            $boosted = Property::where('is_boosted', true)
                ->where('boost_start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('boost_end_date')
                        ->orWhere('boost_end_date', '>=', now());
                })
                ->where('is_active', true)
                ->where('published', true)
                ->orderBy('boost_start_date', 'desc')
                ->limit($limit)
                ->get();

            $transformedData = $boosted->map(function ($property) use ($language) {
                return $this->transformPropertyForSearch($property, $language);
            });

            return ApiResponse::success(
                'Boosted properties retrieved',
                [
                    'data' => $transformedData,
                    'total' => $transformedData->count()
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Get boosted properties error', [
                'message' => $e->getMessage()
            ]);

            return ApiResponse::error(
                'Failed to get boosted properties',
                $e->getMessage(),
                500
            );
        }
    }
    public function getByOwner(Request $request, $ownerType, $ownerId)
    {
        try {
            $validator = Validator::make([
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
            ] + $request->all(), [
                'owner_type' => 'required|in:User,Agent,RealEstateOffice',
                'owner_id' => 'required|string',
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'language' => 'in:en,ar,ku'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid parameters',
                    $validator->errors(),
                    400
                );
            }

            $fullOwnerType = $this->getFullOwnerType($ownerType);
            $language = $request->get('language', 'en');
            $perPage = $request->get('per_page', 20);

            $properties = Property::where('owner_type', $fullOwnerType)
                ->where('owner_id', $ownerId)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // ✅ Use transformPropertyData instead of transformPropertyForSearch to get FULL data like index
            $transformedData = collect($properties->items())->map(function ($property) {
                return $this->transformPropertyData($property);
            });

            return ApiResponse::success(
                'Owner properties retrieved',
                [
                    'data' => $transformedData,
                    'total' => $properties->total(),
                    'current_page' => $properties->currentPage(),
                    'per_page' => $properties->perPage(),
                    'owner' => [
                        'type' => $ownerType,
                        'id' => $ownerId
                    ]
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Owner properties error', [
                'message' => $e->getMessage(),
                'owner_type' => $ownerType,
                'owner_id' => $ownerId
            ]);

            return ApiResponse::error(
                'Failed to get owner properties',
                $e->getMessage(),
                500
            );
        }
    }
    public function toggleBoost($id, Request $request)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            $validator = Validator::make($request->all(), [
                'boost_start_date' => 'nullable|date|after_or_equal:today',
                'boost_end_date' => 'nullable|date|after:boost_start_date',
                'boost_duration_days' => 'nullable|integer|min:1|max:365'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid boost parameters',
                    $validator->errors(),
                    400
                );
            }

            $wasBoost = $property->is_boosted;
            $property->is_boosted = !$property->is_boosted;

            if ($property->is_boosted) {
                // Setting boost
                $property->boost_start_date = $request->get('boost_start_date', now());

                if ($request->has('boost_end_date')) {
                    $property->boost_end_date = $request->boost_end_date;
                } elseif ($request->has('boost_duration_days')) {
                    $property->boost_end_date = now()->addDays($request->boost_duration_days);
                }
            } else {
                // Removing boost
                $property->boost_start_date = null;
                $property->boost_end_date = null;
            }

            $property->save();

            return ApiResponse::success(
                $property->is_boosted ? 'Property boosted successfully' : 'Property boost removed',
                [
                    'id' => $property->id,
                    'is_boosted' => $property->is_boosted,
                    'boost_start_date' => $property->boost_start_date,
                    'boost_end_date' => $property->boost_end_date,
                    'boost_active' => $property->isBoosted()
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Toggle boost error', [
                'message' => $e->getMessage(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to toggle boost status',
                $e->getMessage(),
                500
            );
        }
    }
    /**
     * Add property to favorites (if you want to implement this)
     */
    public function addToFavorites($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            // Increment favorites count
            $property->increment('favorites_count');

            // Update favorites analytics
            $analytics = $property->favorites_analytics ?? [];
            $analytics['last_30_days'] = ($analytics['last_30_days'] ?? 0) + 1;
            $property->favorites_analytics = $analytics;
            $property->save();

            return ApiResponse::success(
                'Property added to favorites',
                [
                    'id' => $property->id,
                    'favorites_count' => $property->favorites_count
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Add to favorites error', [
                'message' => $e->getMessage(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to add to favorites',
                $e->getMessage(),
                500
            );
        }
    }
    public function updateStatus($id, Request $request)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:cancelled,pending,approved,available,sold,rented',
                'note' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid status parameters',
                    $validator->errors(),
                    400
                );
            }

            $oldStatus = $property->status;
            $newStatus = $request->status;

            $property->status = $newStatus;

            // Auto-adjust related fields based on status
            switch ($newStatus) {
                case 'cancelled':
                    $property->is_active = false;
                    $property->published = false;
                    break;
                case 'pending':
                    $property->published = false;
                    break;
                case 'approved':
                    $property->is_active = true;
                    $property->published = true;
                    break;
                case 'sold':
                case 'rented':
                    $property->is_active = false;
                    $property->published = false;
                    break;
                case 'available':
                    $property->is_active = true;
                    $property->published = true;
                    break;
            }

            $property->save();

            return ApiResponse::success(
                'Property status updated successfully',
                [
                    'id' => $property->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'is_active' => $property->is_active,
                    'published' => $property->published,
                    'note' => $request->get('note')
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Update status error', [
                'message' => $e->getMessage(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to update property status',
                $e->getMessage(),
                500
            );
        }
    }
    /**
     * Remove property from favorites
     */
    public function removeFromFavorites($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error(
                    'Property not found',
                    ['id' => $id],
                    404
                );
            }

            // Decrement favorites count (don't go below 0)
            if ($property->favorites_count > 0) {
                $property->decrement('favorites_count');
            }

            return ApiResponse::success(
                'Property removed from favorites',
                [
                    'id' => $property->id,
                    'favorites_count' => $property->favorites_count
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Remove from favorites error', [
                'message' => $e->getMessage(),
                'property_id' => $id
            ]);

            return ApiResponse::error(
                'Failed to remove from favorites',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Bulk update properties
     */
    public function bulkUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_ids' => 'required|array|min:1',
                'property_ids.*' => 'required|string',
                'action' => 'required|in:activate,deactivate,verify,unverify,publish,unpublish,boost,unboost',
                'boost_duration_days' => 'required_if:action,boost|nullable|integer|min:1|max:365'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid bulk update parameters',
                    $validator->errors(),
                    400
                );
            }

            $propertyIds = $request->property_ids;
            $action = $request->action;

            $updateData = [];
            switch ($action) {
                case 'activate':
                    $updateData['is_active'] = true;
                    break;
                case 'deactivate':
                    $updateData['is_active'] = false;
                    break;
                case 'verify':
                    $updateData['verified'] = true;
                    break;
                case 'unverify':
                    $updateData['verified'] = false;
                    break;
                case 'publish':
                    $updateData['published'] = true;
                    break;
                case 'unpublish':
                    $updateData['published'] = false;
                    break;
                case 'boost':
                    $updateData['is_boosted'] = true;
                    $updateData['boost_start_date'] = now();
                    if ($request->has('boost_duration_days')) {
                        $updateData['boost_end_date'] = now()->addDays($request->boost_duration_days);
                    }
                    break;
                case 'unboost':
                    $updateData['is_boosted'] = false;
                    $updateData['boost_start_date'] = null;
                    $updateData['boost_end_date'] = null;
                    break;
            }

            $updatedCount = Property::whereIn('id', $propertyIds)->update($updateData);

            return ApiResponse::success(
                "Bulk {$action} completed successfully",
                [
                    'updated_count' => $updatedCount,
                    'action' => $action,
                    'property_ids' => $propertyIds
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Bulk update error', [
                'message' => $e->getMessage(),
                'action' => $request->action ?? 'unknown'
            ]);

            return ApiResponse::error(
                'Failed to perform bulk update',
                $e->getMessage(),
                500
            );
        }
    }
    /**
     * Generate unique property ID
     */


    private function generateUniquePropertyId(): string
    {
        do {
            $propertyId = 'prop_' . date('Y_m_d') . '_' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Property::where('id', $propertyId)->exists());

        return $propertyId;
    }
    /**
     * Get current user's properties
     */
    public function getMyProperties(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 20);
            $language = $request->get('language', 'en');

            $properties = Property::where('owner_id', $user->id)
                ->where('owner_type', get_class($user))
                ->whereNotIn('status', ['cancelled', 'pending'])  // Exclude cancelled and pending
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $transformedData = collect($properties->items())->map(function ($property) use ($language) {
                return $this->transformPropertyForSearch($property, $language);
            });

            return ApiResponse::success(
                'Your properties retrieved',
                [
                    'data' => $transformedData,
                    'total' => $properties->total(),
                    'current_page' => $properties->currentPage(),
                ],
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get your properties', $e->getMessage(), 500);
        }
    }
    /**
     * Get user's draft (unpublished) properties
     */
    public function getMyDrafts(Request $request)
    {
        try {
            $user = Auth::user();

            $language = $request->get('language', 'en');

            $drafts = Property::where('owner_id', $user->id)
                ->where('owner_type', get_class($user))
                ->where('published', false)
                ->orderBy('updated_at', 'desc')
                ->get();

            $transformedData = $drafts->map(function ($property) use ($language) {
                return $this->transformPropertyForSearch($property, $language);
            });

            return ApiResponse::success(
                'Your draft properties retrieved',
                ['data' => $transformedData],
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get draft properties', $e->getMessage(), 500);
        }
    }

    /**
     * Get user's property analytics
     */
    public function getMyAnalytics(Request $request)
    {
        try {
            $user = Auth::user();

            $properties = Property::where('owner_id', $user->id)
                ->where('owner_type', get_class($user))
                ->get();

            $analytics = [
                'total_properties' => $properties->count(),
                'published_properties' => $properties->where('published', true)->count(),
                'draft_properties' => $properties->where('published', false)->count(),
                'verified_properties' => $properties->where('verified', true)->count(),
                'total_views' => $properties->sum('views'),
                'total_favorites' => $properties->sum('favorites_count'),
                'average_rating' => $properties->where('rating', '>', 0)->avg('rating'),
                'most_viewed' => $properties->sortByDesc('views')->first()?->only(['id', 'name', 'views']),
                'most_favorited' => $properties->sortByDesc('favorites_count')->first()?->only(['id', 'name', 'favorites_count']),
                'status_breakdown' => $properties->groupBy('status')->map->count(),
                'listing_type_breakdown' => $properties->groupBy('listing_type')->map->count(),
            ];

            return ApiResponse::success('Your property analytics', $analytics, 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get analytics', $e->getMessage(), 500);
        }
    }

    /**
     * Toggle publish status
     */
    public function togglePublish($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error('Property not found', ['id' => $id], 404);
            }

            $property->published = !$property->published;
            $property->save();

            return ApiResponse::success(
                $property->published ? 'Property published' : 'Property unpublished',
                [
                    'id' => $property->id,
                    'published' => $property->published
                ],
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to toggle publish status', $e->getMessage(), 500);
        }
    }

    /**
     * Get analytics overview (Admin/Agent)
     */
    public function getAnalyticsOverview(Request $request)
    {
        try {
            $timeframe = $request->get('timeframe', 'month'); // day, week, month, year

            $startDate = match ($timeframe) {
                'day' => now()->subDay(),
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
                default => now()->subMonth()
            };

            $overview = [
                'total_properties' => Property::count(),
                'new_properties' => Property::where('created_at', '>=', $startDate)->count(),
                'total_views' => Property::sum('views'),
                'new_views' => Property::where('updated_at', '>=', $startDate)->sum('views'),
                'total_favorites' => Property::sum('favorites_count'),
                'verified_properties' => Property::where('verified', true)->count(),
                'boosted_properties' => Property::where('is_boosted', true)->count(),
                'by_status' => Property::groupBy('status')->selectRaw('status, count(*) as count')->get(),
                'by_listing_type' => Property::groupBy('listing_type')->selectRaw('listing_type, count(*) as count')->get(),
                'top_viewed' => Property::orderBy('views', 'desc')->limit(5)->get(['id', 'name', 'views']),
                'recent_properties' => Property::orderBy('created_at', 'desc')->limit(5)->get(['id', 'name', 'created_at']),
            ];

            return ApiResponse::success('Analytics overview', $overview, 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get analytics overview', $e->getMessage(), 500);
        }
    }

    /**
     * Get property trends
     */
    public function getTrends(Request $request)
    {
        try {
            $period = $request->get('period', 30); // days

            $trends = [
                'property_creation_trend' => Property::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', now()->subDays($period))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),

                'views_trend' => Property::selectRaw('DATE(updated_at) as date, SUM(views) as total_views')
                    ->where('updated_at', '>=', now()->subDays($period))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),

                'average_price_trend' => Property::selectRaw('DATE(created_at) as date, AVG(JSON_EXTRACT(price, "$.usd")) as avg_price')
                    ->where('created_at', '>=', now()->subDays($period))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ];

            return ApiResponse::success('Property trends', $trends, 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get trends', $e->getMessage(), 500);
        }
    }

    /**
     * Get specific property analytics
     */
    public function getPropertyAnalytics($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error('Property not found', ['id' => $id], 404);
            }

            $analytics = [
                'basic_stats' => [
                    'views' => $property->views,
                    'favorites_count' => $property->favorites_count,
                    'rating' => $property->rating,
                    'created_at' => $property->created_at,
                ],
                'view_analytics' => $property->view_analytics ?? [],
                'favorites_analytics' => $property->favorites_analytics ?? [],
                'performance_score' => $this->calculatePerformanceScore($property),
                'recommendations' => $this->getPropertyRecommendations($property),
            ];

            return ApiResponse::success('Property analytics', $analytics, 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get property analytics', $e->getMessage(), 500);
        }
    }

    /**
     * Bulk verify properties
     */
    public function bulkVerify(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_ids' => 'required|array|min:1',
                'property_ids.*' => 'required|string',
                'verify' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $updatedCount = Property::whereIn('id', $request->property_ids)
                ->update(['verified' => $request->verify]);

            return ApiResponse::success(
                'Bulk verification completed',
                [
                    'updated_count' => $updatedCount,
                    'verified' => $request->verify
                ],
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to bulk verify', $e->getMessage(), 500);
        }
    }

    /**
     * Bulk publish/unpublish properties
     */
    public function bulkPublish(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_ids' => 'required|array|min:1',
                'property_ids.*' => 'required|string',
                'publish' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $updatedCount = Property::whereIn('id', $request->property_ids)
                ->update(['published' => $request->publish]);

            return ApiResponse::success(
                'Bulk publish operation completed',
                [
                    'updated_count' => $updatedCount,
                    'published' => $request->publish
                ],
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to bulk publish', $e->getMessage(), 500);
        }
    }

    /**
     * Bulk status update
     */
    public function bulkStatusUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_ids' => 'required|array|min:1',
                'property_ids.*' => 'required|string',
                'status' => 'required|in:available,sold,rented,pending'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $updatedCount = Property::whereIn('id', $request->property_ids)
                ->update(['status' => $request->status]);

            return ApiResponse::success(
                'Bulk status update completed',
                [
                    'updated_count' => $updatedCount,
                    'new_status' => $request->status
                ],
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to bulk update status', $e->getMessage(), 500);
        }
    }

    /**
     * Get admin dashboard data
     */
    public function getAdminDashboard()
    {
        try {
            $dashboard = [
                'overview' => [
                    'total_properties' => Property::count(),
                    'pending_verification' => Property::where('verified', false)->count(),
                    'active_properties' => Property::where('is_active', true)->count(),
                    'boosted_properties' => Property::where('is_boosted', true)->count(),
                ],
                'recent_activity' => [
                    'new_today' => Property::whereDate('created_at', today())->count(),
                    'new_this_week' => Property::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
                    'new_this_month' => Property::whereMonth('created_at', now()->month)->count(),
                ],
                'status_distribution' => Property::groupBy('status')->selectRaw('status, count(*) as count')->get(),
                'recent_properties' => Property::with('owner')->orderBy('created_at', 'desc')->limit(10)->get(),
                'top_performing' => Property::orderBy('views', 'desc')->limit(10)->get(),
                'flagged_properties' => Property::where('rating', '<', 2)->orWhere('views', '<', 5)->count(),
            ];

            return ApiResponse::success('Admin dashboard data', $dashboard, 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get dashboard data', $e->getMessage(), 500);
        }
    }

    /**
     * Get flagged properties
     */
    public function getFlaggedProperties(Request $request)
    {
        try {
            $flagged = Property::where(function ($query) {
                $query->where('rating', '<', 2)
                    ->orWhere('views', '<', 5)
                    ->orWhere('favorites_count', '<', 1);
            })
                ->where('created_at', '<', now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            return ApiResponse::success('Flagged properties', [
                'data' => $flagged->items(),
                'total' => $flagged->total(),
            ], 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get flagged properties', $e->getMessage(), 500);
        }
    }

    /**
     * Calculate property performance score
     */
    private function calculatePerformanceScore($property)
    {
        $score = 0;

        // Views score (0-30 points)
        $viewsScore = min(($property->views / 100) * 30, 30);

        // Favorites score (0-25 points)
        $favoritesScore = min(($property->favorites_count / 50) * 25, 25);

        // Rating score (0-25 points)
        $ratingScore = ($property->rating / 5) * 25;

        // Verification bonus (0-10 points)
        $verificationScore = $property->verified ? 10 : 0;

        // Age penalty (newer properties get higher scores)
        $ageDays = $property->created_at->diffInDays(now());
        $ageScore = max(10 - ($ageDays / 30), 0);

        $totalScore = $viewsScore + $favoritesScore + $ratingScore + $verificationScore + $ageScore;

        return [
            'total_score' => round($totalScore, 1),
            'breakdown' => [
                'views' => round($viewsScore, 1),
                'favorites' => round($favoritesScore, 1),
                'rating' => round($ratingScore, 1),
                'verification' => $verificationScore,
                'age' => round($ageScore, 1),
            ],
            'grade' => $this->getPerformanceGrade($totalScore)
        ];
    }

    /**
     * Get performance grade based on score
     */
    private function getPerformanceGrade($score)
    {
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }

    /**
     * Get property recommendations based on analytics
     */
    private function getPropertyRecommendations($property)
    {
        $recommendations = [];

        if ($property->views < 10) {
            $recommendations[] = 'Consider improving your property description and adding more high-quality images to increase views.';
        }

        if ($property->favorites_count < 2) {
            $recommendations[] = 'Your property might benefit from competitive pricing or highlighting unique features.';
        }

        if (!$property->verified) {
            $recommendations[] = 'Get your property verified to increase trust and visibility.';
        }

        if (empty($property->virtual_tour_url)) {
            $recommendations[] = 'Adding a virtual tour can significantly increase user engagement.';
        }

        if ($property->rating < 3 && $property->rating > 0) {
            $recommendations[] = 'Consider reviewing and improving your property listing based on user feedback.';
        }

        return $recommendations;
    }

    public function getMapProperties(Request $request)
    {
        try {
            $user = auth('sanctum')->user();

            // Log request for debugging
            Log::info('Map properties request', [
                'user_id' => $user?->id,
                'request_data' => $request->all()
            ]);

            $requestData = $request->all();
            if (isset($requestData['ignore_preferences'])) {
                $requestData['ignore_preferences'] = filter_var($requestData['ignore_preferences'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'bounds' => 'nullable|array',
                'bounds.north' => 'required_with:bounds|numeric|between:-90,90',
                'bounds.south' => 'required_with:bounds|numeric|between:-90,90',
                'bounds.east' => 'required_with:bounds|numeric|between:-180,180',
                'bounds.west' => 'required_with:bounds|numeric|between:-180,180',
                'zoom_level' => 'integer|min:1|max:20',
                'limit' => 'integer|min:1|max:500',
                'language' => 'in:en,ar,ku',
                'ignore_preferences' => 'nullable|in:true,false,1,0',
                'status' => 'nullable|in:sale,rent,sold',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'property_type' => 'nullable|string',
                'bedrooms' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    'Invalid map parameters',
                    $validator->errors(),
                    400
                );
            }

            // Extract parameters
            $bounds = $request->get('bounds');
            $zoomLevel = $request->get('zoom_level', 10);
            $limit = $request->get('limit', 200);
            $language = $request->get('language', 'en');
            $ignorePreferences = $request->boolean('ignore_preferences', false);

            // Base query - exclude cancelled and pending properties (same as index method)
            $query = Property::query()
                ->active()
                ->published()
                ->whereNotIn('status', ['cancelled', 'pending'])
                ->whereNotNull('locations');

            $baseCount = $query->count();
            Log::info('Debug - Base query count: ' . $baseCount);

            // Apply map bounds filter
            if ($bounds) {
                Log::info('Debug - Applying bounds filter', $bounds);

                // Updated bounds query to work with array structure
                $query->whereExists(function ($subQuery) use ($bounds) {
                    $subQuery->selectRaw('1')
                        ->from('properties as p2')
                        ->whereColumn('p2.id', 'properties.id')
                        ->whereRaw("JSON_LENGTH(p2.locations) > 0")
                        ->whereRaw("
                    JSON_EXTRACT(JSON_EXTRACT(p2.locations, '$[0]'), '$.lat') BETWEEN ? AND ?
                    AND JSON_EXTRACT(JSON_EXTRACT(p2.locations, '$[0]'), '$.lng') BETWEEN ? AND ?
                ", [$bounds['south'], $bounds['north'], $bounds['west'], $bounds['east']]);
                });

                $boundsCount = $query->count();
                Log::info('Properties within bounds: ' . $boundsCount);
            }

            // Apply basic filters
            $this->applyBasicMapFilters($query, $request);

            // Apply user preferences if authenticated and not ignored
            if ($user && !$ignorePreferences) {
                $userPreferences = $user->search_preferences ?? $this->getDefaultSearchPreferences();
                $this->applyUserPreferencesToMapQuery($query, $userPreferences, $user);
            }

            // Apply sorting - boosted first, then by relevance
            $this->applyMapSorting($query, $user);

            // Get properties
            $properties = $query->limit($limit)->get();
            Log::info('Retrieved properties count: ' . $properties->count());

            // Transform properties using the same method as index - this gives us the full Laravel model structure
            $transformedData = collect($properties)->map(function ($property) {
                // Use the exact same transformation as index method
                $propertyData = $this->transformPropertyData($property);

                // Extract coordinates from the locations array
                $coordinates = $this->getPropertyCoordinates($property);

                // Add coordinates to the property data for map use
                $propertyData['coordinates'] = [
                    'lat' => (float) ($coordinates['lat'] ?? 0),
                    'lng' => (float) ($coordinates['lng'] ?? 0),
                ];

                return $propertyData;
            })->filter(function ($property) {
                // Only include properties with valid coordinates
                return $property['coordinates']['lat'] != 0 && $property['coordinates']['lng'] != 0;
            });

            Log::info('Debug - Final results', [
                'total_properties_processed' => $properties->count(),
                'valid_coordinates' => $transformedData->count(),
            ]);

            // Generate clusters for lower zoom levels
            $clusters = [];
            if ($zoomLevel < 12 && $transformedData->count() > 20) {
                $clusters = $this->generateSimpleMapClusters($transformedData->toArray(), $zoomLevel);
            }

            // Generate statistics
            $statistics = $this->generateSimpleMapStatistics($transformedData);

            return ApiResponse::success(
                'Map properties retrieved successfully',
                [
                    'data' => $transformedData->values(),
                    'clusters' => $clusters,
                    'statistics' => $statistics,
                    'total' => $transformedData->count(),
                    'meta' => [
                        'bounds' => $bounds,
                        'zoom_level' => $zoomLevel,
                        'user_preferences_applied' => $user && !$ignorePreferences,
                    ]
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Map properties error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::error(
                'Failed to load map properties',
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    // Helper method for simple clustering
    private function generateSimpleMapClusters($properties, $zoomLevel)
    {
        $clusterRadius = match ($zoomLevel) {
            1, 2, 3 => 2.0,
            4, 5, 6 => 1.0,
            7, 8, 9 => 0.5,
            10, 11 => 0.1,
            default => 0.05
        };

        $clusters = [];
        $processed = [];

        foreach ($properties as $index => $property) {
            if (in_array($index, $processed)) continue;

            $cluster = [
                'center' => $property['coordinates'],
                'properties' => [$property['id']],
                'count' => 1,
            ];

            // Find nearby properties
            foreach ($properties as $otherIndex => $otherProperty) {
                if ($otherIndex === $index || in_array($otherIndex, $processed)) continue;

                $distance = $this->calculateDistance(
                    $property['coordinates']['lat'],
                    $property['coordinates']['lng'],
                    $otherProperty['coordinates']['lat'],
                    $otherProperty['coordinates']['lng']
                );

                if ($distance <= $clusterRadius) {
                    $cluster['properties'][] = $otherProperty['id'];
                    $cluster['count']++;
                    $processed[] = $otherIndex;
                }
            }

            // Only create cluster if it has multiple properties
            if ($cluster['count'] > 1) {
                $clusters[] = $cluster;
            }

            $processed[] = $index;
        }

        return $clusters;
    }

    // Helper method for simple statistics
    private function generateSimpleMapStatistics($mapData)
    {
        if ($mapData->isEmpty()) {
            return [
                'total_properties' => 0,
                'verified_count' => 0,
                'boosted_count' => 0,
            ];
        }

        return [
            'total_properties' => $mapData->count(),
            'verified_count' => $mapData->filter(function ($item) {
                return $item['verified'] ?? false;
            })->count(),
            'boosted_count' => $mapData->filter(function ($item) {
                return $item['is_boosted'] ?? false;
            })->count(),
        ];
    }
    private function applyBasicMapFilters($query, $request)
    {
        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Price filters
        if ($request->has('min_price')) {
            $query->whereRaw("CAST(JSON_EXTRACT(price, '$.amount') AS DECIMAL(15,2)) >= ?", [$request->min_price]);
        }
        if ($request->has('max_price')) {
            $query->whereRaw("CAST(JSON_EXTRACT(price, '$.amount') AS DECIMAL(15,2)) <= ?", [$request->max_price]);
        }

        // Property type filter
        if ($request->has('property_type')) {
            $query->whereRaw("JSON_EXTRACT(type, '$.category') = ?", [strtolower($request->property_type)]);
        }

        // Bedrooms filter
        if ($request->has('bedrooms')) {
            $query->whereRaw("JSON_EXTRACT(rooms, '$.bedrooms') = ?", [$request->bedrooms]);
        }
    }

    private function getOwnerForProperty($property)
    {
        $owner = null;

        if ($property->owner_id && $property->owner_type) {
            try {
                $ownerClass = $property->owner_type;
                if (class_exists($ownerClass)) {
                    $ownerModel = $ownerClass::find($property->owner_id);
                    if ($ownerModel) {
                        $owner = $this->transformOwnerInfo($ownerModel);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not load owner', [
                    'property_id' => $property->id,
                    'owner_type' => $property->owner_type,
                    'owner_id' => $property->owner_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $owner ?: [
            'id' => null,
            'name' => 'Unknown Agent',
            'type' => 'User',
            'email' => null,
            'phone' => null,
        ];
    }

    private function transformOwnerInfo($owner)
    {
        $baseInfo = [
            'id' => $owner->id,
            'name' => null,
            'type' => class_basename($owner),
            'email' => null,
            'phone' => null,
        ];

        switch (get_class($owner)) {
            case 'App\\Models\\User':
                return array_merge($baseInfo, [
                    'name' => $owner->username ?? $owner->name ?? 'User',
                    'email' => $owner->email,
                    'phone' => $owner->phone,
                ]);

            case 'App\\Models\\Agent':
                return array_merge($baseInfo, [
                    'name' => $owner->agent_name ?? $owner->name ?? 'Agent',
                    'email' => $owner->primary_email ?? $owner->email,
                    'phone' => $owner->primary_phone ?? $owner->phone,
                    'licenseNumber' => $owner->license_number,
                    'specialization' => $owner->specialization,
                ]);

            case 'App\\Models\\RealEstateOffice':
                return array_merge($baseInfo, [
                    'name' => $owner->company_name ?? $owner->name ?? 'Real Estate Office',
                    'email' => $owner->email_address ?? $owner->email,
                    'phone' => $owner->phone_number ?? $owner->phone,
                ]);

            default:
                return $baseInfo;
        }
    }
    private function mapListingTypeToFlutterStatus($listingType)
    {
        $mapping = [
            'rent' => 'Rent',
            'sell' => 'Sale',
            'sale' => 'Sale',
        ];

        return $mapping[strtolower($listingType)] ?? 'Sale';
    }
    private function checkFeature($features, $featureName)
    {
        if (!is_array($features)) {
            return false;
        }

        foreach ($features as $feature) {
            if (is_string($feature) && stripos($feature, $featureName) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build location string from address details
     */
    private function buildLocationFromAddressDetails($addressDetails, $language)
    {
        if (!is_array($addressDetails)) {
            return 'Location not available';
        }

        $locationParts = [];

        // Get neighborhood
        $neighborhood = $this->getMultiLanguageField($addressDetails['neighborhood'] ?? null, $language);
        if ($neighborhood) {
            $locationParts[] = $neighborhood;
        }

        // Get city
        $city = $this->getMultiLanguageField($addressDetails['city'] ?? null, $language);
        if ($city) {
            $locationParts[] = $city;
        }

        // Get country (optional)
        $country = $this->getMultiLanguageField($addressDetails['country'] ?? null, $language);
        if ($country && $country !== $city) {
            $locationParts[] = $country;
        }

        return !empty($locationParts) ? implode(', ', $locationParts) : 'Location not available';
    }

    /**
     * FIXED: Map backend listing_type to Flutter PropertyStatus enum
     */
    private function mapStatusToFlutter($listingType)
    {
        $statusMapping = [
            'rent' => 'rent',
            'sell' => 'sale',
            'sale' => 'sale',
            'available' => 'sale', // Default fallback
        ];

        return $statusMapping[strtolower($listingType)] ?? 'sale';
    }
    /**
     * Apply user preferences to map query
     */
    private function applyUserPreferencesToMapQuery($query, $userPreferences, $user)
    {
        $filters = $userPreferences['filters'] ?? [];

        // Apply price filter if enabled
        if ($filters['price_enabled'] ?? false) {
            if ($filters['min_price']) {
                $query->whereRaw("JSON_EXTRACT(price, '$.amount') >= ?", [$filters['min_price']]);
            }
            if ($filters['max_price']) {
                $query->whereRaw("JSON_EXTRACT(price, '$.amount') <= ?", [$filters['max_price']]);
            }
        }

        // Apply location radius if enabled and user has location
        if (($filters['location_enabled'] ?? false) && $user->lat && $user->lng) {
            $radius = $filters['location_radius'] ?? 10;
            $query->whereRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(JSON_EXTRACT(locations, '$.coordinates.lat'))) * cos(radians(JSON_EXTRACT(locations, '$.coordinates.lng')) - radians(?)) + sin(radians(?)) * sin(radians(JSON_EXTRACT(locations, '$.coordinates.lat'))))) <= ?",
                [$user->lat, $user->lng, $user->lat, $radius]
            );
        }

        // Apply property types filter
        if (!empty($filters['property_types'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['property_types'] as $type) {
                    $q->orWhereRaw("JSON_EXTRACT(type, '$.category') = ?", [strtolower($type)]);
                }
            });
        }

        // Apply bedroom filters
        if ($filters['min_bedrooms']) {
            $query->whereRaw("JSON_EXTRACT(rooms, '$.bedrooms') >= ?", [$filters['min_bedrooms']]);
        }
        if ($filters['max_bedrooms']) {
            $query->whereRaw("JSON_EXTRACT(rooms, '$.bedrooms') <= ?", [$filters['max_bedrooms']]);
        }
    }

    /**
     * Apply map sorting based on user preferences
     */
    private function applyMapSorting($query, $user)
    {
        // Always prioritize boosted properties first
        $query->orderByDesc('is_boosted');

        if ($user && isset($user->search_preferences['sorting'])) {
            $sorting = $user->search_preferences['sorting'];

            if ($sorting['price_enabled'] ?? false) {
                $direction = ($sorting['price_order'] ?? 'low_to_high') === 'low_to_high' ? 'asc' : 'desc';
                $query->orderByRaw("JSON_EXTRACT(price, '$.amount') {$direction}");
            } elseif ($sorting['popularity_enabled'] ?? false) {
                $query->orderByDesc('views')->orderByDesc('favorites_count');
            } elseif ($sorting['date_enabled'] ?? false) {
                $direction = ($sorting['date_order'] ?? 'newest') === 'newest' ? 'desc' : 'asc';
                $query->orderBy('created_at', $direction);
            } else {
                // Default fallback
                $query->orderByDesc('rating')->orderByDesc('views');
            }
        } else {
            // Default sorting for non-authenticated users
            $query->orderByDesc('rating')->orderByDesc('views');
        }
    }

    /**
     * Get property coordinates from locations object
     */
    private function getPropertyCoordinates($property)
    {
        try {
            $locations = is_array($property->locations) ? $property->locations : json_decode($property->locations, true);

            if (!$locations || empty($locations)) {
                return ['lat' => null, 'lng' => null];
            }

            // Handle array of location objects (your actual structure)
            if (is_array($locations) && isset($locations[0])) {
                $firstLocation = $locations[0];
                $lat = $firstLocation['lat'] ?? null;
                $lng = $firstLocation['lng'] ?? null;
            }
            // Handle nested coordinates structure (fallback)
            elseif (isset($locations['coordinates'])) {
                $coordinates = $locations['coordinates'];
                $lat = $coordinates['lat'] ?? null;
                $lng = $coordinates['lng'] ?? null;
            } else {
                return ['lat' => null, 'lng' => null];
            }

            // Validate coordinates
            if (!is_numeric($lat) || !is_numeric($lng)) {
                return ['lat' => null, 'lng' => null];
            }

            $lat = (float) $lat;
            $lng = (float) $lng;

            // Ensure coordinates are within valid ranges
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                return ['lat' => null, 'lng' => null];
            }

            return ['lat' => $lat, 'lng' => $lng];
        } catch (\Exception $e) {
            Log::error('Error extracting coordinates', [
                'property_id' => $property->id ?? 'unknown',
                'locations' => $property->locations ?? 'null',
                'error' => $e->getMessage()
            ]);
            return ['lat' => null, 'lng' => null];
        }
    }


    /**
     * Get property address based on language
     */
    private function getPropertyAddress($property, $language)
    {
        $addressDetails = $property->address_details ?? [];

        // Get city and neighborhood
        $city = $this->getMultiLanguageField($addressDetails['city'] ?? '', $language);
        $neighborhood = $this->getMultiLanguageField($addressDetails['neighborhood'] ?? '', $language);

        // Combine non-empty parts
        $addressParts = array_filter([$neighborhood, $city]);

        return implode(', ', $addressParts);
    }


    private function generateMapClusters($properties, $zoomLevel)
    {
        $clusterRadius = match ($zoomLevel) {
            1, 2, 3 => 2.0,
            4, 5, 6 => 1.0,
            7, 8, 9 => 0.5,
            10, 11 => 0.1,
            default => 0.05
        };

        $clusters = [];
        $processed = [];

        foreach ($properties as $index => $property) {
            if (in_array($index, $processed)) continue;

            $cluster = [
                'center' => $property['coordinates'],
                'properties' => [$property['id']],
                'count' => 1,
                'price_range' => [
                    'min' => $property['price']['amount'],
                    'max' => $property['price']['amount'],
                    'currency' => $property['price']['currency']
                ],
                'status_breakdown' => [$property['status'] => 1]
            ];

            // Find nearby properties
            foreach ($properties as $otherIndex => $otherProperty) {
                if ($otherIndex === $index || in_array($otherIndex, $processed)) continue;

                $distance = $this->calculateDistance(
                    $property['coordinates']['lat'],
                    $property['coordinates']['lng'],
                    $otherProperty['coordinates']['lat'],
                    $otherProperty['coordinates']['lng']
                );

                if ($distance <= $clusterRadius) {
                    $cluster['properties'][] = $otherProperty['id'];
                    $cluster['count']++;
                    $cluster['price_range']['min'] = min($cluster['price_range']['min'], $otherProperty['price']['amount']);
                    $cluster['price_range']['max'] = max($cluster['price_range']['max'], $otherProperty['price']['amount']);

                    // Update status breakdown
                    $status = $otherProperty['status'];
                    $cluster['status_breakdown'][$status] = ($cluster['status_breakdown'][$status] ?? 0) + 1;

                    $processed[] = $otherIndex;
                }
            }

            // Only create cluster if it has multiple properties
            if ($cluster['count'] > 1) {
                // Calculate cluster center (centroid)
                $totalLat = $totalLng = 0;
                foreach ($cluster['properties'] as $propId) {
                    $prop = collect($properties)->firstWhere('id', $propId);
                    if ($prop) {
                        $totalLat += $prop['coordinates']['lat'];
                        $totalLng += $prop['coordinates']['lng'];
                    }
                }

                $cluster['center'] = [
                    'lat' => round($totalLat / $cluster['count'], 6),
                    'lng' => round($totalLng / $cluster['count'], 6)
                ];

                $clusters[] = $cluster;
            }

            $processed[] = $index;
        }

        return $clusters;
    }
    private function applySearchFilters($query, $request)
    {
        // ==========================================
        // 1. FIXED SEARCH LOGIC (No 'location' column)
        // ==========================================
        if ($request->filled('search')) {
            $searchTerm = strtolower($request->search);

            $query->where(function ($q) use ($searchTerm) {
                // Search in Name (Multilingual)
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ku'))) LIKE ?", ["%{$searchTerm}%"])

                    // Search in Description (English)
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.en'))) LIKE ?", ["%{$searchTerm}%"])

                    // Search in Address Details: City (Multilingual)
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ar'))) LIKE ?", ["%{$searchTerm}%"])

                    // Search in Address Details: Neighborhood (Multilingual)
                    // This replaces the broken 'location' column
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.en'))) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ar'))) LIKE ?", ["%{$searchTerm}%"]);
            });
        }

        // ==========================================
        // 2. EXISTING FILTERS (Unchanged)
        // ==========================================

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Listing type
        if ($request->has('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // Price filters
        $currency = strtolower($request->get('currency', 'usd'));
        if ($request->has('min_price')) {
            $query->whereRaw("JSON_EXTRACT(price, '$.{$currency}') >= ?", [$request->min_price]);
        }
        if ($request->has('max_price')) {
            $query->whereRaw("JSON_EXTRACT(price, '$.{$currency}') <= ?", [$request->max_price]);
        }

        // Bedrooms
        if ($request->has('bedrooms')) {
            $query->whereRaw("JSON_EXTRACT(rooms, '$.bedroom.count') = ?", [$request->bedrooms]);
        }

        // Area filters
        if ($request->has('min_area')) {
            $query->where('area', '>=', $request->min_area);
        }
        if ($request->has('max_area')) {
            $query->where('area', '<=', $request->max_area);
        }

        // Property type
        if ($request->has('property_type')) {
            $type = strtolower($request->property_type);
            $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) = ?", [$type]);
        }

        // Furnished
        if ($request->has('furnished')) {
            $query->where('furnished', $request->boolean('furnished'));
        }

        // City filter (Specific)
        if ($request->has('city')) {
            $city = strtolower($request->city);
            $query->where(function ($q) use ($city) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) LIKE ?", ["%{$city}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ar'))) LIKE ?", ["%{$city}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ku'))) LIKE ?", ["%{$city}%"]);
            });
        }
    }

    private function generateMapStatistics($mapData)
    {
        if ($mapData->isEmpty()) {
            return [
                'total_properties' => 0,
                'price_range' => [
                    'min' => 0,
                    'max' => 0,
                    'average' => 0
                ],
                'status_breakdown' => [],
                'verified_count' => 0,
                'boosted_count' => 0,
            ];
        }

        // Extract prices correctly from the nested property structure
        $prices = $mapData->map(function ($item) {
            $price = $item['property']['price'] ?? 0;
            return is_numeric($price) ? (float) $price : 0;
        })->filter(function ($price) {
            return $price > 0;
        });

        // Extract statuses correctly
        $statuses = $mapData->map(function ($item) {
            return $item['property']['status'] ?? 'sale';
        })->filter(function ($status) {
            return !empty($status);
        });

        return [
            'total_properties' => $mapData->count(),
            'price_range' => [
                'min' => $prices->isNotEmpty() ? $prices->min() : 0,
                'max' => $prices->isNotEmpty() ? $prices->max() : 0,
                'average' => $prices->isNotEmpty() ? round($prices->avg(), 2) : 0
            ],
            'status_breakdown' => $statuses->groupBy(function ($status) {
                return $status;
            })->map->count()->toArray(),
            'verified_count' => $mapData->filter(function ($item) {
                return $item['property']['isVerified'] ?? false;
            })->count(),
            'boosted_count' => $mapData->filter(function ($item) {
                return $item['property']['isBoosted'] ?? false;
            })->count(),
        ];
    }


    // ZANA'S CODE FROM HERE ---------------------------------------------------------------------------------------------------------------------------------------------


    public function showList(Request $request)
    {
        $perPage = $request->get('per_page', 20);

        $properties = \App\Models\Property::where(function ($query) {
            $query->whereNotIn('status', ['cancelled', 'pending'])
                ->orWhere('owner_type', 'Agent'); // ✅ include agent posts
        })
            ->paginate($perPage);

        return view('list', [
            'properties' => $properties
        ]);
    }


    // Edit user method
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('agent.edit-agent-admin', compact('user'));
    }


    public function showUserProperties()
    {
        // 1️⃣ Check for logged-in user
        if (auth()->check()) {
            $owner = auth()->user();
        }
        // 2️⃣ Check for logged-in agent (session-based)
        elseif (session('agent_logged_in')) {
            $owner = \App\Models\Agent::find(session('agent_id'));
        } else {
            // Not logged in
            return redirect()->route('login-page');
        }

        // Fetch properties posted by this owner (user or agent)
        $properties = \App\Models\Property::where('owner_id', $owner->id)
            ->where('owner_type', get_class($owner))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('agent.agent-property-list', compact('properties'));
    }







    public function showPortfolio($property_id)
    {
        $property = Property::find($property_id);

        if (!$property) {
            return redirect()->back()->with('error', 'Property not found.');
        }

        // Decode JSON fields
        $property->images = is_string($property->images) ? json_decode($property->images, true) : $property->images;
        $property->location = is_string($property->location) ? json_decode($property->location, true) : $property->location;

        // Return the Blade view
        return view('PropertyDetail', compact('property'));
    }



    public function edit($property_id)
    {
        $property = Property::findOrFail($property_id);
        return view('agent.edit-property', compact('property'));
    }




    public function removeImage(Request $request, $property_id)
    {
        $property = Property::find($property_id);
        if (!$property) {
            return redirect()->back()->withErrors('Property not found');
        }

        $photoPath = $request->input('photo_path');
        if (!$photoPath) {
            return redirect()->back()->withErrors('No photo specified');
        }

        // Remove the photo from the property images array
        $images = is_string($property->images) ? json_decode($property->images, true) : $property->images;

        if (($key = array_search($photoPath, $images)) !== false) {
            unset($images[$key]);
            $images = array_values($images); // reindex
            $property->images = json_encode($images);
            $property->save();

            // Optionally delete the file from storage
            if (file_exists(public_path($photoPath))) {
                @unlink(public_path($photoPath));
            }
        }

        return redirect()->back()->with('success', 'Image removed successfully');
    }
    public function searchView(Request $request)
    {
        $query = $request->input('q');

        // Get properties based on search query
        $properties = Property::query();

        if ($query) {
            $properties->where(function ($q) use ($query) {
                $q->where('name->en', 'like', "%{$query}%")
                    ->orWhere('name->ar', 'like', "%{$query}%")
                    ->orWhere('name->ku', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%");
            });
        }

        $properties = $properties->paginate(12);

        // Return the LIST VIEW, not JSON
        return view('list', compact('properties'));
    }
}
