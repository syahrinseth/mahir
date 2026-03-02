<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Models\Testimonial;

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

test('can list all testimonials', function () {
    Testimonial::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/testimonials');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing testimonials returns empty array when none exist', function () {
    $response = $this->getJson('/api/v1/testimonials');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

test('can create a testimonial', function () {
    $response = $this->postJson('/api/v1/testimonials', [
        'client_name' => 'Jane Doe',
        'content' => 'Outstanding work on our project.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Testimonial created successfully.')
        ->assertJsonPath('data.client_name', 'Jane Doe')
        ->assertJsonPath('data.content', 'Outstanding work on our project.')
        ->assertJsonPath('data.user_id', $this->user->id);
});

test('can create a testimonial with full client details', function () {
    $response = $this->postJson('/api/v1/testimonials', [
        'client_name' => 'John Smith',
        'content' => 'Great experience.',
        'client_position' => 'CEO',
        'client_company' => 'Acme Corp',
        'rating' => 5,
        'is_featured' => true,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.client_name', 'John Smith')
        ->assertJsonPath('data.client_position', 'CEO')
        ->assertJsonPath('data.client_company', 'Acme Corp')
        ->assertJsonPath('data.rating', 5)
        ->assertJsonPath('data.is_featured', true);
});

test('can create a testimonial linked to a portfolio', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson('/api/v1/testimonials', [
        'client_name' => 'Sarah Connor',
        'content' => 'Loved the portfolio work.',
        'portfolio_id' => $portfolio->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.portfolio_id', $portfolio->id);
});

test('creating testimonial fails without client_name', function () {
    $response = $this->postJson('/api/v1/testimonials', [
        'content' => 'Some review.',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['client_name']);
});

test('creating testimonial fails without content', function () {
    $response = $this->postJson('/api/v1/testimonials', [
        'client_name' => 'Jane Doe',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);
});

test('creating testimonial fails with invalid rating below 1', function () {
    $response = $this->postJson('/api/v1/testimonials', [
        'client_name' => 'Jane Doe',
        'content' => 'Some review.',
        'rating' => 0,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['rating']);
});

test('creating testimonial fails with invalid rating above 5', function () {
    $response = $this->postJson('/api/v1/testimonials', [
        'client_name' => 'Jane Doe',
        'content' => 'Some review.',
        'rating' => 6,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['rating']);
});

test('creating testimonial fails with non-existent portfolio_id', function () {
    $response = $this->postJson('/api/v1/testimonials', [
        'client_name' => 'Jane Doe',
        'content' => 'Some review.',
        'portfolio_id' => 99999,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['portfolio_id']);
});

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

test('can show a testimonial', function () {
    $testimonial = Testimonial::factory()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/v1/testimonials/{$testimonial->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $testimonial->id)
        ->assertJsonPath('data.client_name', $testimonial->client_name);
});

test('showing a non-existent testimonial returns 404', function () {
    $response = $this->getJson('/api/v1/testimonials/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

test('can update a testimonial', function () {
    $testimonial = Testimonial::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/v1/testimonials/{$testimonial->id}", [
        'client_name' => 'Updated Name',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Testimonial updated successfully.')
        ->assertJsonPath('data.client_name', 'Updated Name');
});

test('can update testimonial rating', function () {
    $testimonial = Testimonial::factory()->create([
        'user_id' => $this->user->id,
        'rating' => 3,
    ]);

    $response = $this->putJson("/api/v1/testimonials/{$testimonial->id}", [
        'rating' => 5,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.rating', 5);
});

test('can update is_featured to false', function () {
    $testimonial = Testimonial::factory()->create([
        'user_id' => $this->user->id,
        'is_featured' => true,
    ]);

    $response = $this->putJson("/api/v1/testimonials/{$testimonial->id}", [
        'is_featured' => false,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.is_featured', false);

    expect($testimonial->fresh()->is_featured)->toBeFalse();
});

test('can update sort_order to 0', function () {
    $testimonial = Testimonial::factory()->create([
        'user_id' => $this->user->id,
        'sort_order' => 5,
    ]);

    $response = $this->putJson("/api/v1/testimonials/{$testimonial->id}", [
        'sort_order' => 0,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.sort_order', 0);

    expect($testimonial->fresh()->sort_order)->toBe(0);
});

test('can update multiple fields including falsy values', function () {
    $testimonial = Testimonial::factory()->create([
        'user_id' => $this->user->id,
        'is_featured' => true,
        'sort_order' => 5,
        'rating' => 5,
    ]);

    $response = $this->putJson("/api/v1/testimonials/{$testimonial->id}", [
        'is_featured' => false,
        'sort_order' => 0,
        'rating' => 1,
        'client_name' => 'New Name',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.is_featured', false)
        ->assertJsonPath('data.sort_order', 0)
        ->assertJsonPath('data.rating', 1)
        ->assertJsonPath('data.client_name', 'New Name');

    $fresh = $testimonial->fresh();
    expect($fresh->is_featured)->toBeFalse()
        ->and($fresh->sort_order)->toBe(0)
        ->and($fresh->rating)->toBe(1)
        ->and($fresh->client_name)->toBe('New Name');
});

test('updating a non-existent testimonial returns 404', function () {
    $response = $this->putJson('/api/v1/testimonials/99999', [
        'client_name' => 'Updated Name',
    ]);

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete a testimonial', function () {
    $testimonial = Testimonial::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/testimonials/{$testimonial->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Testimonial deleted successfully.');

    $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
});

test('deleting a non-existent testimonial returns 404', function () {
    $response = $this->deleteJson('/api/v1/testimonials/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Publish
|--------------------------------------------------------------------------
*/

test('can publish a draft testimonial', function () {
    $testimonial = Testimonial::factory()->draft()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/testimonials/{$testimonial->id}/publish");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Testimonial published successfully.');

    expect($testimonial->fresh()->published_at)->not->toBeNull();
});

test('publishing a non-existent testimonial returns 404', function () {
    $response = $this->postJson('/api/v1/testimonials/99999/publish');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot create testimonials', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->postJson('/api/v1/testimonials', [
        'client_name' => 'Test',
        'content' => 'Test review.',
    ]);

    $response->assertUnauthorized();
});

test('unauthenticated user cannot update testimonials', function () {
    $this->app['auth']->forgetGuards();

    $testimonial = Testimonial::factory()->create();

    $response = $this->putJson("/api/v1/testimonials/{$testimonial->id}", [
        'client_name' => 'Updated',
    ]);

    $response->assertUnauthorized();
});

test('unauthenticated user cannot delete testimonials', function () {
    $this->app['auth']->forgetGuards();

    $testimonial = Testimonial::factory()->create();

    $response = $this->deleteJson("/api/v1/testimonials/{$testimonial->id}");

    $response->assertUnauthorized();
});

/*
|--------------------------------------------------------------------------
| Public Access
|--------------------------------------------------------------------------
*/

test('unauthenticated user can list published testimonials', function () {
    $this->app['auth']->forgetGuards();

    Testimonial::factory()->published()->count(2)->create();
    Testimonial::factory()->draft()->count(1)->create();

    $response = $this->getJson('/api/v1/testimonials');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

test('unauthenticated user can view a published testimonial', function () {
    $this->app['auth']->forgetGuards();

    $testimonial = Testimonial::factory()->published()->create();

    $response = $this->getJson("/api/v1/testimonials/{$testimonial->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $testimonial->id);
});

test('unauthenticated user cannot view a draft testimonial', function () {
    $this->app['auth']->forgetGuards();

    $testimonial = Testimonial::factory()->draft()->create();

    $response = $this->getJson("/api/v1/testimonials/{$testimonial->id}");

    $response->assertNotFound();
});

test('authenticated user can list all testimonials regardless of publish status', function () {
    Testimonial::factory()->published()->count(2)->create(['user_id' => $this->user->id]);
    Testimonial::factory()->draft()->count(1)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/testimonials');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('authenticated user can view a draft testimonial', function () {
    $testimonial = Testimonial::factory()->draft()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/v1/testimonials/{$testimonial->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $testimonial->id);
});
