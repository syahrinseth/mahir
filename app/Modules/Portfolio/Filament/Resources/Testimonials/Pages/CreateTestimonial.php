<?php

namespace App\Modules\Portfolio\Filament\Resources\Testimonials\Pages;

use App\Modules\Portfolio\Filament\Resources\Testimonials\TestimonialResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTestimonial extends CreateRecord
{
    protected static string $resource = TestimonialResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
