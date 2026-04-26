<?php

namespace App\Modules\Portfolio\Filament\Resources\Testimonials\Schemas;

use App\Modules\Portfolio\Models\Portfolio;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('client_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('client_position')
                    ->maxLength(255)
                    ->nullable(),
                TextInput::make('client_company')
                    ->maxLength(255)
                    ->nullable(),
                Select::make('portfolio_id')
                    ->label('Portfolio')
                    ->options(Portfolio::query()->pluck('title', 'id'))
                    ->searchable()
                    ->nullable(),
                Textarea::make('content')
                    ->required()
                    ->maxLength(2000)
                    ->columnSpanFull(),
                TextInput::make('rating')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->nullable(),
                Toggle::make('is_featured')
                    ->default(false),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                DateTimePicker::make('published_at')
                    ->nullable(),
            ]);
    }
}
