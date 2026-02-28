<?php

namespace App\Modules\Portfolio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePortfolioCategoryRequest extends FormRequest
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
            /** The category name. */
            'name' => ['required', 'string', 'max:255'],
            /** URL-friendly slug. Must be unique within the tenant. */
            'slug' => ['required', 'string', 'max:255'],
            /** An optional description for this category. */
            'description' => ['nullable', 'string', 'max:500'],
            /** The display order for sorting. */
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'A category name is required.',
            'slug.required' => 'A slug is required.',
        ];
    }
}
