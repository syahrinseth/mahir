<?php

namespace App\Modules\Tenancy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
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
        $tenantId = $this->route('tenant');

        return [
            /** The display name of the tenant. */
            'name' => ['sometimes', 'string', 'max:255'],
            /** A URL-friendly identifier for the tenant. Must be unique and contain only alphanumeric characters, dashes, and underscores. */
            'slug' => ['sometimes', 'string', 'max:255', "unique:landlord.tenants,slug,{$tenantId}", 'alpha_dash'],
            /** The subdomain for the tenant. Must be unique across all tenants. */
            'domain' => ['sometimes', 'string', 'max:255', "unique:landlord.tenants,domain,{$tenantId}"],
            /** Whether the tenant is currently active. */
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.unique' => 'This slug is already in use.',
            'domain.unique' => 'This domain is already in use.',
        ];
    }
}
