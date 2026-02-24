<?php

namespace App\Modules\Subscription\DTOs;

use App\Modules\Subscription\Enums\PlanType;
use App\Modules\Subscription\Enums\SubscriptionStatus;

class UpdateSubscriptionDTO
{
    public function __construct(
        public readonly ?PlanType $plan = null,
        public readonly ?SubscriptionStatus $status = null,
        public readonly ?string $trialEndsAt = null,
        public readonly ?string $startsAt = null,
        public readonly ?string $endsAt = null,
    ) {}

    /**
     * @param  array{plan?: string, status?: string, trial_ends_at?: string|null, starts_at?: string|null, ends_at?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            plan: isset($data['plan']) ? PlanType::from($data['plan']) : null,
            status: isset($data['status']) ? SubscriptionStatus::from($data['status']) : null,
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
        return array_filter([
            'plan' => $this->plan?->value,
            'status' => $this->status?->value,
            'trial_ends_at' => $this->trialEndsAt,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
        ], fn (mixed $value): bool => $value !== null);
    }
}
