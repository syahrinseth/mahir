<?php

namespace App\Modules\Tenancy\Services;

use App\Modules\Tenancy\DTOs\CreateTenantDTO;
use App\Modules\Tenancy\DTOs\UpdateTenantDTO;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Repositories\TenantRepository;
use App\Shared\Contracts\ServiceContract;
use App\Shared\Exceptions\TenantNotFoundException;
use Illuminate\Database\Eloquent\Collection;

/**
 * Core business logic for tenant management.
 *
 * Orchestrates tenant CRUD operations, database provisioning,
 * and tenant lifecycle operations.
 */
class TenantService implements ServiceContract
{
    public function __construct(
        private TenantRepository $repository,
        private TenantDatabaseService $databaseService,
    ) {}

    /**
     * @return Collection<int, Tenant>
     */
    public function listAllTenants(): Collection
    {
        return $this->repository->all();
    }

    /**
     * @return Collection<int, Tenant>
     */
    public function listActiveTenants(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * @throws TenantNotFoundException
     */
    public function getTenantById(int $id): Tenant
    {
        $tenant = $this->repository->findById($id);

        if (! $tenant) {
            throw TenantNotFoundException::forId($id);
        }

        return $tenant;
    }

    public function getTenantBySlug(string $slug): ?Tenant
    {
        return $this->repository->findBySlug($slug);
    }

    public function getTenantByDomain(string $domain): ?Tenant
    {
        return $this->repository->findByDomain($domain);
    }

    /**
     * Create a new tenant with its own database.
     */
    public function createTenant(CreateTenantDTO $dto): Tenant
    {
        $databaseName = $this->databaseService->generateDatabaseName($dto->slug);

        $this->databaseService->createDatabase($databaseName);

        return $this->repository->create([
            ...$dto->toArray(),
            'database' => $databaseName,
        ]);
    }

    /**
     * Update tenant details.
     */
    public function updateTenant(int $id, UpdateTenantDTO $dto): Tenant
    {
        $tenant = $this->repository->update($id, $dto->toArray());

        if (! $tenant instanceof Tenant) {
            throw TenantNotFoundException::forId($id);
        }

        return $tenant;
    }

    /**
     * Delete a tenant and optionally drop its database.
     */
    public function deleteTenant(int $id, bool $dropDatabase = false): bool
    {
        $tenant = $this->getTenantById($id);

        if ($dropDatabase) {
            $this->databaseService->deleteDatabase($tenant->database);
        }

        return $this->repository->delete($id);
    }

    /**
     * Activate a tenant.
     */
    public function activateTenant(int $id): Tenant
    {
        $dto = new UpdateTenantDTO(isActive: true);

        return $this->updateTenant($id, $dto);
    }

    /**
     * Deactivate a tenant.
     */
    public function deactivateTenant(int $id): Tenant
    {
        $dto = new UpdateTenantDTO(isActive: false);

        return $this->updateTenant($id, $dto);
    }
}
