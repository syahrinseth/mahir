<?php

namespace Database\Factories;

use App\Modules\Article\Models\ArticleSeries;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ArticleSeries>
 */
class ArticleSeriesFactory extends Factory
{
    protected $model = ArticleSeries::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the series has no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes): array => [
            'description' => null,
        ]);
    }
}
