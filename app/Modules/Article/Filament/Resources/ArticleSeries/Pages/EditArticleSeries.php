<?php

namespace App\Modules\Article\Filament\Resources\ArticleSeries\Pages;

use App\Modules\Article\Filament\Resources\ArticleSeries\ArticleSeriesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArticleSeries extends EditRecord
{
    protected static string $resource = ArticleSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
