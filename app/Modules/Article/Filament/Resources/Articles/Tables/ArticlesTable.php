<?php

namespace App\Modules\Article\Filament\Resources\Articles\Tables;

use App\Modules\Article\Enums\ArticleStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ArticleStatus $state): string => $state->color() ?? 'gray')
                    ->sortable(),
                TextColumn::make('series.title')
                    ->label('Series')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
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
                    ->options(collect(ArticleStatus::cases())->mapWithKeys(
                        fn (ArticleStatus $status): array => [$status->value => $status->label()]
                    )->all()),
            ])
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
