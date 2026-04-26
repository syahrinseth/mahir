<?php

namespace App\Modules\Portfolio\Filament\Resources\PortfolioCategories;

use App\Modules\Portfolio\Filament\Resources\PortfolioCategories\Pages\CreatePortfolioCategory;
use App\Modules\Portfolio\Filament\Resources\PortfolioCategories\Pages\EditPortfolioCategory;
use App\Modules\Portfolio\Filament\Resources\PortfolioCategories\Pages\ListPortfolioCategories;
use App\Modules\Portfolio\Filament\Resources\PortfolioCategories\Schemas\PortfolioCategoryForm;
use App\Modules\Portfolio\Filament\Resources\PortfolioCategories\Tables\PortfolioCategoriesTable;
use App\Modules\Portfolio\Models\PortfolioCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PortfolioCategoryResource extends Resource
{
    protected static ?string $model = PortfolioCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Portfolio';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Categories';

    public static function form(Schema $schema): Schema
    {
        return PortfolioCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PortfolioCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPortfolioCategories::route('/'),
            'create' => CreatePortfolioCategory::route('/create'),
            'edit' => EditPortfolioCategory::route('/{record}/edit'),
        ];
    }
}
