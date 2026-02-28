<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Article\Enums\ArticleStatus;
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

test('can list all articles', function () {
    Article::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/articles');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing articles returns empty array when none exist', function () {
    $response = $this->getJson('/api/v1/articles');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

test('can create an article', function () {
    $response = $this->postJson('/api/v1/articles', [
        'title' => 'My First Article',
        'slug' => 'my-first-article',
        'content' => '# Hello World\n\nThis is my first article.',
        'description' => 'A test article',
        'status' => ArticleStatus::Draft->value,
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Article created successfully.')
        ->assertJsonPath('data.title', 'My First Article')
        ->assertJsonPath('data.slug', 'my-first-article')
        ->assertJsonPath('data.status', ArticleStatus::Draft->value)
        ->assertJsonPath('data.user_id', $this->user->id);
});

test('can create an article with auto-generated slug', function () {
    $response = $this->postJson('/api/v1/articles', [
        'title' => 'Auto Slug Article',
        'content' => 'Some content here.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.slug', 'auto-slug-article');
});

test('can create an article in a series', function () {
    $series = ArticleSeries::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson('/api/v1/articles', [
        'title' => 'Series Article',
        'content' => 'Part of a series.',
        'series_id' => $series->id,
        'series_order' => 1,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.series_id', $series->id);
});

test('creating article fails without title', function () {
    $response = $this->postJson('/api/v1/articles', [
        'content' => 'No title provided.',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

test('creating article fails without content', function () {
    $response = $this->postJson('/api/v1/articles', [
        'title' => 'Missing Content',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);
});

test('creating article fails with invalid status', function () {
    $response = $this->postJson('/api/v1/articles', [
        'title' => 'Bad Status',
        'content' => 'Some content.',
        'status' => 'nonexistent-status',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

test('can show an article', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/v1/articles/{$article->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $article->id)
        ->assertJsonPath('data.title', $article->title);
});

test('showing an article increments view count', function () {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'views_count' => 5,
    ]);

    $this->getJson("/api/v1/articles/{$article->id}");

    expect($article->fresh()->views_count)->toBe(6);
});

test('showing a non-existent article returns 404', function () {
    $response = $this->getJson('/api/v1/articles/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

test('can update an article', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/v1/articles/{$article->id}", [
        'title' => 'Updated Title',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Article updated successfully.')
        ->assertJsonPath('data.title', 'Updated Title');
});

test('updating an article creates a revision', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $this->putJson("/api/v1/articles/{$article->id}", [
        'title' => 'Updated Title',
    ]);

    $this->assertDatabaseHas('article_revisions', [
        'article_id' => $article->id,
        'user_id' => $this->user->id,
        'title' => $article->title,
    ]);
});

test('updating a non-existent article returns 404', function () {
    $response = $this->putJson('/api/v1/articles/99999', [
        'title' => 'Updated Title',
    ]);

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete an article', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/articles/{$article->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Article deleted successfully.');

    $this->assertDatabaseMissing('articles', ['id' => $article->id]);
});

test('deleting a non-existent article returns 404', function () {
    $response = $this->deleteJson('/api/v1/articles/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Publish
|--------------------------------------------------------------------------
*/

test('can publish a draft article', function () {
    $article = Article::factory()->draft()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/articles/{$article->id}/publish");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Article published successfully.')
        ->assertJsonPath('data.status', ArticleStatus::Published->value);

    expect($article->fresh()->published_at)->not->toBeNull();
});

test('publishing a non-existent article returns 404', function () {
    $response = $this->postJson('/api/v1/articles/99999/publish');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Archive
|--------------------------------------------------------------------------
*/

test('can archive a published article', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/articles/{$article->id}/archive");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Article archived successfully.')
        ->assertJsonPath('data.status', ArticleStatus::Archived->value);
});

test('archiving a non-existent article returns 404', function () {
    $response = $this->postJson('/api/v1/articles/99999/archive');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot access articles', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/articles');

    $response->assertUnauthorized();
});
