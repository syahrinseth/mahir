<?php

namespace App\Modules\Portfolio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialMediaRequest extends FormRequest
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
            /** The client headshot or logo image file. */
            'file' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A file is required.',
            'file.max' => 'The file must not be larger than 5 MB.',
            'file.mimes' => 'The file must be an image (jpg, png, or webp).',
        ];
    }
}
