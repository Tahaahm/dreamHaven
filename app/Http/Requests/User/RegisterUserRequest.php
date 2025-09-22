<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Helper\ApiResponse;

abstract class BaseUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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

class RegisterUserRequest extends BaseUserRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|string|min:3|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'phone' => 'nullable|string|min:10|max:15',
            'place' => 'nullable|string|max:100',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'about_me' => 'nullable|string|max:1000',
            'photo_image' => 'nullable|url',
            'language' => 'in:en,ar,ku',
            'device_name' => 'nullable|string|max:255',
            'device_token' => 'nullable|string|max:500',
            'search_preferences' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'This username is already taken',
            'email.unique' => 'This email is already registered',
            'password.confirmed' => 'Password confirmation does not match',
            'lat.between' => 'Latitude must be between -90 and 90',
            'lng.between' => 'Longitude must be between -180 and 180',
            'photo_image.url' => 'Photo image must be a valid URL',
        ];
    }
}