<?php

namespace App\Modules\Portfolio\Filament\Resources\PortfolioCategories\Pages;

use App\Modules\Portfolio\Filament\Resources\PortfolioCategories\PortfolioCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPortfolioCategory extends EditRecord
{
    protected static string $resource = PortfolioCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
