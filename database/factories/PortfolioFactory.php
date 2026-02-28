<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Enums\PortfolioStatus;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Models\PortfolioCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Portfolio>
 */
class PortfolioFactory extends Factory
{
    protected $model = Portfolio::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'client_name' => null,
            'project_url' => null,
            'featured_image' => null,
            'technologies' => null,
            'status' => PortfolioStatus::Draft->value,
            'sort_order' => 0,
            'started_at' => null,
            'ended_at' => null,
            'published_at' => null,
        ];
    }

    /**
     * Indicate that the portfolio item is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PortfolioStatus::Published->value,
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the portfolio item is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PortfolioStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the portfolio item is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PortfolioStatus::Archived->value,
        ]);
    }

    /**
     * Set client information on the portfolio item.
     */
    public function withClient(): static
    {
        return $this->state(fn (array $attributes): array => [
            'client_name' => fake()->company(),
        ]);
    }

    /**
     * Set a list of technologies on the portfolio item.
     *
     * @param  list<string>|null  $technologies
     */
    public function withTechnologies(?array $technologies = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'technologies' => $technologies ?? fake()->randomElements(
                ['Laravel', 'React', 'Vue', 'Tailwind CSS', 'PostgreSQL', 'Redis', 'Docker', 'TypeScript', 'Next.js', 'Inertia'],
                fake()->numberBetween(2, 5),
            ),
        ]);
    }

    /**
     * Set a project URL on the portfolio item.
     */
    public function withProjectUrl(): static
    {
        return $this->state(fn (array $attributes): array => [
            'project_url' => fake()->url(),
        ]);
    }

    /**
     * Set a featured image on the portfolio item.
     */
    public function withFeaturedImage(): static
    {
        return $this->state(fn (array $attributes): array => [
            'featured_image' => fake()->imageUrl(),
        ]);
    }

    /**
     * Set project date range on the portfolio item.
     */
    public function withDateRange(): static
    {
        $startDate = fake()->dateTimeBetween('-2 years', '-3 months');

        return $this->state(fn (array $attributes): array => [
            'started_at' => $startDate,
            'ended_at' => fake()->dateTimeBetween($startDate, 'now'),
        ]);
    }

    /**
     * Associate the portfolio item with a specific category.
     */
    public function inCategory(PortfolioCategory $category): static
    {
        return $this->state(fn (array $attributes): array => [
            'category_id' => $category->id,
        ]);
    }
}
