<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Modules\Tenancy\Providers\TenancyServiceProvider::class,
    App\Modules\Auth\Providers\AuthServiceProvider::class,
    App\Modules\Subscription\Providers\SubscriptionServiceProvider::class,
    App\Modules\Article\Providers\ArticleServiceProvider::class,
    App\Modules\Portfolio\Providers\PortfolioServiceProvider::class,
];
