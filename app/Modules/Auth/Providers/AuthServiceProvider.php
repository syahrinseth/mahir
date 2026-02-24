<?php

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Models\PersonalAccessToken;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuthService::class);
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
