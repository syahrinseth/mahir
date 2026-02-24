<?php

namespace App\Modules\Subscription\DTOs;

use App\Modules\Subscription\Enums\PlanType;
use App\Modules\Subscription\Enums\SubscriptionStatus;

class CreateSubscriptionDTO
{
    public function __construct(
        public readonly int $tenantId,
        public readonly PlanType $plan,
        public readonly SubscriptionStatus $status = SubscriptionStatus::Active,
        public readonly ?string $trialEndsAt = null,
        public readonly ?string $startsAt = null,
        public readonly ?string $endsAt = null,
    ) {}

    /**
     * @param  array{tenant_id: int, plan: string, status?: string, trial_ends_at?: string|null, starts_at?: string|null, ends_at?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tenantId: $data['tenant_id'],
            plan: PlanType::from($data['plan']),
            status: isset($data['status']) ? SubscriptionStatus::from($data['status']) : SubscriptionStatus::Active,
            trialEndsAt: $data['trial_ends_at'] ?? null,
            startsAt: $data['starts_at'] ?? null,
            endsAt: $data['ends_at'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'plan' => $this->plan->value,
            'status' => $this->status->value,
            'trial_ends_at' => $this->trialEndsAt,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
        ];
    }
}
