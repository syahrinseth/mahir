<?php

namespace App\Modules\Auth\Filament\Resources\AdminUsers;

use App\Modules\Auth\Filament\Resources\AdminUsers\Pages\CreateAdminUser;
use App\Modules\Auth\Filament\Resources\AdminUsers\Pages\EditAdminUser;
use App\Modules\Auth\Filament\Resources\AdminUsers\Pages\ListAdminUsers;
use App\Modules\Auth\Filament\Resources\AdminUsers\Schemas\AdminUserForm;
use App\Modules\Auth\Filament\Resources\AdminUsers\Tables\AdminUsersTable;
use App\Modules\Auth\Models\AdminUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AdminUserResource extends Resource
{
    protected static ?string $model = AdminUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AdminUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminUsersTable::configure($table);
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
            'index' => ListAdminUsers::route('/'),
            'create' => CreateAdminUser::route('/create'),
            'edit' => EditAdminUser::route('/{record}/edit'),
        ];
    }
}
