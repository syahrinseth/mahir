<?php

namespace Database\Factories;

use App\Modules\Article\Enums\ArticleStatus;
use App\Modules\Article\Models\Article;
use App\Modules\Article\Models\ArticleSeries;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'user_id' => User::factory(),
            'series_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(3, true),
            'description' => fake()->sentence(),
            'status' => ArticleStatus::Draft->value,
            'published_at' => null,
            'views_count' => 0,
            'series_order' => null,
        ];
    }

    /**
     * Indicate that the article is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ArticleStatus::Published->value,
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the article is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ArticleStatus::Draft->value,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the article is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ArticleStatus::Archived->value,
        ]);
    }

    /**
     * Indicate that the article is scheduled for future publication.
     */
    public function scheduled(int $daysFromNow = 7): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ArticleStatus::Published->value,
            'published_at' => now()->addDays($daysFromNow),
        ]);
    }

    /**
     * Associate the article with a specific series.
     */
    public function inSeries(ArticleSeries $series, int $order = 1): static
    {
        return $this->state(fn (array $attributes): array => [
            'series_id' => $series->id,
            'series_order' => $order,
        ]);
    }

    /**
     * Set a specific view count for the article.
     */
    public function withViews(int $count): static
    {
        return $this->state(fn (array $attributes): array => [
            'views_count' => $count,
        ]);
    }
}
