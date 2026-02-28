<?php

namespace App\Modules\Article\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeriesRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            /** URL-friendly slug derived from the title. */
            'slug' => ['sometimes', 'string', 'max:255'],
            /** A short description of the series. */
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }
}
