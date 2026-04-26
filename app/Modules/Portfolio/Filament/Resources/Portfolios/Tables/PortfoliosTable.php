<?php

namespace App\Modules\Portfolio\Filament\Resources\Portfolios\Tables;

use App\Modules\Portfolio\Enums\PortfolioStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PortfoliosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (PortfolioStatus $state): string => $state->color())
                    ->formatStateUsing(fn (PortfolioStatus $state): string => $state->label())
                    ->sortable(),
                TextColumn::make('client_name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(PortfolioStatus::cases())->mapWithKeys(
                        fn (PortfolioStatus $status): array => [$status->value => $status->label()]
                    )),
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
