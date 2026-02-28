<?php

namespace App\Modules\Article\Filament\Resources\ArticleSeries;

use App\Modules\Article\Filament\Resources\ArticleSeries\Pages\CreateArticleSeries;
use App\Modules\Article\Filament\Resources\ArticleSeries\Pages\EditArticleSeries;
use App\Modules\Article\Filament\Resources\ArticleSeries\Pages\ListArticleSeries;
use App\Modules\Article\Filament\Resources\ArticleSeries\Schemas\ArticleSeriesForm;
use App\Modules\Article\Filament\Resources\ArticleSeries\Tables\ArticleSeriesTable;
use App\Modules\Article\Models\ArticleSeries as ArticleSeriesModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ArticleSeriesResource extends Resource
{
    protected static ?string $model = ArticleSeriesModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Article Series';

    public static function form(Schema $schema): Schema
    {
        return ArticleSeriesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticleSeriesTable::configure($table);
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
            'index' => ListArticleSeries::route('/'),
            'create' => CreateArticleSeries::route('/create'),
            'edit' => EditArticleSeries::route('/{record}/edit'),
        ];
    }
}
