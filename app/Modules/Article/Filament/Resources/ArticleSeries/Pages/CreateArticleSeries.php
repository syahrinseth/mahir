<?php

namespace App\Modules\Article\Filament\Resources\ArticleSeries\Pages;

use App\Modules\Article\Filament\Resources\ArticleSeries\ArticleSeriesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticleSeries extends CreateRecord
{
    protected static string $resource = ArticleSeriesResource::class;
}
