<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\Portfolio;
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

test('can list media for a portfolio', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $portfolio->addMedia(UploadedFile::fake()->image('photo1.jpg'))->toMediaCollection('gallery');
    $portfolio->addMedia(UploadedFile::fake()->image('photo2.jpg'))->toMediaCollection('gallery');
    $portfolio->addMedia(UploadedFile::fake()->image('photo3.jpg'))->toMediaCollection('gallery');

    $response = $this->getJson("/api/v1/portfolios/{$portfolio->id}/media");

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing media for non-existent portfolio returns 404', function () {
    $response = $this->getJson('/api/v1/portfolios/99999/media');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Store (Upload)
|--------------------------------------------------------------------------
*/

test('can upload media to a portfolio', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('screenshot.jpg', 800, 600);

    $response = $this->postJson("/api/v1/portfolios/{$portfolio->id}/media", [
        'file' => $file,
        'caption' => 'Homepage screenshot',
        'sort_order' => 0,
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Media uploaded successfully.')
        ->assertJsonPath('data.file_name', 'screenshot.jpg');

    $this->assertDatabaseHas('media', [
        'model_type' => Portfolio::class,
        'model_id' => $portfolio->id,
        'collection_name' => 'gallery',
        'file_name' => 'screenshot.jpg',
    ]);
});

test('can upload media to featured collection', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('banner.jpg', 1200, 600);

    $response = $this->postJson("/api/v1/portfolios/{$portfolio->id}/media", [
        'file' => $file,
        'collection' => 'featured',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.collection_name', 'featured');
});

test('featured collection replaces previous file', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $portfolio->addMedia(UploadedFile::fake()->image('old-banner.jpg'))->toMediaCollection('featured');

    $this->postJson("/api/v1/portfolios/{$portfolio->id}/media", [
        'file' => UploadedFile::fake()->image('new-banner.jpg'),
        'collection' => 'featured',
    ]);

    expect($portfolio->getMedia('featured'))->toHaveCount(1);
    expect($portfolio->getFirstMedia('featured')->file_name)->toBe('new-banner.jpg');
});

test('uploading media fails without file', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/portfolios/{$portfolio->id}/media", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

test('uploading media fails with invalid file type', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

    $response = $this->postJson("/api/v1/portfolios/{$portfolio->id}/media", [
        'file' => $file,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

test('uploading media to non-existent portfolio returns 404', function () {
    $file = UploadedFile::fake()->image('screenshot.jpg');

    $response = $this->postJson('/api/v1/portfolios/99999/media', [
        'file' => $file,
    ]);

    $response->assertNotFound();
});

test('uploading media stores custom properties', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('screenshot.jpg', 800, 600);

    $this->postJson("/api/v1/portfolios/{$portfolio->id}/media", [
        'file' => $file,
        'caption' => 'My caption',
        'sort_order' => 5,
    ]);

    $media = $portfolio->getFirstMedia('gallery');

    expect($media->getCustomProperty('caption'))->toBe('My caption');
    expect($media->getCustomProperty('sort_order'))->toBe(5);
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete media from a portfolio', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $media = $portfolio->addMedia(UploadedFile::fake()->image('screenshot.jpg'))
        ->toMediaCollection('gallery');

    $response = $this->deleteJson("/api/v1/portfolios/{$portfolio->id}/media/{$media->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Media deleted successfully.');

    $this->assertDatabaseMissing('media', ['id' => $media->id]);
});

test('deleting non-existent media returns 404', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/portfolios/{$portfolio->id}/media/99999");

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Reorder
|--------------------------------------------------------------------------
*/

test('can reorder media for a portfolio', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $media1 = $portfolio->addMedia(UploadedFile::fake()->image('photo1.jpg'))->toMediaCollection('gallery');
    $media2 = $portfolio->addMedia(UploadedFile::fake()->image('photo2.jpg'))->toMediaCollection('gallery');
    $media3 = $portfolio->addMedia(UploadedFile::fake()->image('photo3.jpg'))->toMediaCollection('gallery');

    $response = $this->putJson("/api/v1/portfolios/{$portfolio->id}/media/reorder", [
        'media_ids' => [$media3->id, $media1->id, $media2->id],
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Media reordered successfully.');

    expect($media3->fresh()->order_column)->toBe(1);
    expect($media1->fresh()->order_column)->toBe(2);
    expect($media2->fresh()->order_column)->toBe(3);
});

test('reordering media for non-existent portfolio returns 404', function () {
    $response = $this->putJson('/api/v1/portfolios/99999/media/reorder', [
        'media_ids' => [1, 2, 3],
    ]);

    $response->assertNotFound();
});

test('reordering media fails without media_ids', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/v1/portfolios/{$portfolio->id}/media/reorder", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['media_ids']);
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot access portfolio media', function () {
    $this->app['auth']->forgetGuards();

    $portfolio = Portfolio::factory()->create();

    $response = $this->getJson("/api/v1/portfolios/{$portfolio->id}/media");

    $response->assertUnauthorized();
});
