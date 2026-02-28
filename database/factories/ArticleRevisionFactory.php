<?php

namespace Database\Factories;

use App\Modules\Article\Models\Article;
use App\Modules\Article\Models\ArticleRevision;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleRevision>
 */
class ArticleRevisionFactory extends Factory
{
    protected $model = ArticleRevision::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'description' => fake()->sentence(),
            'change_summary' => fake()->sentence(),
            'created_at' => now(),
        ];
    }

    /**
     * Indicate that the revision has no change summary.
     */
    public function withoutChangeSummary(): static
    {
        return $this->state(fn (array $attributes): array => [
            'change_summary' => null,
        ]);
    }
}
