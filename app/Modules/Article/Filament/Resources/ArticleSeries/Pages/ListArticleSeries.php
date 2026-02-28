<?php

namespace App\Modules\Article\Filament\Resources\ArticleSeries\Pages;

use App\Modules\Article\Filament\Resources\ArticleSeries\ArticleSeriesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArticleSeries extends ListRecords
{
    protected static string $resource = ArticleSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
