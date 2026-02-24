<?php

namespace App\Modules\Tenancy\Services;

use App\Shared\Contracts\ServiceContract;
use App\Shared\Exceptions\TenantDatabaseException;
use Illuminate\Support\Facades\DB;

/**
 * Manages the lifecycle of tenant databases.
 *
 * Handles creating and dropping MySQL databases
 * for individual tenants.
 */
class TenantDatabaseService implements ServiceContract
{
    /**
     * Create a new MySQL database for the tenant.
     *
     * @throws TenantDatabaseException
     */
    public function createDatabase(string $databaseName): void
    {
        $charset = config('database.connections.tenant.charset', 'utf8mb4');
        $collation = config('database.connections.tenant.collation', 'utf8mb4_unicode_ci');

        try {
            DB::connection('landlord')->statement(
                "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}"
            );
        } catch (\Exception $e) {
            throw TenantDatabaseException::failedToCreate($databaseName, $e->getMessage());
        }
    }

    /**
     * Drop a tenant's MySQL database.
     *
     * @throws TenantDatabaseException
     */
    public function deleteDatabase(string $databaseName): void
    {
        try {
            DB::connection('landlord')->statement(
                "DROP DATABASE IF EXISTS `{$databaseName}`"
            );
        } catch (\Exception $e) {
            throw TenantDatabaseException::failedToDelete($databaseName, $e->getMessage());
        }
    }

    /**
     * Check whether a database already exists.
     */
    public function databaseExists(string $databaseName): bool
    {
        $result = DB::connection('landlord')->select(
            'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$databaseName]
        );

        return count($result) > 0;
    }

    /**
     * Generate a database name from a tenant slug.
     */
    public function generateDatabaseName(string $slug): string
    {
        $prefix = config('app.name', 'mahir');

        return strtolower(str_replace('-', '_', "{$prefix}_tenant_{$slug}"));
    }
}
