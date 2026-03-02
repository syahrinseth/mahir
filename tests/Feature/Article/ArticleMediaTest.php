<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Article\Models\Article;
use App\Modules\Auth\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware(IdentifyTenant::class);
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
    Storage::fake('public');
});

/*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
*/

test('can list media for an article', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);

    $article->addMedia(UploadedFile::fake()->image('photo1.jpg'))->toMediaCollection('gallery');
    $article->addMedia(UploadedFile::fake()->image('photo2.jpg'))->toMediaCollection('gallery');
    $article->addMedia(UploadedFile::fake()->image('photo3.jpg'))->toMediaCollection('gallery');

    $response = $this->getJson("/api/v1/articles/{$article->id}/media");

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing media for non-existent article returns 404', function () {
    $response = $this->getJson('/api/v1/articles/99999/media');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Store (Upload)
|--------------------------------------------------------------------------
*/

test('can upload media to an article', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('screenshot.jpg', 800, 600);

    $response = $this->postJson("/api/v1/articles/{$article->id}/media", [
        'file' => $file,
        'caption' => 'Article screenshot',
        'alt_text' => 'A screenshot of the article',
        'sort_order' => 0,
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Media uploaded successfully.')
        ->assertJsonPath('data.file_name', 'screenshot.jpg');

    $this->assertDatabaseHas('media', [
        'model_type' => Article::class,
        'model_id' => $article->id,
        'collection_name' => 'gallery',
        'file_name' => 'screenshot.jpg',
    ]);
});

test('can upload media to featured collection', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('banner.jpg', 1200, 600);

    $response = $this->postJson("/api/v1/articles/{$article->id}/media", [
        'file' => $file,
        'collection' => 'featured',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.collection_name', 'featured');
});

test('featured collection replaces previous file', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);

    $article->addMedia(UploadedFile::fake()->image('old-banner.jpg'))->toMediaCollection('featured');

    $this->postJson("/api/v1/articles/{$article->id}/media", [
        'file' => UploadedFile::fake()->image('new-banner.jpg'),
        'collection' => 'featured',
    ])->assertCreated();

    $article->refresh();

    expect($article->getMedia('featured'))->toHaveCount(1);
    expect($article->getFirstMedia('featured')->file_name)->toBe('new-banner.jpg');
});

