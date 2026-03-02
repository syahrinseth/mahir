<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\PortfolioCategory;

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

test('can list all portfolio categories', function () {
    PortfolioCategory::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/portfolio-categories');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing categories returns empty array when none exist', function () {
    $response = $this->getJson('/api/v1/portfolio-categories');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

test('can create a portfolio category', function () {
    $response = $this->postJson('/api/v1/portfolio-categories', [
        'name' => 'Web Development',
        'slug' => 'web-development',
        'description' => 'Full-stack web application projects.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Portfolio category created successfully.')
        ->assertJsonPath('data.name', 'Web Development')
        ->assertJsonPath('data.slug', 'web-development');
});

test('creating category fails without name', function () {
    $response = $this->postJson('/api/v1/portfolio-categories', [
        'slug' => 'some-slug',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('creating category fails without slug', function () {
    $response = $this->postJson('/api/v1/portfolio-categories', [
        'name' => 'Missing Slug',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

test('can show a portfolio category', function () {
    $category = PortfolioCategory::factory()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/v1/portfolio-categories/{$category->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $category->id)
        ->assertJsonPath('data.name', $category->name);
});

test('showing a non-existent category returns 404', function () {
    $response = $this->getJson('/api/v1/portfolio-categories/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

test('can update a portfolio category', function () {
    $category = PortfolioCategory::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/v1/portfolio-categories/{$category->id}", [
        'name' => 'Updated Category',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Portfolio category updated successfully.')
        ->assertJsonPath('data.name', 'Updated Category');
});

test('updating a non-existent category returns 404', function () {
    $response = $this->putJson('/api/v1/portfolio-categories/99999', [
        'name' => 'Updated Category',
    ]);

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete a portfolio category', function () {
    $category = PortfolioCategory::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/portfolio-categories/{$category->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Portfolio category deleted successfully.');

    $this->assertDatabaseMissing('portfolio_categories', ['id' => $category->id]);
});

test('deleting a non-existent category returns 404', function () {
    $response = $this->deleteJson('/api/v1/portfolio-categories/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot create portfolio categories', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->postJson('/api/v1/portfolio-categories', [
        'name' => 'Test',
        'slug' => 'test',
    ]);

    $response->assertUnauthorized();
});

test('unauthenticated user cannot update portfolio categories', function () {
    $this->app['auth']->forgetGuards();

    $category = PortfolioCategory::factory()->create();

    $response = $this->putJson("/api/v1/portfolio-categories/{$category->id}", [
        'name' => 'Updated',
    ]);

    $response->assertUnauthorized();
});

test('unauthenticated user cannot delete portfolio categories', function () {
    $this->app['auth']->forgetGuards();

    $category = PortfolioCategory::factory()->create();

    $response = $this->deleteJson("/api/v1/portfolio-categories/{$category->id}");

    $response->assertUnauthorized();
});

/*
|--------------------------------------------------------------------------
| Public Access
|--------------------------------------------------------------------------
*/

test('unauthenticated user can list portfolio categories', function () {
    $this->app['auth']->forgetGuards();

    PortfolioCategory::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/portfolio-categories');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('unauthenticated user can view a portfolio category', function () {
    $this->app['auth']->forgetGuards();

    $category = PortfolioCategory::factory()->create();

    $response = $this->getJson("/api/v1/portfolio-categories/{$category->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $category->id);
});
