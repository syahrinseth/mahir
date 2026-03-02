<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'portfolio_id' => null,
            'client_name' => fake()->name(),
            'client_position' => null,
            'client_company' => null,
            'content' => fake()->paragraphs(2, true),
            'rating' => null,
            'is_featured' => false,
            'sort_order' => 0,
            'published_at' => null,
        ];
    }

    /**
     * Indicate that the testimonial is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the testimonial is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the testimonial is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
        ]);
    }

    /**
     * Associate the testimonial with a specific portfolio.
     */
    public function withPortfolio(?Portfolio $portfolio = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'portfolio_id' => $portfolio?->id ?? Portfolio::factory(),
        ]);
    }

    /**
     * Set a rating on the testimonial.
     */
    public function withRating(?int $rating = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'rating' => $rating ?? fake()->numberBetween(1, 5),
        ]);
    }

    /**
     * Set client position and company on the testimonial.
     */
    public function withClientDetails(): static
    {
        return $this->state(fn (array $attributes): array => [
            'client_position' => fake()->jobTitle(),
            'client_company' => fake()->company(),
        ]);
    }
}
