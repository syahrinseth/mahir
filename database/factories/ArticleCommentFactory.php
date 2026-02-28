<?php

namespace Database\Factories;

use App\Modules\Article\Models\Article;
use App\Modules\Article\Models\ArticleComment;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleComment>
 */
class ArticleCommentFactory extends Factory
{
    protected $model = ArticleComment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'user_id' => User::factory(),
            'content' => fake()->paragraph(),
            'is_approved' => false,
        ];
    }

    /**
     * Indicate that the comment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_approved' => true,
        ]);
    }

    /**
     * Indicate that the comment is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_approved' => false,
        ]);
    }

    /**
     * Indicate that the comment is anonymous (no user).
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
        ]);
    }
}
