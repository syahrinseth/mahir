<?php

namespace App\Modules\Subscription\Providers;

use App\Modules\Subscription\Repositories\SubscriptionRepository;
use App\Modules\Subscription\Services\SubscriptionService;
use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SubscriptionRepository::class);

        $this->app->singleton(SubscriptionService::class, function ($app) {
            return new SubscriptionService(
                $app->make(SubscriptionRepository::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
