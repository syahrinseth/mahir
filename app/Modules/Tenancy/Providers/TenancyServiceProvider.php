<?php

namespace App\Modules\Tenancy\Providers;

use App\Modules\Tenancy\Repositories\TenantRepository;
use App\Modules\Tenancy\Services\TenantDatabaseService;
use App\Modules\Tenancy\Services\TenantService;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantRepository::class);
        $this->app->singleton(TenantDatabaseService::class);

        $this->app->singleton(TenantService::class, function ($app) {
            return new TenantService(
                $app->make(TenantRepository::class),
                $app->make(TenantDatabaseService::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
