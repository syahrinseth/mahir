<?php

namespace App\Modules\Portfolio\Filament\Resources\Portfolios;

use App\Modules\Portfolio\Filament\Resources\Portfolios\Pages\CreatePortfolio;
use App\Modules\Portfolio\Filament\Resources\Portfolios\Pages\EditPortfolio;
use App\Modules\Portfolio\Filament\Resources\Portfolios\Pages\ListPortfolios;
use App\Modules\Portfolio\Filament\Resources\Portfolios\Schemas\PortfolioForm;
use App\Modules\Portfolio\Filament\Resources\Portfolios\Tables\PortfoliosTable;
use App\Modules\Portfolio\Models\Portfolio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PortfolioResource extends Resource
{
    protected static ?string $model = Portfolio::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Portfolio';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PortfolioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PortfoliosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPortfolios::route('/'),
            'create' => CreatePortfolio::route('/create'),
            'edit' => EditPortfolio::route('/{record}/edit'),
        ];
    }
}
