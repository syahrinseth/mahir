<?php

namespace App\Modules\Article\Filament\Resources\Articles\Schemas;

use App\Modules\Article\Enums\ArticleStatus;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('description')
                    ->maxLength(500),
                MarkdownEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(collect(ArticleStatus::cases())->mapWithKeys(
                        fn (ArticleStatus $status): array => [$status->value => $status->label()]
                    )->all())
                    ->default(ArticleStatus::Draft->value)
                    ->required(),
                Select::make('series_id')
                    ->label('Series')
                    ->relationship('series', 'title')
                    ->searchable()
                    ->preload(),
                TextInput::make('series_order')
                    ->numeric()
                    ->minValue(0),
            ]);
    }
}
