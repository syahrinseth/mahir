<?php

namespace App\Modules\Portfolio\Providers;

use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Policies\PortfolioPolicy;
use App\Modules\Portfolio\Repositories\PortfolioCategoryRepository;
use App\Modules\Portfolio\Repositories\PortfolioRepository;
use App\Modules\Portfolio\Services\PortfolioService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PortfolioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PortfolioRepository::class);
        $this->app->singleton(PortfolioCategoryRepository::class);

        $this->app->singleton(PortfolioService::class, function ($app) {
            return new PortfolioService(
                $app->make(PortfolioRepository::class),
                $app->make(PortfolioCategoryRepository::class),
            );
        });
    }

    public function boot(): void
    {
        Gate::policy(Portfolio::class, PortfolioPolicy::class);
    }
}
