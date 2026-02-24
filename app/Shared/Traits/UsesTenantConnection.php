<?php

namespace App\Shared\Traits;

/**
 * Assigns the tenant database connection to the model.
 *
 * Use this trait on any Eloquent model whose underlying table
 * lives in the tenant-specific database.
 */
trait UsesTenantConnection
{
    public function getConnectionName(): ?string
    {
        return config('multitenancy.tenant_database_connection_name');
    }
}