test('uploading media fails without file', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/articles/{$article->id}/media", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

test('uploading media fails with invalid file type', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

    $response = $this->postJson("/api/v1/articles/{$article->id}/media", [
        'file' => $file,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

test('uploading media to non-existent article returns 404', function () {
    $file = UploadedFile::fake()->image('screenshot.jpg');

    $response = $this->postJson('/api/v1/articles/99999/media', [
        'file' => $file,
    ]);

    $response->assertNotFound();
});

test('uploading media stores custom properties', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('screenshot.jpg', 800, 600);

    $this->postJson("/api/v1/articles/{$article->id}/media", [
        'file' => $file,
        'caption' => 'My caption',
        'alt_text' => 'Alt text for image',
        'sort_order' => 5,
    ])->assertCreated();

    $article->refresh();

    $media = $article->getFirstMedia('gallery');

    expect($media->getCustomProperty('caption'))->toBe('My caption');
    expect($media->getCustomProperty('alt_text'))->toBe('Alt text for image');
    expect($media->getCustomProperty('sort_order'))->toBe(5);
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete media from an article', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);

    $media = $article->addMedia(UploadedFile::fake()->image('screenshot.jpg'))
        ->toMediaCollection('gallery');

    $response = $this->deleteJson("/api/v1/articles/{$article->id}/media/{$media->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Media deleted successfully.');

    $this->assertDatabaseMissing('media', ['id' => $media->id]);
});

test('deleting non-existent media returns 404', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/articles/{$article->id}/media/99999");

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Reorder
|--------------------------------------------------------------------------
*/

test('can reorder media for an article', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);

    $media1 = $article->addMedia(UploadedFile::fake()->image('photo1.jpg'))->toMediaCollection('gallery');
    $media2 = $article->addMedia(UploadedFile::fake()->image('photo2.jpg'))->toMediaCollection('gallery');
    $media3 = $article->addMedia(UploadedFile::fake()->image('photo3.jpg'))->toMediaCollection('gallery');

    $response = $this->putJson("/api/v1/articles/{$article->id}/media/reorder", [
        'media_ids' => [$media3->id, $media1->id, $media2->id],
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Media reordered successfully.');

    expect($media3->fresh()->order_column)->toBe(1);
    expect($media1->fresh()->order_column)->toBe(2);
    expect($media2->fresh()->order_column)->toBe(3);
});

test('reordering media for non-existent article returns 404', function () {
    $response = $this->putJson('/api/v1/articles/99999/media/reorder', [
        'media_ids' => [1, 2, 3],
    ]);

    $response->assertNotFound();
});

test('reordering media fails without media_ids', function () {
    $article = Article::factory()->published()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/v1/articles/{$article->id}/media/reorder", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['media_ids']);
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot upload article media', function () {
    $this->app['auth']->forgetGuards();

    $article = Article::factory()->published()->create();
    $file = UploadedFile::fake()->image('screenshot.jpg');

    $response = $this->postJson("/api/v1/articles/{$article->id}/media", [
        'file' => $file,
    ]);

    $response->assertUnauthorized();
});

test('unauthenticated user cannot delete article media', function () {
    $this->app['auth']->forgetGuards();

    $article = Article::factory()->published()->create();
    $media = $article->addMedia(UploadedFile::fake()->image('photo.jpg'))->toMediaCollection('gallery');

    $response = $this->deleteJson("/api/v1/articles/{$article->id}/media/{$media->id}");

    $response->assertUnauthorized();
});

test('unauthenticated user cannot reorder article media', function () {
    $this->app['auth']->forgetGuards();

    $article = Article::factory()->published()->create();

    $response = $this->putJson("/api/v1/articles/{$article->id}/media/reorder", [
        'media_ids' => [1, 2, 3],
    ]);

    $response->assertUnauthorized();
});

/*
|--------------------------------------------------------------------------
| Public Access (Status-Based Visibility)
|--------------------------------------------------------------------------
*/

test('unauthenticated user can list media for a published article', function () {
    $this->app['auth']->forgetGuards();

    $article = Article::factory()->published()->create();

    $article->addMedia(UploadedFile::fake()->image('photo1.jpg'))->toMediaCollection('gallery');
    $article->addMedia(UploadedFile::fake()->image('photo2.jpg'))->toMediaCollection('gallery');

    $response = $this->getJson("/api/v1/articles/{$article->id}/media");

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

test('unauthenticated user cannot list media for a draft article', function () {
    $this->app['auth']->forgetGuards();

    $article = Article::factory()->draft()->create();

    $response = $this->getJson("/api/v1/articles/{$article->id}/media");

    $response->assertNotFound();
});

test('unauthenticated user cannot list media for an archived article', function () {
    $this->app['auth']->forgetGuards();

    $article = Article::factory()->archived()->create();

    $response = $this->getJson("/api/v1/articles/{$article->id}/media");

    $response->assertNotFound();
});

test('authenticated user can list media for a draft article', function () {
    $article = Article::factory()->draft()->create(['user_id' => $this->user->id]);

    $article->addMedia(UploadedFile::fake()->image('photo1.jpg'))->toMediaCollection('gallery');

    $response = $this->getJson("/api/v1/articles/{$article->id}/media");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('authenticated user can list media for an archived article', function () {
    $article = Article::factory()->archived()->create(['user_id' => $this->user->id]);

    $article->addMedia(UploadedFile::fake()->image('photo1.jpg'))->toMediaCollection('gallery');

    $response = $this->getJson("/api/v1/articles/{$article->id}/media");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});
