<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helper\ApiResponse;


class SearchPropertyRequest extends BasePropertyRequest
{
    public function rules(): array
    {
        return [
            // Pagination
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',

            // Sorting
            'sort' => 'in:price_asc,price_desc,area_asc,area_desc,newest,oldest,most_viewed,most_favorited',
            'currency' => 'in:USD,IQD',

            // Price filters
            'min_price' => 'numeric|min:0',
            'max_price' => 'numeric|min:0|gte:min_price',

            // Property characteristics
            'bedrooms' => 'integer|min:0|max:20',
            'bathrooms' => 'integer|min:0|max:20',
            'property_type' => 'string|max:50',
            'furnished' => 'boolean',

            // Area filters
            'min_area' => 'numeric|min:0',
            'max_area' => 'numeric|min:0|gte:min_area',

            // Location
            'city' => 'string|max:100',
            'neighborhood' => 'string|max:100',
            'lat' => 'numeric|between:-90,90',
            'lng' => 'numeric|between:-180,180',
            'radius' => 'integer|min:1|max:100', // km

            // Status and verification
            'status' => 'in:available,sold,rented,pending',
            'listing_type' => 'in:rent,sell',
            'verified_only' => 'boolean',
            'boosted_only' => 'boolean',

            // Features
            'with_virtual_tour' => 'boolean',
            'with_floor_plan' => 'boolean',
            'has_parking' => 'boolean',
            'has_balcony' => 'boolean',
            'has_garden' => 'boolean',

            // Utilities
            'electricity' => 'boolean',
            'water' => 'boolean',
            'internet' => 'boolean',

            // Date filters
            'created_after' => 'date',
            'created_before' => 'date|after:created_after',
            'updated_after' => 'date',

            // Language and format
            'language' => 'in:en,ar,ku',
            'include_similar' => 'boolean',
            'include_recommendations' => 'boolean',

            // Advanced filters
            'year_built_min' => 'integer|min:1900',
            'year_built_max' => 'integer|max:' . (date('Y') + 5) . '|gte:year_built_min',
            'energy_rating' => 'string|max:10',
            'floor_min' => 'integer|min:0',
            'floor_max' => 'integer|max:200|gte:floor_min',

            // Owner filters
            'owner_type' => 'in:User,Agent,RealEstateOffice',
            'owner_id' => 'string',
            'agent_verified' => 'boolean',

            // Performance filters
            'min_rating' => 'numeric|min:0|max:5',
            'min_views' => 'integer|min:0',
            'min_favorites' => 'integer|min:0',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateLocationFilters($validator);
            $this->validatePriceFilters($validator);
            $this->validateDateFilters($validator);
        });
    }

    private function validateLocationFilters($validator): void
    {
        $lat = $this->input('lat');
        $lng = $this->input('lng');
        $radius = $this->input('radius');

        // If lat/lng provided, radius should be provided too
        if (($lat || $lng) && !$radius) {
            $validator->errors()->add('radius', 'Radius is required when using coordinate-based search.');
        }

        // Both lat and lng should be provided together
        if (($lat && !$lng) || (!$lat && $lng)) {
            $validator->errors()->add('coordinates', 'Both latitude and longitude must be provided together.');
        }
    }

    private function validatePriceFilters($validator): void
    {
        $minPrice = $this->input('min_price');
        $maxPrice = $this->input('max_price');

        if ($minPrice && $maxPrice && $minPrice > $maxPrice) {
            $validator->errors()->add('max_price', 'Maximum price must be greater than minimum price.');
        }

        // Reasonable price limits (adjust based on your market)
        if ($minPrice && $minPrice < 1) {
            $validator->errors()->add('min_price', 'Minimum price seems too low.');
        }

        if ($maxPrice && $maxPrice > 10000000) {
            $validator->errors()->add('max_price', 'Maximum price seems too high.');
        }
    }

    private function validateDateFilters($validator): void
    {
        $createdAfter = $this->input('created_after');
        $createdBefore = $this->input('created_before');

        if ($createdAfter && $createdBefore) {
            if (strtotime($createdAfter) > strtotime($createdBefore)) {
                $validator->errors()->add('created_before', 'End date must be after start date.');
            }
        }
    }

    public function messages(): array
    {
        return [
            'per_page.max' => 'Maximum 100 properties per page allowed',
            'bedrooms.max' => 'Maximum 20 bedrooms filter allowed',
            'max_price.gte' => 'Maximum price must be greater than or equal to minimum price',
            'max_area.gte' => 'Maximum area must be greater than or equal to minimum area',
            'radius.max' => 'Maximum search radius is 100 kilometers',
            'lat.between' => 'Latitude must be between -90 and 90 degrees',
            'lng.between' => 'Longitude must be between -180 and 180 degrees',
            'year_built_max.gte' => 'Maximum year built must be greater than or equal to minimum year',
            'energy_rating.max' => 'Energy rating cannot exceed 10 characters',
        ];
    }

    /**
     * Get validated data with defaults
     */
    public function getValidatedWithDefaults(): array
    {
        $validated = $this->validated();

        return array_merge([
            'page' => 1,
            'per_page' => 20,
            'sort' => 'newest',
            'currency' => 'usd',
            'language' => 'en',
            'include_similar' => false,
            'include_recommendations' => false,
            'verified_only' => false,
            'boosted_only' => false,
        ], $validated);
    }
}