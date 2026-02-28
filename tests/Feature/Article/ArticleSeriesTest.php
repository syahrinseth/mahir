<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Article\Models\Article;
use App\Modules\Article\Models\ArticleSeries;
use App\Modules\Auth\Models\User;

beforeEach(function () {
    $this->withoutMiddleware(IdentifyTenant::class);
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
});

/*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
*/

test('can list all article series', function () {
    ArticleSeries::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/article-series');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing series returns empty array when none exist', function () {
    $response = $this->getJson('/api/v1/article-series');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

test('can create an article series', function () {
    $response = $this->postJson('/api/v1/article-series', [
        'title' => 'Laravel Fundamentals',
        'slug' => 'laravel-fundamentals',
        'description' => 'A series about Laravel basics.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Series created successfully.')
        ->assertJsonPath('data.title', 'Laravel Fundamentals')
        ->assertJsonPath('data.slug', 'laravel-fundamentals')
        ->assertJsonPath('data.user_id', $this->user->id);
});

test('creating series fails without title', function () {
    $response = $this->postJson('/api/v1/article-series', [
        'slug' => 'missing-title',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

test('creating series fails without slug', function () {
    $response = $this->postJson('/api/v1/article-series', [
        'title' => 'Missing Slug',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

test('creating series fails with duplicate slug', function () {
    ArticleSeries::factory()->create([
        'user_id' => $this->user->id,
        'slug' => 'duplicate-slug',
    ]);

    $response = $this->postJson('/api/v1/article-series', [
        'title' => 'Duplicate Slug',
        'slug' => 'duplicate-slug',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

test('can show an article series', function () {
    $series = ArticleSeries::factory()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/v1/article-series/{$series->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $series->id);
});

test('showing a non-existent series returns 404', function () {
    $response = $this->getJson('/api/v1/article-series/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

test('can update an article series', function () {
    $series = ArticleSeries::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/v1/article-series/{$series->id}", [
        'title' => 'Updated Series Title',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Series updated successfully.')
        ->assertJsonPath('data.title', 'Updated Series Title');
});

test('updating a non-existent series returns 404', function () {
    $response = $this->putJson('/api/v1/article-series/99999', [
        'title' => 'Updated',
    ]);

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete an article series', function () {
    $series = ArticleSeries::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/article-series/{$series->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Series deleted successfully.');

    $this->assertDatabaseMissing('article_series', ['id' => $series->id]);
});

test('deleting a non-existent series returns 404', function () {
    $response = $this->deleteJson('/api/v1/article-series/99999');

    $response->assertNotFound();
});

test('deleting a series does not delete its articles', function () {
    $series = ArticleSeries::factory()->create(['user_id' => $this->user->id]);
    $article = Article::factory()->inSeries($series)->create(['user_id' => $this->user->id]);

    $this->deleteJson("/api/v1/article-series/{$series->id}");

    $this->assertDatabaseHas('articles', ['id' => $article->id]);
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot access article series', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/article-series');

    $response->assertUnauthorized();
});
