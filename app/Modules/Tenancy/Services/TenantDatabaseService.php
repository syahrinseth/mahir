<?php

namespace App\Modules\Tenancy\Services;

use App\Shared\Contracts\ServiceContract;
use App\Shared\Exceptions\TenantDatabaseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Manages the lifecycle of tenant databases.
 *
 * Handles creating and dropping MySQL databases
 * for individual tenants.
 */
class TenantDatabaseService implements ServiceContract
{
    private const int MAX_CREATION_ATTEMPTS = 3;

    private const int BASE_RETRY_DELAY_MICROSECONDS = 100_000;

    /**
     * Create a new MySQL database for the tenant with retry logic.
     *
     * Retries up to 3 times with exponential backoff (100ms, 200ms, 400ms)
     * to handle transient database connection issues.
     *
     * @throws TenantDatabaseException
     */
    public function createDatabase(string $databaseName): void
    {
        $charset = config('database.connections.tenant.charset', 'utf8mb4');
        $collation = config('database.connections.tenant.collation', 'utf8mb4_unicode_ci');
        $lastException = null;

        for ($attempt = 1; $attempt <= self::MAX_CREATION_ATTEMPTS; $attempt++) {
            try {
                DB::connection('landlord')->statement(
                    "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}"
                );

                Log::info('Tenant database created.', [
                    'database' => $databaseName,
                    'attempt' => $attempt,
                ]);

                return;
            } catch (\Exception $e) {
                $lastException = $e;

                Log::warning('Tenant database creation attempt failed.', [
                    'database' => $databaseName,
                    'attempt' => $attempt,
                    'max_attempts' => self::MAX_CREATION_ATTEMPTS,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt < self::MAX_CREATION_ATTEMPTS) {
                    usleep(self::BASE_RETRY_DELAY_MICROSECONDS * (2 ** ($attempt - 1)));
                }
            }
        }

        throw TenantDatabaseException::failedToCreate(
            $databaseName,
            'Failed after '.self::MAX_CREATION_ATTEMPTS." attempts: {$lastException->getMessage()}"
        );
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
