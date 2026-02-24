<?php

namespace App\Modules\Tenancy\Filament\Resources\Tenants\Pages;

use App\Modules\Tenancy\Filament\Resources\Tenants\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;
}
