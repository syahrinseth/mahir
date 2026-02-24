<?php

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            /** The full name of the user. */
            'name' => ['required', 'string', 'max:255'],
            /** The user's email address. Must be unique within the tenant. */
            'email' => ['required', 'string', 'email', 'max:255', 'unique:tenant.users,email'],
            /** The user's password. Must be confirmed via password_confirmation field. */
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            /** The name of the device requesting the token. Defaults to "default" if not provided. */
            'device_name' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
