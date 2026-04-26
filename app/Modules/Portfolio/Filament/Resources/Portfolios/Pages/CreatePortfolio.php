<?php

namespace App\Modules\Portfolio\Filament\Resources\Portfolios\Pages;

use App\Modules\Portfolio\Filament\Resources\Portfolios\PortfolioResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePortfolio extends CreateRecord
{
    protected static string $resource = PortfolioResource::class;

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
