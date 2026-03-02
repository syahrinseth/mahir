<?php

namespace App\Modules\Portfolio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
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
            /** The client's full name. */
            'client_name' => ['sometimes', 'string', 'max:255'],
            /** The testimonial content/review text. */
            'content' => ['sometimes', 'string'],
            /** Optional portfolio project this testimonial relates to. */
            'portfolio_id' => ['nullable', 'integer', 'exists:tenant.portfolios,id'],
            /** The client's job title or position. */
            'client_position' => ['nullable', 'string', 'max:255'],
            /** The client's company name. */
            'client_company' => ['nullable', 'string', 'max:255'],
            /** Optional star rating from 1 to 5. */
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            /** Whether this testimonial should be featured. */
            'is_featured' => ['sometimes', 'boolean'],
            /** Display order position. */
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'portfolio_id.exists' => 'The selected portfolio does not exist.',
            'rating.min' => 'The rating must be at least 1.',
            'rating.max' => 'The rating must not be greater than 5.',
        ];
    }
}
