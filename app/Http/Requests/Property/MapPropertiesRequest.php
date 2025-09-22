<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class MapPropertiesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all users for now
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'zoom_level' => 'sometimes|integer|min:1|max:20',
            'limit' => 'sometimes|integer|min:1|max:1000',
            'language' => 'sometimes|string|in:en,ar,ku',
            'ignore_preferences' => 'sometimes|boolean',
            'bounds' => 'sometimes|array',
            'bounds.north' => 'required_with:bounds|numeric',
            'bounds.south' => 'required_with:bounds|numeric',
            'bounds.east' => 'required_with:bounds|numeric',
            'bounds.west' => 'required_with:bounds|numeric',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'zoom_level.integer' => 'Zoom level must be an integer between 1 and 20.',
            'limit.max' => 'Maximum limit is 1000 properties.',
            'bounds.*.numeric' => 'All boundary coordinates must be numeric values.',
            'bounds.*.required_with' => 'All boundary coordinates are required when bounds is provided.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'zoom_level' => (int) $this->zoom_level ?? 10,
            'limit' => (int) $this->limit ?? 100,
            'ignore_preferences' => $this->ignore_preferences === 'true' || $this->ignore_preferences === true,
        ]);

        // Handle bounds if they exist
        if ($this->has('bounds')) {
            $bounds = $this->bounds;
            if (is_array($bounds)) {
                $this->merge([
                    'bounds' => [
                        'north' => (float) $bounds['north'],
                        'south' => (float) $bounds['south'],
                        'east' => (float) $bounds['east'],
                        'west' => (float) $bounds['west'],
                    ]
                ]);
            }
        }
    }

    /**
     * Get validated data with defaults.
     */
    public function getValidatedData(): array
    {
        $validated = $this->validated();

        return array_merge([
            'zoom_level' => 10,
            'limit' => 100,
            'language' => 'en',
            'ignore_preferences' => false,
            'bounds' => null,
        ], $validated);
    }
}
