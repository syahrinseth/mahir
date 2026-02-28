<?php

namespace App\Modules\Article\Providers;

use App\Modules\Article\Models\Article;
use App\Modules\Article\Models\ArticleComment;
use App\Modules\Article\Policies\ArticleCommentPolicy;
use App\Modules\Article\Policies\ArticlePolicy;
use App\Modules\Article\Repositories\ArticleRepository;
use App\Modules\Article\Repositories\ArticleSeriesRepository;
use App\Modules\Article\Services\ArticleService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ArticleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ArticleRepository::class);
        $this->app->singleton(ArticleSeriesRepository::class);

        $this->app->singleton(ArticleService::class, function ($app) {
            return new ArticleService(
                $app->make(ArticleRepository::class),
                $app->make(ArticleSeriesRepository::class),
            );
        });
    }

    public function boot(): void
    {
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(ArticleComment::class, ArticleCommentPolicy::class);
    }
}
