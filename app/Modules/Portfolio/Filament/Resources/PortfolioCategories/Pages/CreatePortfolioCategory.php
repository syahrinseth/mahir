<?php

namespace App\Modules\Portfolio\Filament\Resources\PortfolioCategories\Pages;

use App\Modules\Portfolio\Filament\Resources\PortfolioCategories\PortfolioCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePortfolioCategory extends CreateRecord
{
    protected static string $resource = PortfolioCategoryResource::class;

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
