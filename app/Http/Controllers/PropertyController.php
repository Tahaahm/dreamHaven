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
use App\Services\PropertyInteractionService; // <--- ADD THIS
use Illuminate\Support\Facades\Cache;
use App\Models\Support\UserFavoriteProperty;
use App\Models\UserPropertyInteraction;
use Illuminate\Database\Eloquent\Builder;
use App\Services\SmartSearchEngine;
use App\Http\Controllers\Concerns\ManagesPropertyEngagement;
use App\Http\Controllers\Concerns\ManagesPropertyAnalytics;
use App\Http\Controllers\Concerns\ManagesPropertyOwnerViews;
use App\Http\Controllers\Concerns\ManagesPropertyMutations;


/**
 * Every public method here is bound to a route exactly as before this
 * refactor — method names, parameters, and response shapes are unchanged.
 * Endpoints are being split out of this single file into traits under
 * Concerns/ (e.g. ManagesPropertyEngagement below) purely for readability;
 * PHP compiles a trait's methods directly into the class that uses it, so
 * this has zero effect on routing or behavior.
 */
class PropertyController extends Controller
{
    use ManagesPropertyEngagement;
    use ManagesPropertyAnalytics;
    use ManagesPropertyOwnerViews;
    use ManagesPropertyMutations;

    protected $interactionService;

    public function __construct(PropertyInteractionService $interactionService)
    {
        $this->interactionService = $interactionService;
    }
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $user    = auth('sanctum')->user();

            $query = Property::active()
                ->published()
                ->whereIn('status', ['available', 'approved'])
                ->orderByDesc('created_at');

            if ($user) {
                $todayViewed = $this->getRecentlyViewedIds($user, 24);
                if (count($todayViewed) > 0) {
                    $query->whereNotIn('id', $todayViewed);
                }
            }

            $properties = $query->paginate($perPage);
            $properties->load('owner');

            if ($properties->isNotEmpty()) {
                $userId = $user ? $user->id : 'guest_' . session()->getId();
                dispatch(function () use ($userId, $properties) {
                    app(PropertyInteractionService::class)->trackImpressions(
                        $userId,
                        collect($properties->items()),
                        'index'
                    );
                })->afterResponse();
            }

            $transformedData = collect($properties->items())->map(
                fn($property) => $this->transformPropertyData($property)
            );

