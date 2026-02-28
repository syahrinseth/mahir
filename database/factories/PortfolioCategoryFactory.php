<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\PortfolioCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PortfolioCategory>
 */
class PortfolioCategoryFactory extends Factory
{
    protected $model = PortfolioCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the category has no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes): array => [
            'description' => null,
        ]);
    }
}
