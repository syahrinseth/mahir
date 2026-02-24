<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Subscription\Http\Controllers\SubscriptionController;
use App\Modules\Tenancy\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Tenant-aware API routes are loaded under the subdomain pattern
| {tenant}.mahir.test/api/v1/... via bootstrap/app.php configuration.
|
| The IdentifyTenant middleware (prepended to the api group) resolves
| the current tenant from the subdomain before these routes execute.
|
| Landlord routes (admin panel) are handled by Filament at admin.mahir.test.
|
*/

Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
})->name('api.ping');

/*
|--------------------------------------------------------------------------
| Auth Routes (Tenant-scoped)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/user', [AuthController::class, 'user'])->name('api.auth.user');
    });
});

/*
|--------------------------------------------------------------------------
| Tenant Management Routes (Landlord-scoped, requires auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('tenants')->group(function () {
    Route::get('/', [TenantController::class, 'index'])->name('api.tenants.index');
    Route::post('/', [TenantController::class, 'store'])->name('api.tenants.store');
    Route::get('/{tenant}', [TenantController::class, 'show'])->name('api.tenants.show');
    Route::put('/{tenant}', [TenantController::class, 'update'])->name('api.tenants.update');
    Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('api.tenants.destroy');
});

/*
|--------------------------------------------------------------------------
| Subscription Routes (Landlord-scoped, requires auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('subscriptions')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index'])->name('api.subscriptions.index');
    Route::post('/', [SubscriptionController::class, 'store'])->name('api.subscriptions.store');
    Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('api.subscriptions.show');
    Route::put('/{subscription}', [SubscriptionController::class, 'update'])->name('api.subscriptions.update');
    Route::delete('/{subscription}', [SubscriptionController::class, 'destroy'])->name('api.subscriptions.destroy');
});
