<?php

namespace App\Modules\Article\Filament\Resources\Articles;

use App\Modules\Article\Filament\Resources\Articles\Pages\CreateArticle;
use App\Modules\Article\Filament\Resources\Articles\Pages\EditArticle;
use App\Modules\Article\Filament\Resources\Articles\Pages\ListArticles;
use App\Modules\Article\Filament\Resources\Articles\Schemas\ArticleForm;
use App\Modules\Article\Filament\Resources\Articles\Tables\ArticlesTable;
use App\Modules\Article\Models\Article;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ArticleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticlesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }
}
