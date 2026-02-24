<?php

namespace Database\Factories;

use App\Modules\Subscription\Enums\PlanType;
use App\Modules\Subscription\Enums\SubscriptionStatus;
use App\Modules\Subscription\Models\Subscription;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'plan' => fake()->randomElement(PlanType::cases())->value,
            'status' => SubscriptionStatus::Active->value,
            'trial_ends_at' => null,
            'starts_at' => now(),
            'ends_at' => null,
        ];
    }

    /**
     * Indicate the subscription is on trial.
     */
    public function onTrial(int $trialDays = 14): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SubscriptionStatus::Trial->value,
            'trial_ends_at' => now()->addDays($trialDays),
            'starts_at' => now(),
        ]);
    }

    /**
     * Indicate the subscription is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SubscriptionStatus::Cancelled->value,
            'ends_at' => now(),
        ]);
    }

    /**
     * Indicate the subscription is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SubscriptionStatus::Expired->value,
            'ends_at' => now()->subDay(),
        ]);
    }

    /**
     * Set a specific plan type.
     */
    public function withPlan(PlanType $plan): static
    {
        return $this->state(fn (array $attributes): array => [
            'plan' => $plan->value,
        ]);
    }
}
