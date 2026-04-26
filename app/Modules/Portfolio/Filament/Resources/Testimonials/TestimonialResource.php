<?php

namespace App\Modules\Portfolio\Filament\Resources\Testimonials;

use App\Modules\Portfolio\Filament\Resources\Testimonials\Pages\CreateTestimonial;
use App\Modules\Portfolio\Filament\Resources\Testimonials\Pages\EditTestimonial;
use App\Modules\Portfolio\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Modules\Portfolio\Filament\Resources\Testimonials\Schemas\TestimonialForm;
use App\Modules\Portfolio\Filament\Resources\Testimonials\Tables\TestimonialsTable;
use App\Modules\Portfolio\Models\Testimonial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Portfolio';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return TestimonialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TestimonialsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTestimonials::route('/'),
            'create' => CreateTestimonial::route('/create'),
            'edit' => EditTestimonial::route('/{record}/edit'),
        ];
    }
}
