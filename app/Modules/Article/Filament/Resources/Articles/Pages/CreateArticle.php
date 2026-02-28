<?php

namespace App\Modules\Article\Filament\Resources\Articles\Pages;

use App\Modules\Article\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;
}
