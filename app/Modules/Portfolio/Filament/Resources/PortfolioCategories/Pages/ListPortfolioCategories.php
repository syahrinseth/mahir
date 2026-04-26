<?php

namespace App\Modules\Portfolio\Filament\Resources\PortfolioCategories\Pages;

use App\Modules\Portfolio\Filament\Resources\PortfolioCategories\PortfolioCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPortfolioCategories extends ListRecords
{
    protected static string $resource = PortfolioCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
