<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helper\ApiResponse;

abstract class BaseUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Validation failed',
                $validator->errors(),
                422
            )
        );
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Unauthorized',
                'You are not authorized to make this request',
                403
            )
        );
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'phone_number' => 'phone number',
            'date_of_birth' => 'date of birth',
            'profile_picture' => 'profile picture',
        ];
    }

    /**
     * Common validation rules that can be used by child classes
     *
     * @return array<string, mixed>
     */
    protected function commonRules(): array
    {
        return [
            'email' => 'email:rfc,dns|max:255',
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'first_name' => 'string|max:100|regex:/^[a-zA-Z\s]+$/',
            'last_name' => 'string|max:100|regex:/^[a-zA-Z\s]+$/',
            'date_of_birth' => 'date|before:today|after:1900-01-01',
            'gender' => 'in:male,female,other,prefer_not_to_say',
            'latitude' => 'numeric|between:-90,90',
            'longitude' => 'numeric|between:-180,180',
            'city' => 'string|max:100',
            'state' => 'string|max:100',
            'country' => 'string|max:100',
            'postal_code' => 'string|max:20',
        ];
    }

    /**
     * Common validation messages
     *
     * @return array<string, string>
     */
    protected function commonMessages(): array
    {
        return [
            'email.email' => 'Please provide a valid email address',
            'phone.regex' => 'Please provide a valid phone number',
            'first_name.regex' => 'First name should only contain letters and spaces',
            'last_name.regex' => 'Last name should only contain letters and spaces',
            'date_of_birth.before' => 'Date of birth must be before today',
            'date_of_birth.after' => 'Please provide a valid date of birth',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.between' => 'Longitude must be between -180 and 180',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from string inputs
        $input = $this->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
                // Convert empty strings to null
                if ($value === '') {
                    $value = null;
                }
            }
        });

        $this->replace($input);

        // Normalize phone number format if present
        if ($this->has('phone') && $this->phone) {
            $this->merge([
                'phone' => preg_replace('/[^\+0-9]/', '', $this->phone)
            ]);
        }

        // Normalize email to lowercase if present
        if ($this->has('email') && $this->email) {
            $this->merge([
                'email' => strtolower($this->email)
            ]);
        }
    }

    /**
     * Get sanitized input data
     *
     * @param array|null $keys
     * @return array
     */
    public function getSanitizedInput(?array $keys = null): array
    {
        $input = $keys ? $this->only($keys) : $this->validated();

        // Remove null values
        return array_filter($input, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Check if request contains any file uploads
     *
     * @return bool
     */
    public function hasFiles(): bool
    {
        return $this->hasFile('profile_picture') ||
            $this->hasFile('documents') ||
            $this->hasFile('images');
    }

    /**
     * Get file validation rules
     *
     * @return array<string, mixed>
     */
    protected function fileRules(): array
    {
        return [
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
