<?php

namespace App\Modules\Article\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleMediaRequest extends FormRequest
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
            /** The media file to upload. */
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,svg,pdf'],
            /** An optional caption for this media item. */
            'caption' => ['nullable', 'string', 'max:500'],
            /** Alternative text for accessibility. */
            'alt_text' => ['nullable', 'string', 'max:255'],
            /** The display order for sorting. */
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            /** The media collection to upload to ('gallery' or 'featured'). */
            'collection' => ['sometimes', 'string', 'in:gallery,featured'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A file is required.',
            'file.max' => 'The file must not be larger than 10 MB.',
            'file.mimes' => 'The file must be an image (jpg, png, gif, webp, svg) or PDF.',
            'collection.in' => 'The collection must be either gallery or featured.',
        ];
    }
}
