<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\Testimonial;
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

test('can list media for a testimonial', function () {
    $testimonial = Testimonial::factory()->create(['user_id' => $this->user->id]);

    $testimonial->addMedia(UploadedFile::fake()->image('headshot.jpg'))->toMediaCollection('featured');

    $response = $this->getJson("/api/v1/testimonials/{$testimonial->id}/media");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('listing media for non-existent testimonial returns 404', function () {
    $response = $this->getJson('/api/v1/testimonials/99999/media');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Store (Upload)
|--------------------------------------------------------------------------
*/

test('can upload media to a testimonial', function () {
    $testimonial = Testimonial::factory()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('headshot.jpg', 300, 300);

    $response = $this->postJson("/api/v1/testimonials/{$testimonial->id}/media", [
        'file' => $file,
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Media uploaded successfully.')
        ->assertJsonPath('data.file_name', 'headshot.jpg');

    $this->assertDatabaseHas('media', [
        'model_type' => Testimonial::class,
        'model_id' => $testimonial->id,
        'collection_name' => 'featured',
        'file_name' => 'headshot.jpg',
    ]);
});

test('featured collection replaces previous file for testimonial', function () {
    $testimonial = Testimonial::factory()->create(['user_id' => $this->user->id]);

    $testimonial->addMedia(UploadedFile::fake()->image('old-headshot.jpg'))->toMediaCollection('featured');

    $this->postJson("/api/v1/testimonials/{$testimonial->id}/media", [
        'file' => UploadedFile::fake()->image('new-headshot.jpg'),
    ]);

    expect($testimonial->getMedia('featured'))->toHaveCount(1);
    expect($testimonial->getFirstMedia('featured')->file_name)->toBe('new-headshot.jpg');
});

test('uploading media fails without file', function () {
    $testimonial = Testimonial::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/testimonials/{$testimonial->id}/media", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

test('uploading media fails with invalid file type', function () {
    $testimonial = Testimonial::factory()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->postJson("/api/v1/testimonials/{$testimonial->id}/media", [
        'file' => $file,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

test('uploading media to non-existent testimonial returns 404', function () {
    $file = UploadedFile::fake()->image('headshot.jpg');

    $response = $this->postJson('/api/v1/testimonials/99999/media', [
        'file' => $file,
    ]);

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete media from a testimonial', function () {
    $testimonial = Testimonial::factory()->create(['user_id' => $this->user->id]);

    $media = $testimonial->addMedia(UploadedFile::fake()->image('headshot.jpg'))
        ->toMediaCollection('featured');

    $response = $this->deleteJson("/api/v1/testimonials/{$testimonial->id}/media/{$media->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Media deleted successfully.');

    $this->assertDatabaseMissing('media', ['id' => $media->id]);
});

test('deleting non-existent media returns 404', function () {
    $testimonial = Testimonial::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/testimonials/{$testimonial->id}/media/99999");

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot upload testimonial media', function () {
    $this->app['auth']->forgetGuards();

    $testimonial = Testimonial::factory()->published()->create();
    $file = UploadedFile::fake()->image('headshot.jpg');

    $response = $this->postJson("/api/v1/testimonials/{$testimonial->id}/media", [
        'file' => $file,
    ]);

    $response->assertUnauthorized();
});

test('unauthenticated user cannot delete testimonial media', function () {
    $this->app['auth']->forgetGuards();

    $testimonial = Testimonial::factory()->published()->create();
    $media = $testimonial->addMedia(UploadedFile::fake()->image('headshot.jpg'))->toMediaCollection('featured');

    $response = $this->deleteJson("/api/v1/testimonials/{$testimonial->id}/media/{$media->id}");

    $response->assertUnauthorized();
});

/*
|--------------------------------------------------------------------------
| Public Access
|--------------------------------------------------------------------------
*/

test('unauthenticated user can list media for a published testimonial', function () {
    $this->app['auth']->forgetGuards();

    $testimonial = Testimonial::factory()->published()->create();

    $testimonial->addMedia(UploadedFile::fake()->image('headshot.jpg'))->toMediaCollection('featured');

    $response = $this->getJson("/api/v1/testimonials/{$testimonial->id}/media");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('unauthenticated user cannot list media for a draft testimonial', function () {
    $this->app['auth']->forgetGuards();

    $testimonial = Testimonial::factory()->draft()->create();

    $response = $this->getJson("/api/v1/testimonials/{$testimonial->id}/media");

    $response->assertNotFound();
});
