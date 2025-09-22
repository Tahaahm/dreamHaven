<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helper\ApiResponse;

abstract class BasePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Validation failed',
                $validator->errors(),
                422
            )
        );
    }
}

// ============================================
// Store Property Request
// ============================================

class StorePropertyRequest extends BasePropertyRequest
{
    public function rules(): array
    {
        return [
            // Owner information
            'owner_id' => 'required|string',
            'owner_type' => 'required|in:User,Agent,RealEstateOffice',

            // Multi-language fields
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'name.ku' => 'nullable|string|max:255',

            'description' => 'required|array',
            'description.en' => 'required|string|min:10',
            'description.ar' => 'nullable|string',
            'description.ku' => 'nullable|string',

            // Media
            'images' => 'required|array|min:1',
            'images.*' => 'required|url',
            'virtual_tour_url' => 'nullable|url',
            'floor_plan_url' => 'nullable|url',

            // Property details
            'type' => 'required|array',
            'type.category' => 'required|string|min:2',
            'area' => 'required|numeric|min:1',
            'furnished' => 'required|boolean',

            // Price and listing type
            'price' => 'required|array',
            'price.iqd' => 'required|numeric|min:1',
            'price.usd' => 'required|numeric|min:1',
            'listing_type' => 'required|in:rent,sell',
            'rental_period' => 'required_if:listing_type,rent|nullable|in:monthly,yearly',

            // Rooms
            'rooms' => 'required|array',
            'rooms.bedroom' => 'required|array',
            'rooms.bedroom.count' => 'required|integer|min:0|max:50',
            'rooms.bathroom' => 'required|array',
            'rooms.bathroom.count' => 'required|integer|min:0|max:50',

            // Features and amenities
            'features' => 'nullable|array',
            'features.*' => 'string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',

            // Location
            'locations' => 'required|array|min:1',
            'locations.*.lat' => 'required|numeric|between:-90,90',
            'locations.*.lng' => 'required|numeric|between:-180,180',
            'locations.*.type' => 'required|string',

            'address_details' => 'required|array',
            'address_details.city' => 'required|array',
            'address_details.city.en' => 'required|string|min:2',
            'address' => 'nullable|string|max:500',

            // Utilities
            'electricity' => 'sometimes|boolean',
            'water' => 'sometimes|boolean',
            'internet' => 'sometimes|boolean',

            // Status fields
            'published' => 'sometimes|boolean',
            'status' => 'sometimes|in:available,sold,rented,pending',

            // Optional building details
            'floor_number' => 'nullable|integer|min:0|max:200',
            'year_built' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'energy_rating' => 'nullable|string|max:10',

            // Optional complex fields
            'furnishing_details' => 'nullable|array',
            'legal_information' => 'nullable|array',
            'nearby_amenities' => 'nullable|array',
            'construction_details' => 'nullable|array',
            'energy_details' => 'nullable|array',
            'floor_details' => 'nullable|array',
            'virtual_tour_details' => 'nullable|array',
            'additional_media' => 'nullable|array',
            'investment_analysis' => 'nullable|array',
            'seo_metadata' => 'nullable|array',

            // Promotion fields
            'is_boosted' => 'sometimes|boolean',
            'boost_start_date' => 'nullable|date|after_or_equal:today',
            'boost_end_date' => 'nullable|date|after:boost_start_date',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateOwnerExists($validator);
            $this->validatePriceConsistency($validator);
            $this->validateLocationData($validator);
        });
    }

    private function validateOwnerExists($validator): void
    {
        $ownerType = $this->input('owner_type');
        $ownerId = $this->input('owner_id');

        if ($ownerType && $ownerId) {
            $fullOwnerType = $this->getFullOwnerType($ownerType);
            if (class_exists($fullOwnerType)) {
                $exists = $fullOwnerType::where('id', $ownerId)->exists();
                if (!$exists) {
                    $validator->errors()->add('owner_id', 'The selected owner does not exist.');
                }
            }
        }
    }

    private function validatePriceConsistency($validator): void
    {
        $price = $this->input('price', []);
        $iqd = $price['iqd'] ?? 0;
        $usd = $price['usd'] ?? 0;

        // Basic exchange rate validation (adjust based on current rates)
        if ($iqd > 0 && $usd > 0) {
            $exchangeRate = $iqd / $usd;
            if ($exchangeRate < 1200 || $exchangeRate > 1700) {
                $validator->errors()->add('price', 'Price conversion seems incorrect. Please verify IQD/USD rates.');
            }
        }
    }

    private function validateLocationData($validator): void
    {
        $locations = $this->input('locations', []);

        foreach ($locations as $index => $location) {
            if (!isset($location['lat']) || !isset($location['lng'])) {
                $validator->errors()->add("locations.{$index}", 'Each location must have latitude and longitude.');
                continue;
            }

            // Validate coordinates are within Iraq bounds (rough validation)
            $lat = $location['lat'];
            $lng = $location['lng'];

            if ($lat < 29.0 || $lat > 37.5 || $lng < 38.0 || $lng > 49.0) {
                $validator->errors()->add("locations.{$index}", 'Coordinates appear to be outside Iraq. Please verify location.');
            }
        }
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

    public function messages(): array
    {
        return [
            'name.en.required' => 'Property name in English is required',
            'description.en.required' => 'Property description in English is required',
            'description.en.min' => 'Property description must be at least 10 characters long',
            'images.required' => 'At least one property image is required',
            'images.min' => 'At least one property image is required',
            'locations.required' => 'Property location is required',
            'locations.*.lat.between' => 'Latitude must be between -90 and 90',
            'locations.*.lng.between' => 'Longitude must be between -180 and 180',
            'price.iqd.required' => 'Price in IQD is required',
            'price.usd.required' => 'Price in USD is required',
            'area.min' => 'Property area must be at least 1 square meter',
            'rooms.bedroom.count.max' => 'Maximum 50 bedrooms allowed',
            'rooms.bathroom.count.max' => 'Maximum 50 bathrooms allowed',
        ];
    }
}
