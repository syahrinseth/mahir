<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Models\PortfolioMedia;
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
    PortfolioMedia::factory()->count(3)->create(['portfolio_id' => $portfolio->id]);

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
        ->assertJsonPath('data.file_name', 'screenshot.jpg')
        ->assertJsonPath('data.caption', 'Homepage screenshot');

    Storage::disk('public')->assertExists($response->json('data.file_path'));
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

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete media from a portfolio', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);
    $media = PortfolioMedia::factory()->create(['portfolio_id' => $portfolio->id]);

    $response = $this->deleteJson("/api/v1/portfolios/{$portfolio->id}/media/{$media->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Media deleted successfully.');

    $this->assertDatabaseMissing('portfolio_media', ['id' => $media->id]);
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
    $media1 = PortfolioMedia::factory()->create(['portfolio_id' => $portfolio->id, 'sort_order' => 0]);
    $media2 = PortfolioMedia::factory()->create(['portfolio_id' => $portfolio->id, 'sort_order' => 1]);
    $media3 = PortfolioMedia::factory()->create(['portfolio_id' => $portfolio->id, 'sort_order' => 2]);

    $response = $this->putJson("/api/v1/portfolios/{$portfolio->id}/media/reorder", [
        'media_ids' => [$media3->id, $media1->id, $media2->id],
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Media reordered successfully.');

    expect($media3->fresh()->sort_order)->toBe(0);
    expect($media1->fresh()->sort_order)->toBe(1);
    expect($media2->fresh()->sort_order)->toBe(2);
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
