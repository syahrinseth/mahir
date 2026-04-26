<?php

namespace App\Modules\Portfolio\Filament\Resources\Portfolios\Schemas;

use App\Modules\Portfolio\Enums\PortfolioStatus;
use App\Modules\Portfolio\Models\PortfolioCategory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PortfolioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->alphaDash(),
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->options(PortfolioCategory::query()->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Select::make('status')
                    ->options(collect(PortfolioStatus::cases())->mapWithKeys(
                        fn (PortfolioStatus $status): array => [$status->value => $status->label()]
                    ))
                    ->required()
                    ->default(PortfolioStatus::Draft->value),
                Textarea::make('description')
                    ->required()
                    ->maxLength(5000)
                    ->columnSpanFull(),
                TextInput::make('client_name')
                    ->maxLength(255)
                    ->nullable(),
                TextInput::make('project_url')
                    ->url()
                    ->maxLength(2048)
                    ->nullable(),
                TagsInput::make('technologies')
                    ->nullable()
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                DatePicker::make('started_at')
                    ->nullable(),
                DatePicker::make('ended_at')
                    ->nullable()
                    ->afterOrEqual('started_at'),
                DateTimePicker::make('published_at')
                    ->nullable(),
            ]);
    }
}
