<?php

namespace App\Modules\Portfolio\Filament\Resources\Portfolios\Pages;

use App\Modules\Portfolio\Filament\Resources\Portfolios\PortfolioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPortfolio extends EditRecord
{
    protected static string $resource = PortfolioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
