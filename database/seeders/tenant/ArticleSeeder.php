<?php

namespace Database\Seeders\Tenant;

use App\Modules\Article\Models\Article;
use App\Modules\Article\Models\ArticleComment;
use App\Modules\Article\Models\ArticleSeries;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a tenant database with articles, series, and comments.
     *
     * Must be run within a tenant context via:
     *   php artisan tenants:artisan "db:seed --class=Database\\Seeders\\Tenant\\ArticleSeeder"
     */
    public function run(): void
    {
        $originalConnection = DB::getDefaultConnection();
        DB::setDefaultConnection('tenant');

        try {
            $this->seedArticles();
        } finally {
            DB::setDefaultConnection($originalConnection);
        }
    }

    /**
     * Seed articles, series, comments, and revisions.
     */
    private function seedArticles(): void
    {
        $users = User::query()->take(4)->get();

        if ($users->isEmpty()) {
            return;
        }

        $series = ArticleSeries::factory()->create([
            'user_id' => $users->first()->id,
            'title' => 'Getting Started',
            'slug' => 'getting-started',
            'description' => 'A series of articles to help you get started.',
        ]);

        Article::factory()
            ->published()
            ->inSeries($series, 1)
            ->create([
                'user_id' => $users->first()->id,
                'title' => 'Welcome to the Platform',
                'slug' => 'welcome-to-the-platform',
            ]);

        Article::factory()
            ->published()
            ->inSeries($series, 2)
            ->create([
                'user_id' => $users->first()->id,
                'title' => 'Setting Up Your Account',
                'slug' => 'setting-up-your-account',
            ]);

        $publishedArticles = Article::factory(3)
            ->published()
            ->withViews(fake()->numberBetween(10, 500))
            ->create(['user_id' => $users->random()->id]);

        Article::factory(2)->draft()->create([
            'user_id' => $users->count() > 1 ? $users[1]->id : $users->first()->id,
        ]);

        Article::factory()->archived()->create([
            'user_id' => $users->first()->id,
        ]);

        foreach ($publishedArticles as $article) {
            ArticleComment::factory(fake()->numberBetween(1, 3))->create([
                'article_id' => $article->id,
                'user_id' => $users->random()->id,
                'is_approved' => true,
            ]);

            ArticleComment::factory()->create([
                'article_id' => $article->id,
                'user_id' => $users->random()->id,
                'is_approved' => false,
            ]);
        }
    }
}
