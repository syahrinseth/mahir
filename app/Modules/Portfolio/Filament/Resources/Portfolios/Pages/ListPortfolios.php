<?php

namespace App\Modules\Portfolio\Filament\Resources\Portfolios\Pages;

use App\Modules\Portfolio\Filament\Resources\Portfolios\PortfolioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPortfolios extends ListRecords
{
    protected static string $resource = PortfolioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
