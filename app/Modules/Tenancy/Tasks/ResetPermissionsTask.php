<?php

namespace App\Modules\Tenancy\Tasks;

use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;
use Spatie\Permission\PermissionRegistrar;

/**
 * Resets Spatie Permission's in-memory permission cache when switching tenants.
 *
 * PrefixCacheTask already namespaces the persistent cache per-tenant, but
 * the PermissionRegistrar also holds permissions in-memory for the duration
 * of the request. Without this reset, a process that handles multiple tenants
 * (queue workers, tests) could serve stale permissions from a previous tenant.
 */
class ResetPermissionsTask implements SwitchTenantTask
{
    public function __construct(protected PermissionRegistrar $permissionRegistrar) {}

    public function makeCurrent(IsTenant $tenant): void
    {
        $this->permissionRegistrar->forgetCachedPermissions();
    }

    public function forgetCurrent(): void
    {
        $this->permissionRegistrar->forgetCachedPermissions();
    }
}
