<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Helper\ApiResponse;

class UpdateProfileRequest extends BaseUserRequest
{
    public function rules(): array
    {
        $user = $this->user();

        return [
            'lat' => 'sometimes|numeric|between:-90,90',
            'lng' => 'sometimes|numeric|between:-180,180',
            'place' => 'sometimes|nullable|string|max:100',
            'username' => 'sometimes|string|min:3|max:50|unique:users,username,' . $user->id,
            'phone' => 'sometimes|nullable|string|min:10|max:15',
            'about_me' => 'sometimes|nullable|string|max:1000',
            'photo_image' => 'sometimes|nullable|url',
            'language' => 'sometimes|in:en,ar,ku',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'search_preferences' => 'sometimes|array',
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'This username is already taken by another user',
            'email.unique' => 'This email is already taken by another user',
            'lat.between' => 'Latitude must be between -90 and 90',
            'lng.between' => 'Longitude must be between -180 and 180',
            'photo_image.url' => 'Photo image must be a valid URL',
            'phone.min' => 'Phone number must be at least 10 characters',
        ];
    }
}