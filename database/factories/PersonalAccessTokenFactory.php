<?php

namespace Database\Factories;

use App\Modules\Auth\Models\PersonalAccessToken;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PersonalAccessToken>
 */
class PersonalAccessTokenFactory extends Factory
{
    protected $model = PersonalAccessToken::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tokenable_type' => User::class,
            'tokenable_id' => User::factory(),
            'name' => fake()->randomElement(['iPhone', 'Android', 'Browser', 'Tablet', 'Desktop']),
            'token' => hash('sha256', Str::random(40)),
            'abilities' => ['*'],
            'last_used_at' => null,
            'expires_at' => null,
        ];
    }

    /**
     * Associate the token with a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * Set specific abilities/scopes for the token.
     *
     * @param  list<string>  $abilities
     */
    public function withAbilities(array $abilities): static
    {
        return $this->state(fn (array $attributes) => [
            'abilities' => $abilities,
        ]);
    }

    /**
     * Indicate that the token has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the token has not expired.
     */
    public function notExpired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addYear(),
        ]);
    }

    /**
     * Set the token as recently used.
     */
    public function recentlyUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_used_at' => now(),
        ]);
    }
}
