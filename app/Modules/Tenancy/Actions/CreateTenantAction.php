<?php

namespace App\Modules\Tenancy\Actions;

use App\Modules\Tenancy\DTOs\CreateTenantDTO;
use App\Modules\Tenancy\Events\TenantCreated;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantDatabaseService;
use App\Modules\Tenancy\Services\TenantService;
use App\Shared\Contracts\ActionContract;
use App\Shared\Exceptions\TenantDatabaseException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Create a new tenant, provision its database, and run migrations.
 */
class CreateTenantAction implements ActionContract
{
    public function __construct(
        private TenantService $tenantService,
        private TenantDatabaseService $databaseService,
    ) {}

    /**
     * @param  array{name: string, slug: string, domain: string}  $data
     */
    public function execute(array $data): Tenant
    {
        $dto = CreateTenantDTO::fromArray($data);

        $this->ensureDatabaseCanBeCreated($dto->slug);

        $tenant = $this->tenantService->createTenant($dto);

        Log::info('Tenant record created, provisioning database.', [
            'tenant_id' => $tenant->id,
            'database' => $tenant->database,
        ]);

        $this->runMigrations($tenant);
        $this->runSeeders($tenant);

        event(new TenantCreated($tenant));

        Log::info('Tenant fully provisioned.', [
            'tenant_id' => $tenant->id,
            'database' => $tenant->database,
        ]);

        return $tenant;
    }

    /**
     * Validate the database name is available before creating the tenant record.
     *
     * @throws TenantDatabaseException
     */
    private function ensureDatabaseCanBeCreated(string $slug): void
    {
        $databaseName = $this->databaseService->generateDatabaseName($slug);

        if ($this->databaseService->databaseExists($databaseName)) {
            throw TenantDatabaseException::alreadyExists($databaseName);
        }
    }

    /**
     * Run tenant-specific migrations on the newly created database.
     *
     * @throws TenantDatabaseException
     */
    private function runMigrations(Tenant $tenant): void
    {
        try {
            $exitCode = Artisan::call('tenants:artisan', [
                'artisanCommand' => 'migrate --database=tenant --path=database/migrations/tenant --force',
                '--tenant' => $tenant->id,
            ]);

            $output = Artisan::output();

            Log::info('Tenant migrations completed.', [
                'tenant_id' => $tenant->id,
                'database' => $tenant->database,
                'exit_code' => $exitCode,
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            Log::error('Tenant migrations failed.', [
                'tenant_id' => $tenant->id,
                'database' => $tenant->database,
                'error' => $e->getMessage(),
            ]);

            throw TenantDatabaseException::migrationFailed($tenant->database, $e->getMessage());
        }
    }

    /**
     * Run tenant-specific seeders on the newly created database.
     *
     * @throws TenantDatabaseException
     */
    private function runSeeders(Tenant $tenant): void
    {
        try {
            $exitCode = Artisan::call('tenants:artisan', [
                'artisanCommand' => 'db:seed --class=Database\\Seeders\\Tenant\\TenantSeeder --force',
                '--tenant' => $tenant->id,
            ]);

            $output = Artisan::output();

            Log::info('Tenant seeding completed.', [
                'tenant_id' => $tenant->id,
                'database' => $tenant->database,
                'exit_code' => $exitCode,
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            Log::error('Tenant seeding failed.', [
                'tenant_id' => $tenant->id,
                'database' => $tenant->database,
                'error' => $e->getMessage(),
            ]);

            throw TenantDatabaseException::seedingFailed($tenant->database, $e->getMessage());
        }
    }
}
