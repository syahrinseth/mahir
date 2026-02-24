<?php

namespace App\Modules\Tenancy\Repositories;

use App\Modules\Tenancy\Models\Tenant;
use App\Shared\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TenantRepository implements RepositoryContract
{
    /**
     * @return Collection<int, Tenant>
     */
    public function all(): Collection
    {
        return Tenant::query()->orderBy('name')->get();
    }

    public function findById(int $id): ?Tenant
    {
        return Tenant::query()->find($id);
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return Tenant::query()->where('slug', $slug)->first();
    }

    public function findByDomain(string $domain): ?Tenant
    {
        return Tenant::query()->where('domain', $domain)->first();
    }

    /**
     * @return Collection<int, Tenant>
     */
    public function getActive(): Collection
    {
        return Tenant::query()->where('is_active', true)->orderBy('name')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tenant
    {
        return Tenant::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Model
    {
        $tenant = $this->findById($id);

        if (! $tenant) {
            return null;
        }

        $tenant->update($data);

        return $tenant->fresh();
    }

    public function delete(int $id): bool
    {
        $tenant = $this->findById($id);

        if (! $tenant) {
            return false;
        }

        return (bool) $tenant->delete();
    }
}
