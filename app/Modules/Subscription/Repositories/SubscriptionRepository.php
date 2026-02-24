<?php

namespace App\Modules\Subscription\Repositories;

use App\Modules\Subscription\Models\Subscription;
use App\Shared\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SubscriptionRepository implements RepositoryContract
{
    /**
     * @return Collection<int, Subscription>
     */
    public function all(): Collection
    {
        return Subscription::query()->with('tenant')->latest()->get();
    }

    public function findById(int $id): ?Subscription
    {
        return Subscription::query()->with('tenant')->find($id);
    }

    public function findByTenantId(int $tenantId): ?Subscription
    {
        return Subscription::query()
            ->where('tenant_id', $tenantId)
            ->latest()
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Subscription
    {
        return Subscription::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Model
    {
        $subscription = $this->findById($id);

        if (! $subscription) {
            return null;
        }

        $subscription->update($data);

        return $subscription->fresh();
    }

    public function delete(int $id): bool
    {
        $subscription = $this->findById($id);

        if (! $subscription) {
            return false;
        }

        return (bool) $subscription->delete();
    }
}
