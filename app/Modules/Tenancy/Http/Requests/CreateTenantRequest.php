<?php

namespace App\Modules\Tenancy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            /** The display name of the tenant. */
            'name' => ['required', 'string', 'max:255'],
            /** A URL-friendly identifier for the tenant. Must be unique and contain only alphanumeric characters, dashes, and underscores. */
            'slug' => ['required', 'string', 'max:255', 'unique:landlord.tenants,slug', 'alpha_dash'],
            /** The subdomain for the tenant. Must be unique across all tenants. */
            'domain' => ['required', 'string', 'max:255', 'unique:landlord.tenants,domain'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tenant name is required.',
            'slug.required' => 'Tenant slug is required.',
            'slug.unique' => 'This slug is already in use.',
            'domain.required' => 'Tenant domain is required.',
            'domain.unique' => 'This domain is already in use.',
        ];
    }
}
