<?php

namespace App\Modules\Auth\Filament\Resources\AdminUsers\Pages;

use App\Modules\Auth\Filament\Resources\AdminUsers\AdminUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;
}
