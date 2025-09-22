<?php

namespace App\Services;  // Add Property to namespace

use App\Events\Property\PropertyCreated;
use App\Events\Property\PropertyUpdated;
use App\Models\Property;
use App\Models\User;
use App\Models\Agent;
use App\Models\RealEstateOffice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PropertyService
{
    /**
     * Get active properties with pagination
     */
    public function getActiveProperties(int $perPage = 20)
    {
        return Property::active()
            ->published()
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get property with full details
     */
    public function getPropertyWithDetails(string $id): ?Property
    {
        return Property::find($id);
    }

    /**
     * Create new property
     */
    public function createProperty(array $data): Property
    {
        DB::beginTransaction();

        try {
            // Generate unique ID
            $data['id'] = $this->generateUniquePropertyId();
            $data['owner_type'] = $this->getFullOwnerType($data['owner_type']);

            // Set default values
            $data = $this->setDefaultValues($data);

            $property = Property::create($data);

            // Fire event for notifications
            event(new PropertyCreated($property));

            DB::commit();

            return $property;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update existing property
     */
    public function updateProperty(string $id, array $data): ?Property
    {
        $property = Property::find($id);

        if (!$property) {
            return null;
        }

        DB::beginTransaction();

        try {
            $property->update($data);

            // Fire event for notifications
            event(new PropertyUpdated($property, $data));

            DB::commit();

            return $property->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete property
     */
    public function deleteProperty(string $id): bool
    {
        $property = Property::find($id);

        if (!$property) {
            return false;
        }

        return $property->delete();
    }

    /**
     * Search properties with filters
     */
    public function searchProperties(array $filters): array
    {
        $query = Property::query()->active()->published();

        // Apply filters
        $this->applySearchFilters($query, $filters);

        // Apply sorting
        $this->applySorting($query, $filters['sort'] ?? 'newest', $filters['currency'] ?? 'usd');

        $perPage = $filters['per_page'] ?? 20;
        $properties = $query->paginate($perPage);

        $language = $filters['language'] ?? 'en';
        $transformedData = $properties->getCollection()->map(function ($property) use ($language) {
            return $this->transformForListing($property, $language);
        });

        return [
            'data' => $transformedData,
            'pagination' => [
                'total' => $properties->total(),
                'current_page' => $properties->currentPage(),
                'per_page' => $properties->perPage(),
                'last_page' => $properties->lastPage(),
            ]
        ];
    }

    /**
     * Get nearby properties
     */
    public function getNearbyProperties(float $lat, float $lng, int $radius, int $limit, string $language): array
    {
        $properties = Property::whereRaw(
            "(6371 * acos(cos(radians(?)) * cos(radians(JSON_EXTRACT(locations, '$[0].lat'))) * cos(radians(JSON_EXTRACT(locations, '$[0].lng')) - radians(?)) + sin(radians(?)) * sin(radians(JSON_EXTRACT(locations, '$[0].lat'))))) <= ?",
            [$lat, $lng, $lat, $radius]
        )
            ->active()
            ->published()
            ->limit($limit)
            ->get();

        $transformedData = $properties->map(function ($property) use ($language, $lat, $lng) {
            $propertyLat = $property->locations[0]['lat'] ?? 0;
            $propertyLng = $property->locations[0]['lng'] ?? 0;
            $distance = $this->calculateDistance($lat, $lng, $propertyLat, $propertyLng);

            $data = $this->transformForListing($property, $language);
            $data['distance_km'] = round($distance, 2);

            return $data;
        });

        return [
            'data' => $transformedData,
            'search_center' => ['lat' => $lat, 'lng' => $lng],
            'radius_km' => $radius,
            'total_found' => $transformedData->count()
        ];
    }

    /**
     * Get featured properties based on performance and manual selection
     */
    public function getFeaturedProperties(int $limit = 20, string $language = 'en'): array
    {
        // Featured properties logic:
        // 1. Manually marked as featured (if you add is_featured column)
        // 2. Verified properties with high performance
        // 3. Boosted properties that are currently active
        // 4. Properties with high engagement (views + favorites)

        $featured = Property::where(function ($query) {
            $query->where('is_boosted', true)
                ->where(function ($q) {
                    $q->where('boost_start_date', '<=', now())
                        ->where(function ($q2) {
                            $q2->whereNull('boost_end_date')
                                ->orWhere('boost_end_date', '>=', now());
                        });
                });
        })
            ->orWhere(function ($query) {
                // High performing verified properties
                $query->where('verified', true)
                    ->where('views', '>', 100)
                    ->where('favorites_count', '>', 10);
            })
            ->orWhere(function ($query) {
                // Recently created high-quality properties
                $query->where('verified', true)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->whereNotNull('virtual_tour_url');
            })
            ->active()
            ->published()
            ->orderByDesc('is_boosted')
            ->orderByDesc('verified')
            ->orderByDesc(DB::raw('(views + favorites_count * 2)')) // Engagement score
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $featured->map(function ($property) use ($language) {
            return $this->transformForListing($property, $language);
        })->toArray();
    }

    /**
     * Get properties for map view
     */
    public function getMapProperties(array $params): array
    {
        $bounds = $params['bounds'] ?? null;
        $zoomLevel = $params['zoom_level'] ?? 10;
        $limit = $params['limit'] ?? 200;

        $query = Property::query()
            ->active()
            ->published()
            ->whereNotIn('status', ['cancelled', 'pending'])
            ->whereNotNull('locations');

        // Apply map bounds filter
        if ($bounds) {
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
        }

        $properties = $query->orderByDesc('is_boosted')
            ->orderByDesc('verified')
            ->limit($limit)
            ->get();

        $transformedData = $properties->map(function ($property) {
            $propertyData = $this->transformForListing($property);
            $coordinates = $this->getPropertyCoordinates($property);

            $propertyData['coordinates'] = [
                'lat' => (float) ($coordinates['lat'] ?? 0),
                'lng' => (float) ($coordinates['lng'] ?? 0),
            ];

            return $propertyData;
        })->filter(function ($property) {
            return $property['coordinates']['lat'] != 0 && $property['coordinates']['lng'] != 0;
        });

        return [
            'data' => $transformedData->values(),
            'total' => $transformedData->count(),
            'meta' => [
                'bounds' => $bounds,
                'zoom_level' => $zoomLevel,
            ]
        ];
    }

    /**
     * Toggle verification status
     */
    public function toggleVerification(string $id): ?array
    {
        $property = Property::find($id);

        if (!$property) {
            return null;
        }

        $property->verified = !$property->verified;
        $property->save();

        return [
            'id' => $property->id,
            'verified' => $property->verified
        ];
    }

    /**
     * Bulk update properties
     */
    public function bulkUpdate(array $data): array
    {
        $propertyIds = $data['property_ids'];
        $action = $data['action'];

        $updateData = $this->getBulkUpdateData($action, $data);
        $updatedCount = Property::whereIn('id', $propertyIds)->update($updateData);

        return [
            'updated_count' => $updatedCount,
            'action' => $action,
            'property_ids' => $propertyIds
        ];
    }

    /**
     * Transform property for listing view
     */
    public function transformForListing(Property $property, string $language = 'en'): array
    {
        return [
            'id' => $property->id,
            'name' => $this->getMultiLanguageField($property->name, $language),
            'description' => $this->getMultiLanguageField($property->description, $language),
            'images' => $property->images ?? [],
            'main_image' => $property->images[0] ?? null,

            // Price
            'price' => [
                'iqd' => $property->price['iqd'] ?? 0,
                'usd' => $property->price['usd'] ?? 0,
                'formatted' => $this->formatPrice($property->price),
            ],
            'listing_type' => $property->listing_type,

            // Property details
            'area' => $property->area,
            'bedrooms' => $property->rooms['bedroom']['count'] ?? 0,
            'bathrooms' => $property->rooms['bathroom']['count'] ?? 0,
            'property_type' => $property->type['category'] ?? null,
            'furnished' => $property->furnished,

            // Location
            'location' => $property->locations[0] ?? null,
            'city' => $this->getMultiLanguageField($property->address_details['city'] ?? '', $language),
            'address' => $property->address,

            // Status
            'verified' => $property->verified,
            'is_active' => $property->is_active,
            'published' => $property->published,
            'status' => $property->status,

            // Analytics
            'views' => $property->views,
            'favorites_count' => $property->favorites_count,
            'rating' => $property->rating,

            // Features
            'is_boosted' => $property->is_boosted,
            'virtual_tour_url' => $property->virtual_tour_url,
            'floor_plan_url' => $property->floor_plan_url,

            // Timestamps
            'created_at' => $property->created_at,
            'updated_at' => $property->updated_at,
        ];
    }

    /**
     * Transform property for detailed view
     */
    public function transformForDetails(Property $property, string $language = 'en'): array
    {
        $basic = $this->transformForListing($property, $language);

        return array_merge($basic, [
            // Full details
            'owner' => $this->getOwnerInfo($property),
            'features' => $property->features ?? [],
            'amenities' => $property->amenities ?? [],
            'floor_number' => $property->floor_number,
            'year_built' => $property->year_built,
            'energy_rating' => $property->energy_rating,
            'utilities' => [
                'electricity' => $property->electricity,
                'water' => $property->water,
                'internet' => $property->internet,
            ],
            'nearby_amenities' => $property->nearby_amenities ?? [],
            'legal_information' => $property->legal_information ?? [],
            'investment_analysis' => $property->investment_analysis ?? [],
        ]);
    }

    // Private helper methods

    private function generateUniquePropertyId(): string
    {
        do {
            $propertyId = 'prop_' . date('Y_m_d') . '_' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Property::where('id', $propertyId)->exists());

        return $propertyId;
    }

    private function getFullOwnerType(string $shortType): string
    {
        $mapping = [
            'User' => 'App\\Models\\User',
            'Agent' => 'App\\Models\\Agent',
            'RealEstateOffice' => 'App\\Models\\RealEstateOffice'
        ];

        return $mapping[$shortType] ?? $shortType;
    }

    private function setDefaultValues(array $data): array
    {
        $defaults = [
            'verified' => false,
            'is_active' => true,
            'published' => false,
            'status' => 'available',
            'views' => 0,
            'favorites_count' => 0,
            'rating' => 0,
            'electricity' => true,
            'water' => true,
            'internet' => false,
            'is_boosted' => false,
        ];

        return array_merge($defaults, $data);
    }

    private function applySearchFilters($query, array $filters): void
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['listing_type'])) {
            $query->where('listing_type', $filters['listing_type']);
        }

        // Price filters
        $currency = strtolower($filters['currency'] ?? 'usd');
        if (isset($filters['min_price'])) {
            $query->whereRaw("JSON_EXTRACT(price, '$.{$currency}') >= ?", [$filters['min_price']]);
        }
        if (isset($filters['max_price'])) {
            $query->whereRaw("JSON_EXTRACT(price, '$.{$currency}') <= ?", [$filters['max_price']]);
        }

        // Other filters
        if (isset($filters['bedrooms'])) {
            $query->whereRaw("JSON_EXTRACT(rooms, '$.bedroom.count') = ?", [$filters['bedrooms']]);
        }

        if (isset($filters['property_type'])) {
            $query->whereRaw("JSON_EXTRACT(type, '$.category') = ?", [$filters['property_type']]);
        }

        if (isset($filters['furnished'])) {
            $query->where('furnished', $filters['furnished']);
        }

        if (isset($filters['city'])) {
            $city = $filters['city'];
            $query->where(function ($q) use ($city) {
                $q->whereRaw("JSON_EXTRACT(address_details, '$.city.en') LIKE ?", ["%{$city}%"])
                    ->orWhereRaw("JSON_EXTRACT(address_details, '$.city.ar') LIKE ?", ["%{$city}%"])
                    ->orWhereRaw("JSON_EXTRACT(address_details, '$.city.ku') LIKE ?", ["%{$city}%"]);
            });
        }
    }

    private function applySorting($query, string $sort, string $currency): void
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
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    }

    private function getBulkUpdateData(string $action, array $data): array
    {
        switch ($action) {
            case 'activate':
                return ['is_active' => true];
            case 'deactivate':
                return ['is_active' => false];
            case 'verify':
                return ['verified' => true];
            case 'unverify':
                return ['verified' => false];
            case 'publish':
                return ['published' => true];
            case 'unpublish':
                return ['published' => false];
            case 'boost':
                $updateData = ['is_boosted' => true, 'boost_start_date' => now()];
                if (isset($data['boost_duration_days'])) {
                    $updateData['boost_end_date'] = now()->addDays($data['boost_duration_days']);
                }
                return $updateData;
            case 'unboost':
                return [
                    'is_boosted' => false,
                    'boost_start_date' => null,
                    'boost_end_date' => null
                ];
            default:
                return [];
        }
    }

    private function getMultiLanguageField($field, string $language): string
    {
        if (is_string($field)) {
            return $field;
        }

        if (is_array($field)) {
            return $field[$language] ?? $field['en'] ?? $field['ar'] ?? $field['ku'] ?? '';
        }

        return '';
    }

    private function formatPrice(array $price): string
    {
        $usdPrice = $price['usd'] ?? 0;

        if ($usdPrice >= 1000000) {
            return '$' . number_format($usdPrice / 1000000, 1) . 'M';
        } elseif ($usdPrice >= 1000) {
            return '$' . number_format($usdPrice / 1000, 0) . 'K';
        }

        return '$' . number_format($usdPrice, 0);
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function getPropertyCoordinates(Property $property): array
    {
        try {
            $locations = is_array($property->locations) ? $property->locations : json_decode($property->locations, true);

            if (!$locations || empty($locations) || !isset($locations[0])) {
                return ['lat' => null, 'lng' => null];
            }

            $firstLocation = $locations[0];
            $lat = $firstLocation['lat'] ?? null;
            $lng = $firstLocation['lng'] ?? null;

            if (!is_numeric($lat) || !is_numeric($lng)) {
                return ['lat' => null, 'lng' => null];
            }

            return ['lat' => (float) $lat, 'lng' => (float) $lng];
        } catch (\Exception $e) {
            Log::error('Error extracting coordinates', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
            return ['lat' => null, 'lng' => null];
        }
    }

    private function getOwnerInfo(Property $property): array
    {
        if (!$property->owner_type || !$property->owner_id) {
            return [
                'id' => null,
                'name' => 'Unknown Agent',
                'type' => 'User',
                'email' => null,
                'phone' => null,
            ];
        }

        try {
            $ownerClass = $property->owner_type;
            if (class_exists($ownerClass)) {
                $owner = $ownerClass::find($property->owner_id);
                if ($owner) {
                    return $this->transformOwnerInfo($owner);
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

        return [
            'id' => null,
            'name' => 'Unknown Agent',
            'type' => 'User',
            'email' => null,
            'phone' => null,
        ];
    }

    private function transformOwnerInfo($owner): array
    {
        $baseInfo = [
            'id' => $owner->id,
            'name' => null,
            'type' => class_basename($owner),
            'email' => null,
            'phone' => null,
        ];

        switch (get_class($owner)) {
            case User::class:
                return array_merge($baseInfo, [
                    'name' => $owner->username ?? $owner->name ?? 'User',
                    'email' => $owner->email,
                    'phone' => $owner->phone,
                ]);

            case Agent::class:
                return array_merge($baseInfo, [
                    'name' => $owner->agent_name ?? $owner->name ?? 'Agent',
                    'email' => $owner->primary_email ?? $owner->email,
                    'phone' => $owner->primary_phone ?? $owner->phone,
                    'license_number' => $owner->license_number,
                    'specialization' => $owner->specialization,
                ]);

            case RealEstateOffice::class:
                return array_merge($baseInfo, [
                    'name' => $owner->company_name ?? $owner->name ?? 'Real Estate Office',
                    'email' => $owner->email_address ?? $owner->email,
                    'phone' => $owner->phone_number ?? $owner->phone,
                ]);

            default:
                return $baseInfo;
        }
    } // Add this method to your PropertyService classpublic function getPropertiesByOwner(array $filters, int $perPage = 20)
    public function getPropertiesByOwner(array $filters, int $perPage = 20)
    {
        Log::info('=== getPropertiesByOwner START ===');
        Log::info('Incoming filters:', $filters);
        Log::info('Per page:', ['perPage' => $perPage]);

        $query = Property::query()
            ->where('owner_id', $filters['owner_id'])
            ->where('status', 'available')
            ->where('published', true);

        Log::info('Base query built');

        // Handle owner_type - convert to full model class name
        if (!empty($filters['owner_type'])) {
            $ownerType = match ($filters['owner_type']) {
                'User' => 'App\\Models\\User',
                'Agent' => 'App\\Models\\Agent',
                'RealEstateOffice' => 'App\\Models\\RealEstateOffice',
                default => $filters['owner_type']
            };

            Log::info('Owner type conversion:', [
                'original' => $filters['owner_type'],
                'converted' => $ownerType
            ]);

            $query->where('owner_type', $ownerType);
            Log::info('Added owner_type filter');
        }

        // Simple additional filters
        if (!empty($filters['listing_type'])) {
            $query->where('listing_type', $filters['listing_type']);
            Log::info('Added listing_type filter:', ['listing_type' => $filters['listing_type']]);
        }

        if (isset($filters['furnished'])) {
            $query->where('furnished', $filters['furnished']);
            Log::info('Added furnished filter:', ['furnished' => $filters['furnished']]);
        }

        $query->orderBy('created_at', 'desc');
        Log::info('Added sorting');

        // Log the final SQL and bindings
        Log::info('Final SQL query:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        // Execute and log results
        $result = $query->paginate($perPage);

        Log::info('Query results:', [
            'total' => $result->total(),
            'current_page' => $result->currentPage(),
            'per_page' => $result->perPage(),
            'count' => $result->count()
        ]);

        if ($result->count() > 0) {
            Log::info('First result sample:', [
                'first_property' => $result->first()->toArray()
            ]);
        } else {
            Log::warning('No properties found - checking what exists in database');

            // Debug: Check if any properties exist for this owner_id
            $ownerCheck = Property::where('owner_id', $filters['owner_id'])->count();
            Log::info('Properties with owner_id check:', ['count' => $ownerCheck]);

            // Debug: Check owner_type values
            $ownerTypeCheck = Property::where('owner_id', $filters['owner_id'])
                ->select('owner_type')
                ->distinct()
                ->pluck('owner_type');
            Log::info('Available owner_type values for owner:', ['owner_types' => $ownerTypeCheck->toArray()]);

            // Debug: Check different status values
            $statusCheck = Property::where('owner_id', $filters['owner_id'])
                ->select('status')
                ->distinct()
                ->pluck('status');
            Log::info('Available status values for owner:', ['statuses' => $statusCheck->toArray()]);

            // Debug: Check published values
            $publishedCheck = Property::where('owner_id', $filters['owner_id'])
                ->select('published')
                ->distinct()
                ->pluck('published');
            Log::info('Available published values for owner:', ['published' => $publishedCheck->toArray()]);
        }

        Log::info('=== getPropertiesByOwner END ===');

        return $result;
    }
}
