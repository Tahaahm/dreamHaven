<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Helper\ApiResponse;

class SearchPropertiesRequest extends BaseUserRequest
{
    public function rules(): array
    {
        return [
            'query' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'override_preferences' => 'nullable|boolean',

            // Override filters
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'property_types' => 'nullable|array',
            'property_types.*' => 'string|max:50',
            'min_bedrooms' => 'nullable|integer|min:0',
            'max_bedrooms' => 'nullable|integer|min:0',
            'radius' => 'nullable|numeric|min:1|max:100',

            // Additional filters
            'furnished' => 'nullable|boolean',
            'verified_only' => 'nullable|boolean',
            'listing_type' => 'nullable|in:rent,sell',
            'sort_by' => 'nullable|in:price_asc,price_desc,date_newest,date_oldest,distance,popularity',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validatePriceRange($validator);
            $this->validateLocationData($validator);
            $this->validateBedroomRange($validator);
        });
    }

    private function validatePriceRange($validator): void
    {
        $minPrice = $this->input('min_price');
        $maxPrice = $this->input('max_price');

        if ($minPrice && $maxPrice && $minPrice >= $maxPrice) {
            $validator->errors()->add('max_price', 'Maximum price must be greater than minimum price');
        }
    }

    private function validateLocationData($validator): void
    {
        $lat = $this->input('lat');
        $lng = $this->input('lng');
        $radius = $this->input('radius');

        // If lat/lng provided, both should be provided
        if (($lat && !$lng) || (!$lat && $lng)) {
            $validator->errors()->add('coordinates', 'Both latitude and longitude must be provided together');
        }

        // If using coordinate search, radius is recommended
        if (($lat || $lng) && !$radius) {
            $validator->errors()->add('radius', 'Search radius is recommended when using coordinate-based search');
        }
    }

    private function validateBedroomRange($validator): void
    {
        $minBedrooms = $this->input('min_bedrooms');
        $maxBedrooms = $this->input('max_bedrooms');

        if ($minBedrooms !== null && $maxBedrooms !== null && $minBedrooms > $maxBedrooms) {
            $validator->errors()->add('max_bedrooms', 'Maximum bedrooms must be greater than or equal to minimum bedrooms');
        }
    }

    public function messages(): array
    {
        return [
            'per_page.max' => 'Maximum 50 properties per page allowed',
            'radius.max' => 'Maximum search radius is 100 kilometers',
            'lat.between' => 'Latitude must be between -90 and 90 degrees',
            'lng.between' => 'Longitude must be between -180 and 180 degrees',
        ];
    }
}