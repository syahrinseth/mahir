<?php

namespace App\Modules\Auth\Models;

use App\Shared\Traits\UsesTenantConnection;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Tenant-scoped Role model.
 *
 * Extends Spatie's Role model and forces all queries to run
 * against the tenant database connection, not the landlord connection.
 */
class Role extends SpatieRole
{
    use UsesTenantConnection;
}
