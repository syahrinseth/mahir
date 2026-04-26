<?php

namespace App\Modules\Article\Filament\Resources\ArticleSeries\Pages;

use App\Modules\Article\Filament\Resources\ArticleSeries\ArticleSeriesResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateArticleSeries extends CreateRecord
{
    protected static string $resource = ArticleSeriesResource::class;

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
