<?php

namespace App\Modules\Article\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSeriesRequest extends FormRequest
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
            /** The series title. */
            'title' => ['required', 'string', 'max:255'],
            /** URL-friendly slug derived from the title. Must be unique. */
            'slug' => ['required', 'string', 'max:255', Rule::unique('article_series', 'slug')],
            /** A short description of the series. */
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A series title is required.',
            'slug.required' => 'A slug is required.',
            'slug.unique' => 'This slug is already in use.',
        ];
    }
}
