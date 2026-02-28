<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Article\Models\Article;
use App\Modules\Article\Models\ArticleComment;
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

test('can list comments for an article', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);
    ArticleComment::factory()->count(3)->create([
        'article_id' => $article->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson("/api/v1/articles/{$article->id}/comments");

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing comments for non-existent article returns 404', function () {
    $response = $this->getJson('/api/v1/articles/99999/comments');

    $response->assertNotFound();
});

test('listing comments returns empty array when none exist', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/v1/articles/{$article->id}/comments");

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

test('can add a comment to an article', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/articles/{$article->id}/comments", [
        'content' => 'Great article!',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Comment added successfully.')
        ->assertJsonPath('data.content', 'Great article!')
        ->assertJsonPath('data.user_id', $this->user->id)
        ->assertJsonPath('data.article_id', $article->id);
});

test('new comments default to unapproved', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/articles/{$article->id}/comments", [
        'content' => 'Pending comment.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.is_approved', false);
});

test('adding comment to non-existent article returns 404', function () {
    $response = $this->postJson('/api/v1/articles/99999/comments', [
        'content' => 'Some comment.',
    ]);

    $response->assertNotFound();
});

test('adding comment fails without content', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/articles/{$article->id}/comments", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);
});

test('adding comment fails when content exceeds max length', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/articles/{$article->id}/comments", [
        'content' => str_repeat('a', 5001),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete own comment', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);
    $comment = ArticleComment::factory()->create([
        'article_id' => $article->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->deleteJson("/api/v1/articles/{$article->id}/comments/{$comment->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Comment deleted successfully.');

    $this->assertDatabaseMissing('article_comments', ['id' => $comment->id]);
});

test('deleting a non-existent comment returns 404', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/articles/{$article->id}/comments/99999");

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot access comments', function () {
    $this->app['auth']->forgetGuards();

    $article = Article::factory()->create();

    $response = $this->getJson("/api/v1/articles/{$article->id}/comments");

    $response->assertUnauthorized();
});