            return ApiResponse::success(
                'Properties retrieved successfully',
                [
                    'data'         => $transformedData,
                    'total'        => $properties->total(),
                    'current_page' => $properties->currentPage(),
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Index error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to retrieve properties', $e->getMessage(), 500);
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
        $__t0 = microtime(true);
        try {
            $searchTerm = $request->get('search', '');
            $user       = auth('sanctum')->user();

            // ── Detect language from query script ────────────────────────────────
            // Arabic/Kurdish Unicode block: U+0600–U+06FF
            $locale = 'en';
            if (preg_match('/[\x{0600}-\x{06FF}]/u', $searchTerm)) {
                // Differentiate Kurdish (Sorani) from Arabic by checking for
                // common Sorani-specific characters (ە، ێ، ۆ، ڵ، ڕ، ك→ک pattern)
                $locale = preg_match('/[ەێۆڵڕگکچژ]/u', $searchTerm) ? 'ku' : 'ar';
            }

            // ── Base query ────────────────────────────────────────────────────────
            $query = Property::query()
                ->active()
                ->published()
                ->whereNotIn('status', ['cancelled', 'pending']);

            // ── Smart engine ─────────────────────────────────────────────────────
            if (!empty($searchTerm)) {
                $engine = new SmartSearchEngine($searchTerm, $locale);
                $engine->apply($query);

                // Log structured intent for analytics (non-blocking)
                Log::debug('🔍 SMART SEARCH', [
                    'term'   => $searchTerm,
                    'locale' => $locale,
                    'intent' => $engine->getIntent(),
                    'user'   => $user?->id ?? 'guest',
                ]);
            }

            // ── Additional explicit filters from request params ──────────────────
            // (These supplement the natural-language engine for the filter modal)
            $this->applyExplicitFilters($query, $request);

            // ── Sorting (only if engine hasn't ordered by relevance) ─────────────
            $sort = $request->get('sort', empty($searchTerm) ? 'newest' : 'relevance');
            if ($sort !== 'relevance' || empty($searchTerm)) {
                $this->applySorting($query, $sort, $request->get('currency', 'usd'));
            }

            // ── Pagination ────────────────────────────────────────────────────────
            $perPage    = (int) $request->get('per_page', 20);
            $properties = $query->paginate($perPage);
            $properties->load('owner');

            // ── Impression tracking ───────────────────────────────────────────────
            if ($properties->isNotEmpty()) {
                $userId = $user ? $user->id : 'guest_' . session()->getId();
                $this->interactionService->trackImpressions(
                    $userId,
                    collect($properties->items()),
                    'search',
                    [
                        'search_term' => $searchTerm,
                        'locale'      => $locale,
                        'intent'      => isset($engine) ? $engine->getIntent() : [],
                    ]
                );
            }

            // ── Transform ─────────────────────────────────────────────────────────
            $transformedData = collect($properties->items())->map(
                fn($property) => $this->transformPropertyData($property)
            );

            // ── Build response meta ───────────────────────────────────────────────
            $meta = [
                'search_term'     => $searchTerm,
                'locale_detected' => $locale,
                'total'           => $properties->total(),
                'current_page'    => $properties->currentPage(),
                'per_page'        => $perPage,
            ];

            if (isset($engine)) {
                $intent = $engine->getIntent();
                $meta['parsed_intent'] = [
                    'listing_type'   => $intent['listing_type'],
                    'property_types' => $intent['property_types'],
                    'cities'         => $intent['cities'],
                    'areas'          => $intent['areas'],
                    'bedrooms'       => $intent['bedrooms'],
                    'price_range'    => [
                        'min'      => $intent['min_price'],
                        'max'      => $intent['max_price'],
                        'currency' => $intent['currency'],
                    ],
                    'features'       => $intent['features'],
                ];
            }

            Log::debug('✅ SEARCH: Success', [
                'search_term'      => $searchTerm,
                'properties_found' => $transformedData->count(),
                'duration_ms'      => round((microtime(true) - $__t0) * 1000, 1),
                'peak_memory_mb'   => round(memory_get_peak_usage(true) / 1048576, 1),
            ]);

            return ApiResponse::success('Properties found', [
                'data' => $transformedData,
                'meta' => $meta,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Search error', [
                'message' => $e->getMessage(),
                'trace'   => array_slice(
                    array_map(fn($f) => ($f['file'] ?? '?') . ':' . ($f['line'] ?? '?'), $e->getTrace()),
                    0,
                    6
                ),
            ]);
            return ApiResponse::error('Search failed', $e->getMessage(), 500);
        }
    }

    /**
     * Applies explicit request parameters from the filter modal on top of
     * what the SmartSearchEngine already resolved from the natural-language query.
     * These are the structured params Flutter sends (listing_type=rent, city=Erbil, etc.)
     */
    private function applyExplicitFilters(Builder $query, Request $request): void
    {
        // listing_type — only apply if engine didn't already lock it in
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // city — supplement/override engine-detected city
        if ($request->filled('city')) {
            $city = $request->city;
            $query->where(function ($q) use ($city) {
                $q->whereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) LIKE LOWER(?)",
                    ["%{$city}%"]
                )->orWhereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ar')) LIKE ?",
                    ["%{$city}%"]
                )->orWhereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ku')) LIKE ?",
                    ["%{$city}%"]
                );
            });
        }

        // neighborhood / area
        if ($request->filled('area') || $request->filled('neighborhood')) {
            $area = $request->get('area') ?? $request->get('neighborhood');
            $query->where(function ($q) use ($area) {
                $q->whereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.en'))) LIKE LOWER(?)",
                    ["%{$area}%"]
                )->orWhereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ar')) LIKE ?",
                    ["%{$area}%"]
                )->orWhereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ku')) LIKE ?",
                    ["%{$area}%"]
                )->orWhere('address', 'LIKE', "%{$area}%");
            });
        }

        // property_type
        // (was: whereRaw LOWER(JSON_UNQUOTE(JSON_EXTRACT(type,'$.category'))) = ? —
        // now uses the indexed, DB-generated property_type_category column added
        // by the add_indexed_generated_columns migration. Same comparison, same
        // result, but MySQL can use an index instead of scanning + parsing JSON.)
        if ($request->filled('property_type')) {
            $query->ofCategory($request->property_type);
        }

        // price (explicit params take precedence over engine-parsed ones)
        // (was: two whereRaw CAST(JSON_UNQUOTE(JSON_EXTRACT(price,'$.{currency}'))...)
        // calls per bound — now the indexed price_usd/price_iqd column via
        // Property::scopePriceBetween(), which reproduces the exact same
        // "only counts if present and > 0" guard.)
        $currency = strtolower($request->get('currency', 'usd'));
        $query->priceBetween($currency, $request->min_price, $request->max_price);

        // bedrooms
        // (was: whereRaw CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms,'$.bedroom.count'))...)
        // now uses the indexed bedrooms_count column.)
        if ($request->filled('bedrooms')) {
            $query->bedroomCount((int) $request->bedrooms, $request->boolean('bedrooms_plus'));
        }

        // bathrooms
        // (was: whereRaw CAST(JSON_UNQUOTE(JSON_EXTRACT(rooms,'$.bathroom.count'))...)
        // now uses the indexed bathrooms_count column. No "plus" option here,
        // matching the original behavior of this method exactly.)
        if ($request->filled('bathrooms')) {
            $query->bathroomCount((int) $request->bathrooms, false);
        }

        // area m²
        if ($request->filled('min_area')) {
            $query->where('area', '>=', (float)$request->min_area);
        }
        if ($request->filled('max_area')) {
            $query->where('area', '<=', (float)$request->max_area);
        }

        // features
        $featureKeys = ['has_pool', 'has_gym', 'has_garden', 'has_parking', 'has_balcony', 'has_elevator', 'has_security'];
        foreach ($featureKeys as $key) {
            if ($request->boolean($key)) {
                $feature = str_replace('has_', '', $key);
                $query->whereRaw("JSON_CONTAINS(LOWER(features), '\"" . $feature . "\"')");
            }
        }

        // furnished
        if ($request->has('furnished')) {
            $query->where('furnished', $request->boolean('furnished'));
        }

        // verified
        if ($request->boolean('verified')) {
            $query->where('verified', true);
        }
    }
    private function getDefaultSearchPreferences($user = null)
    {
        if ($user) {
            $saved = \App\Models\Support\UserSavedFilter::where('user_id', $user->id)
                ->latest()
                ->first();

            if ($saved) {
                return $saved->filters;
            }
        }

        return [
            'filters' => [
                'price_enabled'    => false,
                'location_enabled' => false,
                'property_types'   => [],
            ],
            'sorting' => [
                'price_enabled'      => false,
                'popularity_enabled' => true,
            ],
        ];
    }

    private function applyMultilingualSearchFilters($query, $request)
    {
        // 1. Keyword Search (Multilingual)
        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE LOWER(?)", ["%{$searchTerm}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar')) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ku')) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.en'))) LIKE LOWER(?)", ["%{$searchTerm}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(description, '$.ar')) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(description, '$.ku')) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) LIKE LOWER(?)", ["%{$searchTerm}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ar')) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ku')) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.en'))) LIKE LOWER(?)", ["%{$searchTerm}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ar')) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.neighborhood.ku')) LIKE ?", ["%{$searchTerm}%"])
                    ->orWhere('address', 'LIKE', "%{$searchTerm}%");
            });
        }

        // 2. Exact Match & Boolean Filters
        if ($request->has('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        if ($request->boolean('verified')) {
            $query->where('verified', true);
        }

        // 3. Range & Value Filters (Price, Area, Rooms)
        //
        // FIX: Default currency is now 'iqd' (not 'usd') because Kurdistan/Iraq
        // properties are priced in IQD. Properties with price.usd = 0 were
        // matching every USD max_price filter (0 <= any_value = true in MySQL).
        //
        // CAST + JSON_UNQUOTE handles both numeric JSON values and string-encoded
        // JSON values correctly. The > 0 guard excludes properties where the
        // requested currency field is null, missing, or zero — which happens on
        // IQD-only listings when filtering by USD and vice versa.
        // Price, bedrooms, bathrooms, and property_type now filter on the
        // indexed, DB-generated columns (price_usd/price_iqd/bedrooms_count/
        // bathrooms_count/property_type_category) added by the
        // add_indexed_generated_columns migration, instead of raw
        // JSON_EXTRACT/JSON_UNQUOTE scans. Same values, same comparisons,
        // same guards (price only counts if present and > 0) — just backed
        // by an index now.
        $currency = strtolower($request->get('currency', 'usd'));
        $query->priceBetween($currency, $request->min_price, $request->max_price);

        // Bedrooms — supports plain count and 5+ (bedrooms_plus=1)
        if ($request->has('bedrooms')) {
            $query->bedroomCount((int) $request->bedrooms, $request->boolean('bedrooms_plus'));
        }

        // Bathrooms — supports plain count and 5+
        if ($request->has('bathrooms')) {
            $query->bathroomCount((int) $request->bathrooms, $request->boolean('bathrooms_plus'));
        }

        if ($request->has('min_area')) {
            $query->where('area', '>=', (float) $request->min_area);
        }
        if ($request->has('max_area')) {
            $query->where('area', '<=', (float) $request->max_area);
        }

        if ($request->has('property_type')) {
            $query->ofCategory($request->property_type);
        }

        // 4. Utility Booleans
        if ($request->has('furnished')) {
            $query->where('furnished', $request->boolean('furnished'));
        }
        if ($request->has('electricity')) {
            $query->where('electricity', $request->boolean('electricity'));
        }
        if ($request->has('water')) {
            $query->where('water', $request->boolean('water'));
        }
        if ($request->has('internet')) {
            $query->where('internet', $request->boolean('internet'));
        }

        // 5. City Filter
        if ($request->has('city')) {
            $city = $request->city;
            $query->where(function ($q) use ($city) {
                $q->whereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en'))) LIKE LOWER(?)",
                    ["%{$city}%"]
                )
                    ->orWhereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ar')) LIKE ?",
                        ["%{$city}%"]
                    )
                    ->orWhereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ku')) LIKE ?",
                        ["%{$city}%"]
                    );
            });
        }

        // 6. JSON Array Feature Searching
        $requestedFeatures = [];
        if ($request->boolean('has_pool'))    $requestedFeatures[] = 'pool';
        if ($request->boolean('has_gym'))     $requestedFeatures[] = 'gym';
        if ($request->boolean('has_garden'))  $requestedFeatures[] = 'garden';
        if ($request->boolean('has_parking')) $requestedFeatures[] = 'parking';
        if ($request->boolean('has_balcony')) $requestedFeatures[] = 'balcony';

        foreach ($requestedFeatures as $feature) {
            $query->whereRaw("JSON_CONTAINS(LOWER(features), '\"{$feature}\"')");
        }
    }
    public function show($id)
    {
        try {
            $property = Property::where('id', $id)
                ->where('published', true)
                ->where('is_active', true)
                ->first();

            if (!$property) {
                return ApiResponse::error('Property not found', ['id' => $id], 404);
            }

            $property->increment('views');
            $user = auth('sanctum')->user();

            if ($user) {
                // Track in user_property_interactions table
                $this->interactionService->trackView($user->id, $property->id, [
                    'source'     => request()->header('X-Source', 'app'),
                    'user_agent' => request()->header('User-Agent'),
                ]);

                // ✅ FIX: Also update recently_viewed_properties JSON column on users table
                $recentlyViewed = $user->recently_viewed_properties ?? [];
                $recentlyViewed = array_filter($recentlyViewed, fn($pid) => $pid !== $property->id);
                array_unshift($recentlyViewed, $property->id);
                $recentlyViewed = array_slice(array_values($recentlyViewed), 0, 50);

                $user->update([
                    'recently_viewed_properties' => $recentlyViewed,
                    'last_activity_at'           => now(),
                ]);
            }

            return ApiResponse::success(
                'Property retrieved successfully',
                $this->transformPropertyData($property),
                200
            );
        } catch (\Exception $e) {
            Log::error('Property show error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to retrieve property', $e->getMessage(), 500);
        }
    }
    /**
     * Find nearby properties
     */
    public function nearby(Request $request)
    {
        try {
            $lat = $request->lat;
            $lng = $request->lng;
            $radius = $request->get('radius', 10);
            $limit = $request->get('limit', 10);
            $user = auth('sanctum')->user();

            $query = Property::whereRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(JSON_EXTRACT(locations, '$[0].lat'))) * cos(radians(JSON_EXTRACT(locations, '$[0].lng')) - radians(?)) + sin(radians(?)) * sin(radians(JSON_EXTRACT(locations, '$[0].lat'))))) <= ?",
                [$lat, $lng, $lat, $radius]
            )->where('is_active', true)
                ->where('published', true);


            if ($user) {
                $todayViewed = $this->getRecentlyViewedIds($user, 24);
                if (count($todayViewed) > 0) {
                    $query->whereNotIn('id', $todayViewed);
                }
            }

            $properties = $query->limit($limit)->get();

            $properties->load('owner'); // ← ADD HERE


            if ($properties->isNotEmpty()) {
                $userId = $user ? $user->id : 'guest_' . session()->getId();
                $this->interactionService->trackImpressions(
                    $userId,
                    $properties,
                    'nearby',
                    ['lat' => $lat, 'lng' => $lng, 'radius' => $radius]
                );
            }

            $transformedData = $properties->map(function ($property) use ($lat, $lng) {
                $propertyLat = $property->locations[0]['lat'] ?? 0;
                $propertyLng = $property->locations[0]['lng'] ?? 0;
                $distance = $this->calculateDistance($lat, $lng, $propertyLat, $propertyLng);

                $data = $this->transformPropertyData($property);
                $data['distance_km'] = round($distance, 2);
                return $data;
            });

            return ApiResponse::success(
                'Nearby properties found',
                [
                    'data' => $transformedData,
                    'total' => $transformedData->count(),
                    'search_center' => ['lat' => $lat, 'lng' => $lng],
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Nearby error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to find nearby properties', $e->getMessage(), 500);
        }
    }


    private function getRecentlyViewedIds($user, int $hoursBack = 24): array
    {
        if (!$user) return [];

        try {
            // Primary source: user_property_interactions table (most accurate)
            return \App\Models\UserPropertyInteraction::where('user_id', $user->id)
                ->where('interaction_type', 'view')
                ->where('created_at', '>=', now()->subHours($hoursBack))
                ->pluck('property_id')
                ->unique()
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            // Fallback: recently_viewed_properties JSON column on users table
            return $user->recently_viewed_properties ?? [];
        }
    }

    /**
     * Get property statistics
     */
    // ===== PRIVATE HELPER METHODS =====

    /**
     * Transform property data for response
     */
    private function transformPropertyData($property)
    {
        // ── Resolve owner ──────────────────────────────────────────────────────
        $ownerName    = null;
        $ownerImage   = null;
        $ownerPhone   = null;
        $ownerLanguage = null;

        try {
            $ownerClass = $property->owner_type;
            if ($ownerClass && class_exists($ownerClass)) {
                // $owner = $ownerClass::find($property->owner_id);

                $owner = $property->relationLoaded('owner') ? $property->owner : $ownerClass::find($property->owner_id);


                if ($owner) {
                    switch ($ownerClass) {
                        case 'App\\Models\\Agent':
                            $ownerName     = $owner->agent_name;
                            $ownerImage    = $owner->profile_image;
                            $ownerPhone    = $owner->primary_phone ?? $owner->whatsapp_number;
                            $ownerLanguage = $owner->language;
                            break;

                        case 'App\\Models\\RealEstateOffice':
                            $ownerName     = $owner->company_name;
                            $ownerImage    = $owner->profile_image;
                            $ownerPhone    = $owner->phone_number;
                            $ownerLanguage = $owner->language;
                            break;

                        case 'App\\Models\\User':
                            $ownerName     = $owner->username ?? $owner->name;
                            $ownerImage    = $owner->profile_image ?? $owner->avatar;
                            $ownerPhone    = $owner->phone;
                            $ownerLanguage = $owner->language;
                            break;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('transformPropertyData: could not resolve owner', [
                'property_id' => $property->id,
                'owner_type'  => $property->owner_type,
                'error'       => $e->getMessage(),
            ]);
        }

        return [
            'id'          => $property->id,
            'owner_id'    => $property->owner_id,
            'owner_type'  => $property->owner_type,

            // ── NEW owner fields ───────────────────────────────────────────
            'owner_name'     => $ownerName,
            'owner_image'    => $ownerImage,
            'owner_phone'    => $ownerPhone,
            'owner_language' => $ownerLanguage,
            // ──────────────────────────────────────────────────────────────

            'name'            => $property->name,
            // ... rest of your existing fields unchanged
            'description'     => $property->description,
            'images'          => $property->images ?? [],
            'main_image'      => isset($property->images) && is_array($property->images)
                ? ($property->images[0] ?? null) : null,
            'availability'    => $property->availability ?? [],
            'type'            => $property->type ?? [],
            'area'            => $property->area,
            'furnished'       => $property->furnished,
            'furnishing_details' => $property->furnishing_details ?? [],
            'price'           => $property->price ?? [],
            'listing_type'    => $property->listing_type,
            'rental_period'   => $property->rental_period,
            'rooms'           => $property->rooms ?? [],
            'features'        => $property->features ?? [],
            'amenities'       => $property->amenities ?? [],
            'locations'       => $property->locations ?? [],
            'address_details' => $property->address_details ?? [],
            'address'         => $property->address,
            'floor_number'    => $property->floor_number,
            'floor_details'   => $property->floor_details ?? [],
            'year_built'      => $property->year_built,
            'construction_details'  => $property->construction_details ?? [],
            'energy_rating'         => $property->energy_rating,
            'energy_details'        => $property->energy_details ?? [],
            'electricity'           => $property->electricity,
            'water'                 => $property->water,
            'internet'              => $property->internet,
            'virtual_tour_url'      => $property->virtual_tour_url,
            'virtual_tour_details'  => $property->virtual_tour_details ?? [],
            'floor_plan_url'        => $property->floor_plan_url,
            'additional_media'      => $property->additional_media ?? [],
            'verified'              => $property->verified,
            'verification_details'  => $property->verification_details ?? [],
            'is_active'             => $property->is_active,
            'published'             => $property->published,
            'status'                => $property->status,
            'views'                 => $property->views,
            'view_analytics'        => $property->view_analytics ?? [],
            'favorites_count'       => $property->favorites_count,
            'favorites_analytics'   => $property->favorites_analytics ?? [],
            'rating'                => $property->rating,
            'is_boosted'            => $property->is_boosted,
            'boost_start_date'      => $property->boost_start_date,
            'boost_end_date'        => $property->boost_end_date,
            'nearby_amenities'      => $property->nearby_amenities ?? [],
            'legal_information'     => $property->legal_information ?? [],
            'investment_analysis'   => $property->investment_analysis ?? [],
            'seo_metadata'          => $property->seo_metadata ?? [],
            'created_at'            => $property->created_at,
            'updated_at'            => $property->updated_at,
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
                'formatted_iqd' => $this->formatPrice($property->price['iqd'] ?? 0,),
                'formatted_usd' => $this->formatPrice($property->price['usd'] ?? 0,),
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

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        return sqrt(pow($lat2 - $lat1, 2) + pow($lng2 - $lng1, 2));
    }

    public function getRecommended(Request $request)
    {
        $__t0 = microtime(true);
        try {
            $limit = min((int) $request->get('limit', 20), 50); // cap at 50
            $user  = auth('sanctum')->user();

            // Diagnostic only — Log::debug so this doesn't serialize a context
            // array on every single production request (only fires when
            // LOG_LEVEL=debug). Real problems still go through Log::error below.
            Log::debug('🎯 RECOMMENDED: Request started', [
                'user_authenticated' => $user ? 'YES' : 'NO',
                'user_id'            => $user?->id,
                'limit'              => $limit,
            ]);

            // ── Stable cache key: don't vary by $limit to avoid fragmentation.
            // We always fetch 20; Flutter can take fewer from the array.
            $FETCH_LIMIT = 20;
            $authTtl     = 600; // 10 min
            $guestTtl    = 600;

            if ($user) {
                $cacheKey = "recommended_user_{$user->id}";

                $properties = Cache::remember($cacheKey, $authTtl, function () use ($user, $FETCH_LIMIT) {
                    $personalizedLimit = (int) ($FETCH_LIMIT * 0.7);

                    try {
                        $personalized = $this->interactionService->getPersonalizedRecommendations(
                            $user->id,
                            $personalizedLimit
                        );
                    } catch (\Exception $e) {
                        Log::warning('Personalized recommendations failed, using fallback', [
                            'error' => $e->getMessage(),
                        ]);
                        $personalized = collect();
                    }

                    $trendingLimit = $FETCH_LIMIT - $personalized->count();

                    $trending = Property::query()
                        ->where('is_active', true)
                        ->where('published', true)
                        ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                        ->whereNotIn('id', $personalized->pluck('id')->toArray())
                        ->with('owner')
                        ->selectRaw('properties.*,
                            (
                                (CASE WHEN DATEDIFF(NOW(), created_at) <= 7 THEN 50 ELSE 0 END) +
                                (views * 0.3) +
                                (favorites_count * 2) +
                                (CASE WHEN is_boosted = 1 THEN 30 ELSE 0 END) +
                                (CASE WHEN verified  = 1 THEN 20 ELSE 0 END)
                            ) as trending_score
                        ')
                        ->orderByDesc('trending_score')
                        ->limit($trendingLimit)
                        ->get();

                    // Load owner for personalized results
                    if ($personalized->isNotEmpty()) {
                        $personalized->load('owner');
                    }

                    return $personalized->merge($trending)->values();
                });
            } else {
                $cacheKey = "recommended_guest";

                $properties = Cache::remember($cacheKey, $guestTtl, function () use ($FETCH_LIMIT) {
                    return Property::query()
                        ->where('is_active', true)
                        ->where('published', true)
                        ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                        ->where(function ($q) {
                            $q->where('is_boosted', true)
                                ->orWhere('verified', true)
                                ->orWhere('views', '>', 50)
                                ->orWhere('favorites_count', '>', 5);
                        })
                        ->with('owner')
                        ->orderByDesc('is_boosted')
                        ->orderByDesc('verified')
                        ->orderByDesc('created_at')
                        ->limit($FETCH_LIMIT)
                        ->get();
                });
            }

            Log::debug('✅ RECOMMENDED: Success', [
                'properties_found' => $properties->count(),
                'personalized'     => $user ? true : false,
                'duration_ms'      => round((microtime(true) - $__t0) * 1000, 1),
                'peak_memory_mb'   => round(memory_get_peak_usage(true) / 1048576, 1),
            ]);

            // FIX: moved to afterResponse so it doesn't block the API response
            if ($properties->isNotEmpty()) {
                $userId = $user ? $user->id : 'guest_' . session()->getId();
                dispatch(function () use ($userId, $properties) {
                    $this->interactionService->trackImpressions(
                        $userId,
                        $properties,
                        'recommended'
                    );
                })->afterResponse();
            }

            // Slice to requested limit AFTER cache retrieval
            $sliced          = $properties->take($limit);
            $transformedData = $sliced->map(fn($p) => $this->transformPropertyData($p));

            return ApiResponse::success(
                'Recommended properties retrieved',
                [
                    'data'        => $transformedData,
                    'total'       => $transformedData->count(),
                    'personalized' => $user ? true : false,
                    'algorithm'   => $user ? 'hybrid' : 'curated',
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('❌ RECOMMENDED: Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return ApiResponse::error('Failed to get recommended properties', $e->getMessage(), 500);
        }
    }
    public function getRecent(Request $request)
    {
        $__t0 = microtime(true);
        try {
            $validator = Validator::make($request->all(), [
                'limit'    => 'integer|min:1|max:50',
                'language' => 'in:en,ar,ku',
                'days'     => 'integer|min:1|max:90',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $limit = $request->get('limit', 20);
            $days  = $request->get('days', 30);
            $user  = auth('sanctum')->user();

            Log::debug('🆕 RECENT: Request started', [
                'endpoint' => 'getRecent',
                'user_id'  => $user?->id,
                'limit'    => $limit,
                'days'     => $days,
            ]);

            $properties = Property::query()
                ->where('is_active', true)
                ->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                ->where('created_at', '>=', now()->subDays($days))
                ->orderByDesc('created_at')
                ->orderByDesc('is_boosted')
                ->limit($limit)
                ->get();

            $properties->load('owner');

            Log::debug('✅ RECENT: Success', [
                'properties_found' => $properties->count(),
                'duration_ms'      => round((microtime(true) - $__t0) * 1000, 1),
                'peak_memory_mb'   => round(memory_get_peak_usage(true) / 1048576, 1),
            ]);

            if ($properties->isNotEmpty()) {
                $userId = $user ? $user->id : 'guest_' . session()->getId();
                $this->interactionService->trackImpressions($userId, $properties, 'recent');
            }

            $transformedData = $properties->map(function ($property) {
                return $this->transformPropertyData($property);
            });

            return ApiResponse::success(
                'Recent properties retrieved',
                [
                    'data'  => $transformedData,
                    'total' => $transformedData->count(),
                    'days'  => $days,
                ],
                200
            )->header('Cache-Control', 'public, max-age=300');
            // Recent listings are identical for every user.
            // 5-min HTTP cache lets Nginx serve repeat requests without
            // touching PHP/DB at all.

        } catch (\Exception $e) {
            Log::error('❌ RECENT: Error', ['message' => $e->getMessage()]);
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
    public function getPopular(Request $request)
    {
        $__t0 = microtime(true);
        try {
            $validator = Validator::make($request->all(), [
                'limit'        => 'integer|min:1|max:50',
                'listing_type' => 'nullable|in:rent,sell',
                'city'         => 'nullable|string|max:100',
                'days'         => 'nullable|integer|min:1|max:90',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $limit       = $request->get('limit', 20);
            $listingType = $request->get('listing_type');
            $city        = $request->get('city');
            $days        = $request->get('days', 30);
            $user        = auth('sanctum')->user();

            Log::debug('🔥 POPULAR: Request started', [
                'endpoint'     => 'getPopular',
                'user_id'      => $user?->id,
                'limit'        => $limit,
                'listing_type' => $listingType,
                'city'         => $city,
                'days'         => $days,
            ]);

            // ── Infer city/listing_type from user's last filter signal ────────────
            if ($user && (!$listingType || !$city)) {
                try {
                    $filterSignal = DB::table('user_property_interactions')
                        ->where('user_id', $user->id)
                        ->where('interaction_type', 'filter_applied')
                        ->where('property_id', 'filter_signal')
                        ->where('created_at', '>=', now()->subDays(60))
                        ->latest('created_at')
                        ->value('metadata');

                    if ($filterSignal) {
                        $fs = is_array($filterSignal) ? $filterSignal : json_decode($filterSignal, true);
                        if (!$listingType && !empty($fs['listing_type'])) {
                            $listingType = $fs['listing_type'];
                        }
                        if (!$city && !empty($fs['city'])) {
                            $city = $fs['city'];
                        }
                    }
                } catch (\Throwable $e) {
                    // Non-fatal — fall back to global popular
                }
            }

            // ── Scored popular properties ─────────────────────────────────────────
            $properties = $this->interactionService->getPopularProperties(
                limit: $limit,
                listingType: $listingType,
                city: $city,
                days: $days
            );

            if ($properties->isEmpty()) {
                $properties = Property::where('is_active', true)
                    ->where('published', true)
                    ->whereNotIn('status', ['cancelled', 'pending', 'sold', 'rented'])
                    ->where(function ($q) {
                        $q->where('views', '>', 0)->orWhere('favorites_count', '>', 0);
                    })
                    ->orderByRaw('(views * 0.4) + (favorites_count * 2) + (rating * 10) DESC')
                    ->limit($limit)
                    ->get();
            }

            $properties->load('owner');

            Log::debug('✅ POPULAR: Success', [
                'properties_found' => $properties->count(),
                'listing_type'     => $listingType,
                'city'             => $city,
                'duration_ms'      => round((microtime(true) - $__t0) * 1000, 1),
                'peak_memory_mb'   => round(memory_get_peak_usage(true) / 1048576, 1),
            ]);

            if ($properties->isNotEmpty()) {
                $userId = $user ? $user->id : 'guest_' . session()->getId();
                $this->interactionService->trackImpressions($userId, $properties, 'popular');
            }

            $transformedData = $properties->map(function ($property) {
                $data = $this->transformPropertyData($property);
                $data['popularity_score']     = $property->popularity_score     ?? null;
                $data['popularity_breakdown'] = $property->popularity_breakdown ?? null;
                return $data;
            });

            return ApiResponse::success(
                'Popular properties retrieved',
                [
                    'data'      => $transformedData,
                    'total'     => $transformedData->count(),
                    'context'   => [
                        'listing_type' => $listingType,
                        'city'         => $city,
                        'days_window'  => $days,
                    ],
                    'algorithm' => 'search_ctr_weighted',
                ],
                200
            )->header('Cache-Control', 'public, max-age=300');
            // Popularity scores are recalculated every 10 min inside
            // PropertyInteractionService (Cache::remember 600s), so a 5-min
            // HTTP cache on top is safe and avoids the scoring query entirely
            // for repeat callers.

        } catch (\Exception $e) {
            Log::error('❌ POPULAR: Error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to get popular properties', $e->getMessage(), 500);
        }
    }

    /**
     * Get human-readable reason why property is featured
     */
    public function getFeatured(Request $request)
    {
        $__t0 = microtime(true);
        try {
            $limit  = $request->get('limit', 10);
            $user   = auth('sanctum')->user();
            $userId = $user?->id;

            Log::debug('⭐ FEATURED: Request started', [
                'user_id' => $userId,
                'limit'   => $limit,
            ]);

            $cacheKey = $userId
                ? "featured_user_{$userId}_{$limit}"
                : "featured_guest_{$limit}";

            // FIX: cache the FULL scored collection (including scores/reasons),
            // then reuse it directly — no second call to getFeaturedProperties().
            $featured = Cache::remember($cacheKey, 600, function () use ($limit, $userId) {
                $scored = $this->interactionService->getFeaturedProperties(
                    limit: $limit,
                    userId: $userId
                );

                // Attach scores to the Eloquent models NOW, inside the cache closure,
                // so the cached value already has all computed fields.
                $scoreLookup = $scored->keyBy('id');

                // Re-fetch as a fresh Eloquent collection so ->load() works,
                // but only ONE DB round-trip here.
                $ids        = $scored->pluck('id');
                $properties = Property::whereIn('id', $ids)->get();
                $properties->load('owner');

                return $properties->map(function ($property) use ($scoreLookup) {
                    $scored = $scoreLookup->get($property->id);
                    if ($scored) {
                        $property->featured_layer       = $scored->featured_layer       ?? 2;
                        $property->featured_reason      = $scored->featured_reason      ?? [];
                        $property->popularity_score     = $scored->popularity_score     ?? null;
                        $property->popularity_breakdown = $scored->popularity_breakdown ?? null;
                        $property->relevance_score      = $scored->relevance_score      ?? null;
                    }
                    return $property;
                })->values();
            });

            // Impression tracking: fire-and-forget, don't block response
            if ($featured->isNotEmpty()) {
                $trackUserId = $userId ?? 'guest_' . session()->getId();
                dispatch(function () use ($trackUserId, $featured) {
                    $this->interactionService->trackImpressions(
                        $trackUserId,
                        $featured,
                        'featured'
                    );
                })->afterResponse();
            }

            $transformedData = $featured->map(function ($property) {
                $data = $this->transformPropertyData($property);
                $data['featured_layer']       = $property->featured_layer       ?? 2;
                $data['featured_reason']      = $property->featured_reason      ?? [];
                $data['popularity_score']     = $property->popularity_score     ?? null;
                $data['relevance_score']      = $property->relevance_score      ?? null;
                return $data;
            });

            Log::debug('✅ FEATURED: Success', [
                'properties_found' => $transformedData->count(),
                'personalized'     => (bool) $userId,
                'layer1_count'     => $transformedData->where('featured_layer', 1)->count(),
                'layer2_count'     => $transformedData->where('featured_layer', 2)->count(),
                'duration_ms'      => round((microtime(true) - $__t0) * 1000, 1),
                'peak_memory_mb'   => round(memory_get_peak_usage(true) / 1048576, 1),
            ]);

            return ApiResponse::success(
                'Featured properties retrieved',
                [
                    'data'          => $transformedData,
                    'total'         => $transformedData->count(),
                    'personalized'  => (bool) $userId,
                    'algorithm'     => 'two_layer_contextual',
                    'layer_summary' => [
                        'layer1_boosted'    => $transformedData->where('featured_layer', 1)->count(),
                        'layer2_contextual' => $transformedData->where('featured_layer', 2)->count(),
                    ],
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('❌ FEATURED: Error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to get featured properties', $e->getMessage(), 500);
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

            $properties->load('owner'); // ← ADD HERE

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
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $limit = $request->get('limit', 20);
            $language = $request->get('language', 'en');
            $user = auth('sanctum')->user();

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

            if ($boosted->isNotEmpty()) {
                $userId = $user ? $user->id : 'guest_' . session()->getId();
                $this->interactionService->trackImpressions($userId, $boosted, 'boosted');
            }

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
            Log::error('Get boosted properties error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to get boosted properties', $e->getMessage(), 500);
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
                return ApiResponse::error('Invalid parameters', $validator->errors(), 400);
            }

            $fullOwnerType = $this->getFullOwnerType($ownerType);
            $language = $request->get('language', 'en');
            $perPage = $request->get('per_page', 20);
            $user = auth('sanctum')->user();

            $properties = Property::where('owner_type', $fullOwnerType)
                ->where('owner_id', $ownerId)
                ->where('is_active', true)
                ->where('published', true)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            if ($properties->isNotEmpty()) {
                $userId = $user ? $user->id : 'guest_' . session()->getId();
                $this->interactionService->trackImpressions(
                    $userId,
                    collect($properties->items()),
                    'owner',
                    ['owner_type' => $ownerType, 'owner_id' => $ownerId]
                );
            }

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
            return ApiResponse::error('Failed to get owner properties', $e->getMessage(), 500);
        }
    }
    /**
     * Get admin dashboard data
     */

    public function getMapProperties(Request $request)
    {
        try {
            $user   = auth('sanctum')->user();
            $bounds = $request->get('bounds');
            $limit  = $request->get('limit', 200);

            // ── Base query ────────────────────────────────────────────────────────
            $query = Property::query()
                ->active()
                ->published()
                ->whereNotIn('status', ['cancelled', 'pending'])
                ->whereNotNull('locations');

            // ── Bounds filter ─────────────────────────────────────────────────────
            // (was: a correlated whereExists subquery re-scanning `properties`
            // with a double JSON_EXTRACT per row — now a plain indexed range
            // filter on primary_lat/primary_lng, the DB-generated mirror of
            // locations[0].lat/lng added by the add_indexed_generated_columns
            // migration. Same bounding box, same matching rows: a property with
            // an empty/missing locations array has NULL primary_lat/lng, which
            // whereBetween naturally excludes — the same effect the old
            // JSON_LENGTH(locations) > 0 guard had.)
            if ($bounds) {
                $query->whereBetween('primary_lat', [$bounds['south'], $bounds['north']])
                    ->whereBetween('primary_lng', [$bounds['west'], $bounds['east']]);
            }

            $this->applyBasicMapFilters($query, $request);

            // ── Smart ordering ────────────────────────────────────────────────────
            // Best properties (boosted + verified + views + fresh) load first.
            // This matters especially at low zoom where Flutter limits pin count.
            $query->selectRaw("
            properties.*,
            (
                (CASE WHEN is_boosted = 1 THEN 40 ELSE 0 END) +
                (CASE WHEN verified   = 1 THEN 20 ELSE 0 END) +
                (LEAST(views, 100) * 0.15) +
                (LEAST(favorites_count, 50) * 0.5) +
                (CASE WHEN DATEDIFF(NOW(), created_at) <= 7  THEN 15 ELSE 0 END) +
                (CASE WHEN DATEDIFF(NOW(), created_at) <= 30 THEN  5 ELSE 0 END)
            ) as map_score
        ")->orderByDesc('map_score');

            $properties = $query->limit($limit)->get();
            $properties->load('owner');

            // ── Recommendation & popularity IDs ───────────────────────────────────
            $recommendedIds = collect();
            $popularIds     = collect();

            if ($user) {
                // Personalized recommendation IDs (IDs only — lightweight)
                try {
                    $personalized   = $this->interactionService->getPersonalizedRecommendations($user->id, 60);
                    $recommendedIds = $personalized->pluck('id');
                } catch (\Throwable $e) {
                    Log::warning('Map rec overlay non-fatal', ['error' => $e->getMessage()]);
                }

                // Popular property IDs
                try {
                    $popularIds = $this->interactionService->getPopularProperties(limit: 60)->pluck('id');
                } catch (\Throwable $e) {
                    // non-fatal
                }
            } else {
                // Guest: treat boosted + high-engagement as "popular"
                $popularIds = $properties
                    ->filter(fn($p) => $p->is_boosted || $p->views > 50 || $p->favorites_count > 5)
                    ->pluck('id');
            }

            // ── Track impressions ─────────────────────────────────────────────────
            if ($properties->isNotEmpty()) {
                $userId = $user ? $user->id : 'guest_' . session()->getId();
                $this->interactionService->trackImpressions($userId, $properties, 'map', ['bounds' => $bounds]);
            }

            // ── Transform ─────────────────────────────────────────────────────────
            $transformedData = collect($properties)->map(function ($property) use ($recommendedIds, $popularIds) {
                $data        = $this->transformPropertyData($property);
                $coordinates = $this->getPropertyCoordinates($property);

                $data['coordinates'] = [
                    'lat'     => (float) ($coordinates['lat'] ?? 0),
                    'lng'     => (float) ($coordinates['lng'] ?? 0),
                    'polygon' => $this->getPropertyPolygon($property),
                ];

                $isRec = $recommendedIds->contains($property->id);
                $isPop = $popularIds->contains($property->id);

                $data['is_recommended'] = $isRec;
                $data['is_popular']     = $isPop;
                $data['map_score']      = round($property->map_score ?? 0, 2);
                $data['map_badge']      = match (true) {
                    $isRec                                          => 'for_you',
                    $isPop                                          => 'trending',
                    (bool) $property->is_boosted                   => 'promoted',
                    (bool) $property->verified                     => 'verified',
                    $property->created_at->diffInDays(now()) <= 7  => 'new',
                    default                                        => null,
                };

                return $data;
            })->filter(fn($p) => $p['coordinates']['lat'] != 0 && $p['coordinates']['lng'] != 0);

            return ApiResponse::success(
                'Map properties retrieved successfully',
                [
                    'data'   => $transformedData->values(),
                    'total'  => $transformedData->count(),
                    'bounds' => $bounds,
                    'meta'   => [
                        'recommended_count' => $recommendedIds->count(),
                        'popular_count'     => $popularIds->count(),
                        'personalized'      => (bool) $user,
                    ],
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Map error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to load map properties', $e->getMessage(), 500);
        }
    }

    private function getPropertyPolygon(Property $property): ?array
    {
        // If you have a dedicated polygon column
        if (!empty($property->polygon)) {
            $decoded = is_string($property->polygon)
                ? json_decode($property->polygon, true)
                : $property->polygon;
            return $decoded ?? null;
        }

        // If locations has more than 1 point, use them as a polygon
        $locations = is_string($property->locations)
            ? json_decode($property->locations, true)
            : $property->locations;

        if (is_array($locations) && count($locations) > 1) {
            return array_map(
                fn($pt) => [(float) $pt['lng'], (float) $pt['lat']],
                $locations
            );
        }

        return null;
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





}
