<?php

namespace Database\Factories;

use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Models\PortfolioMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioMedia>
 */
class PortfolioMediaFactory extends Factory
{
    protected $model = PortfolioMedia::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'portfolio_id' => Portfolio::factory(),
            'file_path' => 'portfolios/'.fake()->uuid().'.jpg',
            'file_name' => fake()->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => fake()->numberBetween(50000, 5000000),
            'sort_order' => 0,
            'caption' => null,
        ];
    }

    /**
     * Set a caption on the media item.
     */
    public function withCaption(): static
    {
        return $this->state(fn (array $attributes): array => [
            'caption' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the media item is a PNG image.
     */
    public function png(): static
    {
        return $this->state(fn (array $attributes): array => [
            'file_path' => 'portfolios/'.fake()->uuid().'.png',
            'file_name' => fake()->word().'.png',
            'mime_type' => 'image/png',
        ]);
    }

    /**
     * Indicate that the media item is a PDF document.
     */
    public function pdf(): static
    {
        return $this->state(fn (array $attributes): array => [
            'file_path' => 'portfolios/'.fake()->uuid().'.pdf',
            'file_name' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
        ]);
    }
}
