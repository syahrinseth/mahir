<?php

namespace App\Modules\Tenancy\Actions;

use App\Modules\Tenancy\DTOs\CreateTenantDTO;
use App\Modules\Tenancy\Events\TenantCreated;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantService;
use App\Shared\Contracts\ActionContract;
use Illuminate\Support\Facades\Artisan;

/**
 * Create a new tenant, provision its database, and run migrations.
 */
class CreateTenantAction implements ActionContract
{
    public function __construct(
        private TenantService $tenantService,
    ) {}

    /**
     * @param  array{name: string, slug: string, domain: string}  $data
     */
    public function execute(array $data): Tenant
    {
        $dto = CreateTenantDTO::fromArray($data);

        $tenant = $this->tenantService->createTenant($dto);

        $this->runMigrations($tenant);

        event(new TenantCreated($tenant));

        return $tenant;
    }

    /**
     * Run tenant-specific migrations on the newly created database.
     */
    private function runMigrations(Tenant $tenant): void
    {
        Artisan::call('tenants:artisan', [
            'artisanCommand' => 'migrate --database=tenant --path=database/migrations/tenant --force',
            '--tenant' => $tenant->id,
        ]);
    }
}
