<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Article\Models\Article;
use App\Modules\Article\Models\ArticleRevision;
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

test('can list revisions for an article', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);
    ArticleRevision::factory()->count(3)->create([
        'article_id' => $article->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson("/api/v1/articles/{$article->id}/revisions");

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing revisions for non-existent article returns 404', function () {
    $response = $this->getJson('/api/v1/articles/99999/revisions');

    $response->assertNotFound();
});

test('listing revisions returns empty array when none exist', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/v1/articles/{$article->id}/revisions");

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

test('can show a specific revision', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);
    $revision = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson("/api/v1/articles/{$article->id}/revisions/{$revision->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $revision->id)
        ->assertJsonPath('data.article_id', $article->id);
});

test('showing revision for wrong article returns 404', function () {
    $article1 = Article::factory()->create(['user_id' => $this->user->id]);
    $article2 = Article::factory()->create(['user_id' => $this->user->id]);
    $revision = ArticleRevision::factory()->create([
        'article_id' => $article2->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson("/api/v1/articles/{$article1->id}/revisions/{$revision->id}");

    $response->assertNotFound();
});

test('showing a non-existent revision returns 404', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/v1/articles/{$article->id}/revisions/99999");

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Restore
|--------------------------------------------------------------------------
*/

test('can restore an article to a previous revision', function () {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Current Title',
        'content' => 'Current content.',
    ]);

    $revision = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'user_id' => $this->user->id,
        'title' => 'Old Title',
        'content' => 'Old content.',
        'description' => 'Old description.',
    ]);

    $response = $this->postJson("/api/v1/articles/{$article->id}/restore-revision/{$revision->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Article restored from revision successfully.')
        ->assertJsonPath('data.title', 'Old Title');

    expect($article->fresh()->title)->toBe('Old Title')
        ->and($article->fresh()->content)->toBe('Old content.');
});

test('restoring creates a new revision with current state', function () {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Before Restore',
    ]);

    $revision = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'user_id' => $this->user->id,
        'title' => 'Old Title',
        'content' => 'Old content.',
    ]);

    $this->postJson("/api/v1/articles/{$article->id}/restore-revision/{$revision->id}");

    $this->assertDatabaseHas('article_revisions', [
        'article_id' => $article->id,
        'title' => 'Before Restore',
    ]);
});

test('restoring with non-existent revision returns 404', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/articles/{$article->id}/restore-revision/99999");

    $response->assertNotFound();
});

test('restoring revision from wrong article returns 404', function () {
    $article1 = Article::factory()->create(['user_id' => $this->user->id]);
    $article2 = Article::factory()->create(['user_id' => $this->user->id]);
    $revision = ArticleRevision::factory()->create([
        'article_id' => $article2->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->postJson("/api/v1/articles/{$article1->id}/restore-revision/{$revision->id}");

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot access revisions', function () {
    $this->app['auth']->forgetGuards();

    $article = Article::factory()->create();

    $response = $this->getJson("/api/v1/articles/{$article->id}/revisions");

    $response->assertUnauthorized();
});
