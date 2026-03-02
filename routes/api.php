<?php

use App\Modules\Article\Http\Controllers\ArticleCommentController;
use App\Modules\Article\Http\Controllers\ArticleController;
use App\Modules\Article\Http\Controllers\ArticleRevisionController;
use App\Modules\Article\Http\Controllers\ArticleSeriesController;
use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Portfolio\Http\Controllers\PortfolioCategoryController;
use App\Modules\Portfolio\Http\Controllers\PortfolioController;
use App\Modules\Portfolio\Http\Controllers\PortfolioMediaController;
use App\Modules\Portfolio\Http\Controllers\TestimonialController;
use App\Modules\Portfolio\Http\Controllers\TestimonialMediaController;
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
| Article Routes (Tenant-scoped)
|--------------------------------------------------------------------------
| Index and show are publicly accessible (auth is optional).
| Write operations require authentication.
*/
Route::prefix('articles')->group(function () {
    // Public read endpoints
    Route::get('/', [ArticleController::class, 'index'])->name('api.articles.index');
    Route::get('/{article}', [ArticleController::class, 'show'])->name('api.articles.show');
    Route::get('/{article}/comments', [ArticleCommentController::class, 'index'])->name('api.articles.comments.index');

    // Authenticated write endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [ArticleController::class, 'store'])->name('api.articles.store');
        Route::put('/{article}', [ArticleController::class, 'update'])->name('api.articles.update');
        Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('api.articles.destroy');
        Route::post('/{article}/publish', [ArticleController::class, 'publish'])->name('api.articles.publish');
        Route::post('/{article}/archive', [ArticleController::class, 'archive'])->name('api.articles.archive');

        // Article Comments (write)
        Route::post('/{article}/comments', [ArticleCommentController::class, 'store'])->name('api.articles.comments.store');
        Route::delete('/{article}/comments/{comment}', [ArticleCommentController::class, 'destroy'])->name('api.articles.comments.destroy');

        // Article Revisions (auth-only)
        Route::get('/{article}/revisions', [ArticleRevisionController::class, 'index'])->name('api.articles.revisions.index');
        Route::get('/{article}/revisions/{revision}', [ArticleRevisionController::class, 'show'])->name('api.articles.revisions.show');
        Route::post('/{article}/restore-revision/{revision}', [ArticleRevisionController::class, 'restore'])->name('api.articles.revisions.restore');
    });
});

/*
|--------------------------------------------------------------------------
| Article Series Routes (Tenant-scoped)
|--------------------------------------------------------------------------
| Index and show are publicly accessible.
| Write operations require authentication.
*/
Route::prefix('article-series')->group(function () {
    // Public read endpoints
    Route::get('/', [ArticleSeriesController::class, 'index'])->name('api.article-series.index');
    Route::get('/{series}', [ArticleSeriesController::class, 'show'])->name('api.article-series.show');

    // Authenticated write endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [ArticleSeriesController::class, 'store'])->name('api.article-series.store');
        Route::put('/{series}', [ArticleSeriesController::class, 'update'])->name('api.article-series.update');
        Route::delete('/{series}', [ArticleSeriesController::class, 'destroy'])->name('api.article-series.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Portfolio Routes (Tenant-scoped)
|--------------------------------------------------------------------------
| Index and show are publicly accessible (auth is optional).
| Write operations require authentication.
*/
Route::prefix('portfolios')->group(function () {
    // Public read endpoints
    Route::get('/', [PortfolioController::class, 'index'])->name('api.portfolios.index');
    Route::get('/{portfolio}', [PortfolioController::class, 'show'])->name('api.portfolios.show');
    Route::get('/{portfolio}/media', [PortfolioMediaController::class, 'index'])->name('api.portfolios.media.index');

    // Authenticated write endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [PortfolioController::class, 'store'])->name('api.portfolios.store');
        Route::put('/{portfolio}', [PortfolioController::class, 'update'])->name('api.portfolios.update');
        Route::delete('/{portfolio}', [PortfolioController::class, 'destroy'])->name('api.portfolios.destroy');
        Route::post('/{portfolio}/publish', [PortfolioController::class, 'publish'])->name('api.portfolios.publish');
        Route::post('/{portfolio}/archive', [PortfolioController::class, 'archive'])->name('api.portfolios.archive');

        // Portfolio Media (write)
        Route::post('/{portfolio}/media', [PortfolioMediaController::class, 'store'])->name('api.portfolios.media.store');
        Route::delete('/{portfolio}/media/{media}', [PortfolioMediaController::class, 'destroy'])->name('api.portfolios.media.destroy');
        Route::put('/{portfolio}/media/reorder', [PortfolioMediaController::class, 'reorder'])->name('api.portfolios.media.reorder');
    });
});

/*
|--------------------------------------------------------------------------
| Portfolio Category Routes (Tenant-scoped)
|--------------------------------------------------------------------------
| Index and show are publicly accessible.
| Write operations require authentication.
*/
Route::prefix('portfolio-categories')->group(function () {
    // Public read endpoints
    Route::get('/', [PortfolioCategoryController::class, 'index'])->name('api.portfolio-categories.index');
    Route::get('/{category}', [PortfolioCategoryController::class, 'show'])->name('api.portfolio-categories.show');

    // Authenticated write endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [PortfolioCategoryController::class, 'store'])->name('api.portfolio-categories.store');
        Route::put('/{category}', [PortfolioCategoryController::class, 'update'])->name('api.portfolio-categories.update');
        Route::delete('/{category}', [PortfolioCategoryController::class, 'destroy'])->name('api.portfolio-categories.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Testimonial Routes (Tenant-scoped)
|--------------------------------------------------------------------------
| Index and show are publicly accessible (auth is optional).
| Write operations require authentication.
*/
Route::prefix('testimonials')->group(function () {
    // Public read endpoints
    Route::get('/', [TestimonialController::class, 'index'])->name('api.testimonials.index');
    Route::get('/{testimonial}', [TestimonialController::class, 'show'])->name('api.testimonials.show');
    Route::get('/{testimonial}/media', [TestimonialMediaController::class, 'index'])->name('api.testimonials.media.index');

    // Authenticated write endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [TestimonialController::class, 'store'])->name('api.testimonials.store');
        Route::put('/{testimonial}', [TestimonialController::class, 'update'])->name('api.testimonials.update');
        Route::delete('/{testimonial}', [TestimonialController::class, 'destroy'])->name('api.testimonials.destroy');
        Route::post('/{testimonial}/publish', [TestimonialController::class, 'publish'])->name('api.testimonials.publish');

        // Testimonial Media (write)
        Route::post('/{testimonial}/media', [TestimonialMediaController::class, 'store'])->name('api.testimonials.media.store');
        Route::delete('/{testimonial}/media/{media}', [TestimonialMediaController::class, 'destroy'])->name('api.testimonials.media.destroy');
    });
});
