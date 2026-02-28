<?php

namespace App\Modules\Article\Http\Requests;

use App\Modules\Article\Enums\ArticleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
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
            /** The article title. */
            'title' => ['sometimes', 'string', 'max:255'],
            /** URL-friendly slug derived from the title. */
            'slug' => ['sometimes', 'string', 'max:255'],
            /** The article body in Markdown format. */
            'content' => ['sometimes', 'string'],
            /** A short summary of the article. */
            'description' => ['nullable', 'string', 'max:500'],
            /** The article status. */
            'status' => ['sometimes', 'string', Rule::enum(ArticleStatus::class)],
            /** URL to a featured image. */
            'featured_image' => ['nullable', 'string', 'max:2048'],
            /** Scheduled publication date and time. */
            'published_at' => ['nullable', 'date'],
            /** The series this article belongs to. */
            'series_id' => ['nullable', 'integer'],
            /** The article's position within the series. */
            'series_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.Illuminate\Validation\Rules\Enum' => 'The selected status is invalid.',
        ];
    }
}
