<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Helper\ApiResponse;

class UpdateSearchPreferencesRequest extends BaseUserRequest
{
    public function rules(): array
    {
        return [
            'search_preferences' => 'required|array',
            'search_preferences.filters' => 'required|array',
            'search_preferences.filters.price_enabled' => 'required|boolean',
            'search_preferences.filters.min_price' => 'nullable|numeric|min:0',
            'search_preferences.filters.max_price' => 'nullable|numeric|min:0',
            'search_preferences.filters.location_enabled' => 'required|boolean',
            'search_preferences.filters.location_radius' => 'nullable|numeric|min:1|max:100',
            'search_preferences.filters.property_types' => 'nullable|array',
            'search_preferences.filters.property_types.*' => 'string|max:50',
            'search_preferences.filters.min_bedrooms' => 'nullable|integer|min:0|max:10',
            'search_preferences.filters.max_bedrooms' => 'nullable|integer|min:0|max:10',

            'search_preferences.sorting' => 'required|array',
            'search_preferences.sorting.price_enabled' => 'required|boolean',
            'search_preferences.sorting.price_order' => 'nullable|in:low_to_high,high_to_low',
            'search_preferences.sorting.popularity_enabled' => 'required|boolean',
            'search_preferences.sorting.date_enabled' => 'required|boolean',
            'search_preferences.sorting.date_order' => 'nullable|in:newest,oldest',
            'search_preferences.sorting.distance_enabled' => 'required|boolean',

            'search_preferences.behavior' => 'required|array',
            'search_preferences.behavior.enable_notifications' => 'required|boolean',
            'search_preferences.behavior.save_search_history' => 'required|boolean',
            'search_preferences.behavior.auto_suggestions' => 'required|boolean',
            'search_preferences.behavior.recent_searches' => 'required|boolean',
            'search_preferences.behavior.max_history_items' => 'nullable|integer|min:10|max:200',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validatePriceRange($validator);
            $this->validateBedroomRange($validator);
        });
    }

    private function validatePriceRange($validator): void
    {
        $preferences = $this->input('search_preferences');

        if ($preferences['filters']['price_enabled'] ?? false) {
            $minPrice = $preferences['filters']['min_price'] ?? 0;
            $maxPrice = $preferences['filters']['max_price'] ?? 0;

            if ($minPrice > 0 && $maxPrice > 0 && $minPrice >= $maxPrice) {
                $validator->errors()->add('search_preferences.filters.min_price', 'Minimum price must be less than maximum price');
            }
        }
    }

    private function validateBedroomRange($validator): void
    {
        $preferences = $this->input('search_preferences');

        $minBedrooms = $preferences['filters']['min_bedrooms'] ?? null;
        $maxBedrooms = $preferences['filters']['max_bedrooms'] ?? null;

        if ($minBedrooms !== null && $maxBedrooms !== null && $minBedrooms > $maxBedrooms) {
            $validator->errors()->add('search_preferences.filters.min_bedrooms', 'Minimum bedrooms must be less than or equal to maximum bedrooms');
        }
    }

    public function messages(): array
    {
        return [
            'search_preferences.filters.location_radius.max' => 'Location radius cannot exceed 100 kilometers',
            'search_preferences.filters.max_bedrooms.max' => 'Maximum bedrooms cannot exceed 10',
            'search_preferences.behavior.max_history_items.max' => 'Maximum history items cannot exceed 200',
            'search_preferences.behavior.max_history_items.min' => 'Maximum history items must be at least 10',
        ];
    }
}