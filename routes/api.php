<?php

use App\Modules\Article\Http\Controllers\ArticleCommentController;
use App\Modules\Article\Http\Controllers\ArticleController;
use App\Modules\Article\Http\Controllers\ArticleRevisionController;
use App\Modules\Article\Http\Controllers\ArticleSeriesController;
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

/*
|--------------------------------------------------------------------------
| Article Routes (Tenant-scoped, requires auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index'])->name('api.articles.index');
    Route::post('/', [ArticleController::class, 'store'])->name('api.articles.store');
    Route::get('/{article}', [ArticleController::class, 'show'])->name('api.articles.show');
    Route::put('/{article}', [ArticleController::class, 'update'])->name('api.articles.update');
    Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('api.articles.destroy');
    Route::post('/{article}/publish', [ArticleController::class, 'publish'])->name('api.articles.publish');
    Route::post('/{article}/archive', [ArticleController::class, 'archive'])->name('api.articles.archive');

    // Article Comments
    Route::get('/{article}/comments', [ArticleCommentController::class, 'index'])->name('api.articles.comments.index');
    Route::post('/{article}/comments', [ArticleCommentController::class, 'store'])->name('api.articles.comments.store');
    Route::delete('/{article}/comments/{comment}', [ArticleCommentController::class, 'destroy'])->name('api.articles.comments.destroy');

    // Article Revisions
    Route::get('/{article}/revisions', [ArticleRevisionController::class, 'index'])->name('api.articles.revisions.index');
    Route::get('/{article}/revisions/{revision}', [ArticleRevisionController::class, 'show'])->name('api.articles.revisions.show');
    Route::post('/{article}/restore-revision/{revision}', [ArticleRevisionController::class, 'restore'])->name('api.articles.revisions.restore');
});

/*
|--------------------------------------------------------------------------
| Article Series Routes (Tenant-scoped, requires auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('article-series')->group(function () {
    Route::get('/', [ArticleSeriesController::class, 'index'])->name('api.article-series.index');
    Route::post('/', [ArticleSeriesController::class, 'store'])->name('api.article-series.store');
    Route::get('/{series}', [ArticleSeriesController::class, 'show'])->name('api.article-series.show');
    Route::put('/{series}', [ArticleSeriesController::class, 'update'])->name('api.article-series.update');
    Route::delete('/{series}', [ArticleSeriesController::class, 'destroy'])->name('api.article-series.destroy');
});
