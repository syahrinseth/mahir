<?php

namespace App\Modules\Subscription\DTOs;

use App\Modules\Subscription\Enums\PlanType;
use App\Modules\Subscription\Enums\SubscriptionStatus;

/**
 * Data Transfer Object for updating subscriptions.
 *
 * Tracks which fields were explicitly provided in the request to support
 * partial updates. This allows falsy values (false, 0, empty strings) to be
 * saved correctly, unlike array_filter() which would remove them.
 */
class UpdateSubscriptionDTO
{
    /**
     * @param  set<string>  $providedFields  Fields that were explicitly provided in the request
     */
    public function __construct(
        public readonly ?PlanType $plan = null,
        public readonly ?SubscriptionStatus $status = null,
        public readonly ?string $trialEndsAt = null,
        public readonly ?string $startsAt = null,
        public readonly ?string $endsAt = null,
        private readonly array $providedFields = [],
    ) {}

    /**
     * Create DTO from array, tracking which fields were explicitly provided.
     *
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
            providedFields: array_keys($data),
        );
    }

    /**
     * Convert to array for database updates, only including explicitly provided fields.
     *
     * This approach preserves falsy values (false, 0, empty strings) unlike
     * array_filter(), ensuring partial updates work correctly.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $fieldMapping = [
            'plan' => 'plan',
            'status' => 'status',
            'trial_ends_at' => 'trialEndsAt',
            'starts_at' => 'startsAt',
            'ends_at' => 'endsAt',
        ];

        $result = [];
        foreach ($this->providedFields as $field) {
            if (! isset($fieldMapping[$field])) {
                continue;
            }

            $property = $fieldMapping[$field];
            $value = $this->{$property};

            // For enums, convert to their string value
            if ($value instanceof PlanType || $value instanceof SubscriptionStatus) {
                $result[$field] = $value->value;
            } else {
                $result[$field] = $value;
            }
        }

        return $result;
    }
}
