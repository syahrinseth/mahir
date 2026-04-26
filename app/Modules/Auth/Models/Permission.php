<?php

namespace App\Modules\Auth\Models;

use App\Shared\Traits\UsesTenantConnection;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Tenant-scoped Permission model.
 *
 * Extends Spatie's Permission model and forces all queries to run
 * against the tenant database connection, not the landlord connection.
 */
class Permission extends SpatiePermission
{
    use UsesTenantConnection;
}
