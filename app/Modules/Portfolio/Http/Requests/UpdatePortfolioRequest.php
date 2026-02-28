<?php

namespace App\Modules\Portfolio\Http\Requests;

use App\Modules\Portfolio\Enums\PortfolioStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePortfolioRequest extends FormRequest
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
            /** The portfolio title. */
            'title' => ['sometimes', 'string', 'max:255'],
            /** Custom slug. */
            'slug' => ['sometimes', 'string', 'max:255'],
            /** Rich text description or case study. */
            'description' => ['sometimes', 'string'],
            /** Optional category to group the portfolio item. */
            'category_id' => ['nullable', 'integer'],
            /** Name of the client this project was for. */
            'client_name' => ['nullable', 'string', 'max:255'],
            /** External URL to the live project. */
            'project_url' => ['nullable', 'url', 'max:2048'],
            /** Featured image URL or path. */
            'featured_image' => ['nullable', 'string', 'max:2048'],
            /** List of technologies used in this project. */
            'technologies' => ['nullable', 'array'],
            /** Each technology name. */
            'technologies.*' => ['string', 'max:100'],
            /** Portfolio status. */
            'status' => ['sometimes', 'string', Rule::enum(PortfolioStatus::class)],
            /** Display order position. */
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            /** Date when the project started. */
            'started_at' => ['nullable', 'date'],
            /** Date when the project ended. */
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_url.url' => 'The project URL must be a valid URL.',
            'ended_at.after_or_equal' => 'The end date must be after or equal to the start date.',
            'status.Illuminate\Validation\Rules\Enum' => 'The selected status is invalid.',
        ];
    }
}
