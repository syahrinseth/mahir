<?php

namespace App\Modules\Article\Http\Requests;

use App\Modules\Article\Enums\ArticleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateArticleRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            /** URL-friendly slug derived from the title. Auto-generated from title if omitted. */
            'slug' => ['nullable', 'string', 'max:255'],
            /** The article body in Markdown format. */
            'content' => ['required', 'string'],
            /** A short summary of the article. */
            'description' => ['nullable', 'string', 'max:500'],
            /** The initial article status. Defaults to "draft". */
            'status' => ['sometimes', 'string', Rule::enum(ArticleStatus::class)],
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
            'title.required' => 'A title is required.',
            'content.required' => 'Article content is required.',
            'status.Illuminate\Validation\Rules\Enum' => 'The selected status is invalid.',
        ];
    }
}
