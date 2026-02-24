<?php

namespace App\Modules\Subscription\Services;

use App\Modules\Subscription\DTOs\CreateSubscriptionDTO;
use App\Modules\Subscription\DTOs\UpdateSubscriptionDTO;
use App\Modules\Subscription\Enums\PlanType;
use App\Modules\Subscription\Enums\SubscriptionStatus;
use App\Modules\Subscription\Models\Subscription;
use App\Modules\Subscription\Repositories\SubscriptionRepository;
use App\Shared\Contracts\ServiceContract;

/**
 * Business logic for managing tenant subscriptions.
 */
class SubscriptionService implements ServiceContract
{
    public function __construct(
        private SubscriptionRepository $repository,
    ) {}

    public function getSubscriptionForTenant(int $tenantId): ?Subscription
    {
        return $this->repository->findByTenantId($tenantId);
    }

    /**
     * Create a new subscription for a tenant.
     */
    public function createSubscription(CreateSubscriptionDTO $dto): Subscription
    {
        return $this->repository->create($dto->toArray());
    }

    /**
     * Update an existing subscription.
     */
    public function updateSubscription(int $id, UpdateSubscriptionDTO $dto): ?Subscription
    {
        $subscription = $this->repository->update($id, $dto->toArray());

        return $subscription instanceof Subscription ? $subscription : null;
    }

    /**
     * Start a trial subscription for a tenant.
     */
    public function startTrial(int $tenantId, PlanType $plan, int $trialDays = 14): Subscription
    {
        $dto = new CreateSubscriptionDTO(
            tenantId: $tenantId,
            plan: $plan,
            status: SubscriptionStatus::Trial,
            trialEndsAt: now()->addDays($trialDays)->toDateTimeString(),
            startsAt: now()->toDateTimeString(),
        );

        return $this->repository->create($dto->toArray());
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(int $subscriptionId): ?Subscription
    {
        $dto = new UpdateSubscriptionDTO(
            status: SubscriptionStatus::Cancelled,
            endsAt: now()->toDateTimeString(),
        );

        $subscription = $this->repository->update($subscriptionId, $dto->toArray());

        return $subscription instanceof Subscription ? $subscription : null;
    }

    /**
     * Check if a tenant has an active subscription.
     */
    public function tenantHasActiveSubscription(int $tenantId): bool
    {
        $subscription = $this->repository->findByTenantId($tenantId);

        if (! $subscription) {
            return false;
        }

        return $subscription->isActive() || $subscription->isOnTrial();
    }
}
