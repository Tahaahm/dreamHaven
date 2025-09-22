<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helper\ApiResponse;


class UpdatePropertyRequest extends BasePropertyRequest
{
    public function rules(): array
    {
        return [
            // Multi-language fields (all optional for updates)
            'name' => 'sometimes|array',
            'name.en' => 'sometimes|string|max:255',
            'name.ar' => 'sometimes|string|max:255',
            'name.ku' => 'sometimes|string|max:255',

            'description' => 'sometimes|array',
            'description.en' => 'sometimes|string|min:10',
            'description.ar' => 'sometimes|string',
            'description.ku' => 'sometimes|string',

            // Media
            'images' => 'sometimes|array',
            'images.*' => 'url',
            'virtual_tour_url' => 'sometimes|nullable|url',
            'floor_plan_url' => 'sometimes|nullable|url',

            // Property details
            'type' => 'sometimes|array',
            'area' => 'sometimes|numeric|min:1',
            'furnished' => 'sometimes|boolean',

            // Price and listing
            'price' => 'sometimes|array',
            'price.iqd' => 'sometimes|numeric|min:1',
            'price.usd' => 'sometimes|numeric|min:1',
            'listing_type' => 'sometimes|in:rent,sell',
            'rental_period' => 'sometimes|nullable|in:monthly,yearly',

            // Structure
            'rooms' => 'sometimes|array',
            'rooms.bedroom.count' => 'sometimes|integer|min:0|max:50',
            'rooms.bathroom.count' => 'sometimes|integer|min:0|max:50',
            'features' => 'sometimes|array',
            'amenities' => 'sometimes|array',

            // Location
            'locations' => 'sometimes|array',
            'locations.*.lat' => 'sometimes|numeric|between:-90,90',
            'locations.*.lng' => 'sometimes|numeric|between:-180,180',
            'address_details' => 'sometimes|array',
            'address' => 'sometimes|string|max:500',

            // Utilities
            'electricity' => 'sometimes|boolean',
            'water' => 'sometimes|boolean',
            'internet' => 'sometimes|boolean',

            // Status
            'published' => 'sometimes|boolean',
            'status' => 'sometimes|in:available,sold,rented,pending',
            'verified' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',

            // Building details
            'floor_number' => 'sometimes|nullable|integer|min:0|max:200',
            'year_built' => 'sometimes|nullable|integer|min:1900|max:' . (date('Y') + 5),
            'energy_rating' => 'sometimes|nullable|string|max:10',

            // Promotion
            'is_boosted' => 'sometimes|boolean',
            'boost_start_date' => 'sometimes|nullable|date',
            'boost_end_date' => 'sometimes|nullable|date|after:boost_start_date',

            // Optional complex fields
            'furnishing_details' => 'sometimes|array',
            'legal_information' => 'sometimes|array',
            'nearby_amenities' => 'sometimes|array',
            'construction_details' => 'sometimes|array',
            'energy_details' => 'sometimes|array',
            'floor_details' => 'sometimes|array',
            'virtual_tour_details' => 'sometimes|array',
            'additional_media' => 'sometimes|array',
            'investment_analysis' => 'sometimes|array',
            'seo_metadata' => 'sometimes|array',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('price')) {
                $this->validatePriceConsistency($validator);
            }

            if ($this->has('locations')) {
                $this->validateLocationData($validator);
            }
        });
    }

    private function validatePriceConsistency($validator): void
    {
        $price = $this->input('price', []);
        if (isset($price['iqd']) && isset($price['usd'])) {
            $iqd = $price['iqd'];
            $usd = $price['usd'];

            if ($iqd > 0 && $usd > 0) {
                $exchangeRate = $iqd / $usd;
                if ($exchangeRate < 1200 || $exchangeRate > 1700) {
                    $validator->errors()->add('price', 'Price conversion seems incorrect. Please verify IQD/USD rates.');
                }
            }
        }
    }

    private function validateLocationData($validator): void
    {
        $locations = $this->input('locations', []);

        foreach ($locations as $index => $location) {
            if (isset($location['lat']) && isset($location['lng'])) {
                $lat = $location['lat'];
                $lng = $location['lng'];

                if ($lat < 29.0 || $lat > 37.5 || $lng < 38.0 || $lng > 49.0) {
                    $validator->errors()->add("locations.{$index}", 'Coordinates appear to be outside Iraq. Please verify location.');
                }
            }
        }
    }
}
