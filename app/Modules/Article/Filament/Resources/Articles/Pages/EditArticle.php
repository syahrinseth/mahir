<?php

namespace App\Modules\Article\Filament\Resources\Articles\Pages;

use App\Modules\Article\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
