<?php

namespace App\Modules\Tenancy\Actions;

use App\Modules\Tenancy\Events\TenantDeleted;
use App\Modules\Tenancy\Services\TenantService;
use App\Shared\Contracts\ActionContract;

/**
 * Delete a tenant and optionally drop its database.
 */
class DeleteTenantAction implements ActionContract
{
    public function __construct(
        private TenantService $tenantService,
    ) {}

    public function execute(int $tenantId, bool $dropDatabase = true): bool
    {
        $tenant = $this->tenantService->getTenantById($tenantId);

        $result = $this->tenantService->deleteTenant($tenantId, $dropDatabase);

        if ($result) {
            event(new TenantDeleted($tenant->name, $tenant->database));
        }

        return $result;
    }
}
