<?php

namespace App\Modules\Tenancy\Filament\Resources\Tenants;

use App\Modules\Tenancy\Filament\Resources\Tenants\Pages\CreateTenant;
use App\Modules\Tenancy\Filament\Resources\Tenants\Pages\EditTenant;
use App\Modules\Tenancy\Filament\Resources\Tenants\Pages\ListTenants;
use App\Modules\Tenancy\Filament\Resources\Tenants\Schemas\TenantForm;
use App\Modules\Tenancy\Filament\Resources\Tenants\Tables\TenantsTable;
use App\Modules\Tenancy\Models\Tenant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|UnitEnum|null $navigationGroup = 'Tenancy';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return TenantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table);
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
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'edit' => EditTenant::route('/{record}/edit'),
        ];
    }
}
