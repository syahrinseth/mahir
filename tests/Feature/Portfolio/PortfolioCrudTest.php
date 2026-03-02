<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Enums\PortfolioStatus;
use App\Modules\Portfolio\Models\Portfolio;
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

test('can list all portfolios', function () {
    Portfolio::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/portfolios');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing portfolios returns empty array when none exist', function () {
    $response = $this->getJson('/api/v1/portfolios');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

test('can create a portfolio', function () {
    $response = $this->postJson('/api/v1/portfolios', [
        'title' => 'My First Portfolio',
        'slug' => 'my-first-portfolio',
        'description' => 'A showcase of my best work.',
        'status' => PortfolioStatus::Draft->value,
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Portfolio created successfully.')
        ->assertJsonPath('data.title', 'My First Portfolio')
        ->assertJsonPath('data.slug', 'my-first-portfolio')
        ->assertJsonPath('data.status', PortfolioStatus::Draft->value)
        ->assertJsonPath('data.user_id', $this->user->id);
});

test('can create a portfolio with auto-generated slug', function () {
    $response = $this->postJson('/api/v1/portfolios', [
        'title' => 'Auto Slug Portfolio',
        'description' => 'Description for auto slug.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.slug', 'auto-slug-portfolio');
});

test('can create a portfolio in a category', function () {
    $category = PortfolioCategory::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson('/api/v1/portfolios', [
        'title' => 'Categorized Portfolio',
        'description' => 'Portfolio in a category.',
        'category_id' => $category->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.category_id', $category->id);
});

test('can create a portfolio with technologies', function () {
    $response = $this->postJson('/api/v1/portfolios', [
        'title' => 'Tech Portfolio',
        'description' => 'Portfolio with technologies.',
        'technologies' => ['Laravel', 'React', 'Tailwind CSS'],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.technologies', ['Laravel', 'React', 'Tailwind CSS']);
});

test('can create a portfolio with client info', function () {
    $response = $this->postJson('/api/v1/portfolios', [
        'title' => 'Client Portfolio',
        'description' => 'Portfolio for a client.',
        'client_name' => 'Acme Corp',
        'project_url' => 'https://acme.example.com',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.client_name', 'Acme Corp')
        ->assertJsonPath('data.project_url', 'https://acme.example.com');
});

test('creating portfolio fails without title', function () {
    $response = $this->postJson('/api/v1/portfolios', [
        'description' => 'No title provided.',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

test('creating portfolio fails without description', function () {
    $response = $this->postJson('/api/v1/portfolios', [
        'title' => 'Missing Description',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['description']);
});

test('creating portfolio fails with invalid status', function () {
    $response = $this->postJson('/api/v1/portfolios', [
        'title' => 'Bad Status',
        'description' => 'Some description.',
        'status' => 'nonexistent-status',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('creating portfolio fails with invalid project url', function () {
    $response = $this->postJson('/api/v1/portfolios', [
        'title' => 'Bad URL',
        'description' => 'Some description.',
        'project_url' => 'not-a-url',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['project_url']);
});

test('creating portfolio fails with non-existent category_id', function () {
    $response = $this->postJson('/api/v1/portfolios', [
        'title' => 'Bad Category',
        'description' => 'Some description.',
        'category_id' => 99999,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

test('updating portfolio fails with non-existent category_id', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/v1/portfolios/{$portfolio->id}", [
        'category_id' => 99999,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

test('can show a portfolio', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/v1/portfolios/{$portfolio->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $portfolio->id)
        ->assertJsonPath('data.title', $portfolio->title);
});

test('showing a non-existent portfolio returns 404', function () {
    $response = $this->getJson('/api/v1/portfolios/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

test('can update a portfolio', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/v1/portfolios/{$portfolio->id}", [
        'title' => 'Updated Title',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Portfolio updated successfully.')
        ->assertJsonPath('data.title', 'Updated Title');
});

test('updating other fields does not wipe category_id', function () {
    $category = PortfolioCategory::factory()->create(['user_id' => $this->user->id]);
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id, 'category_id' => $category->id]);

    // Update only the title — category_id should remain untouched
    $this->putJson("/api/v1/portfolios/{$portfolio->id}", [
        'title' => 'New Title Only',
    ])->assertSuccessful();

    expect($portfolio->fresh()->category_id)->toBe($category->id);
});

test('can update portfolio category_id', function () {
    $category = PortfolioCategory::factory()->create(['user_id' => $this->user->id]);
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id, 'category_id' => null]);

    $response = $this->putJson("/api/v1/portfolios/{$portfolio->id}", [
        'category_id' => $category->id,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.category_id', $category->id);

    expect($portfolio->fresh()->category_id)->toBe($category->id);
});

test('can change portfolio to a different category', function () {
    $categoryA = PortfolioCategory::factory()->create(['user_id' => $this->user->id]);
    $categoryB = PortfolioCategory::factory()->create(['user_id' => $this->user->id]);
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id, 'category_id' => $categoryA->id]);

    $response = $this->putJson("/api/v1/portfolios/{$portfolio->id}", [
        'category_id' => $categoryB->id,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.category_id', $categoryB->id);

    expect($portfolio->fresh()->category_id)->toBe($categoryB->id);
});

test('can clear portfolio category_id by setting it to null', function () {
    $category = PortfolioCategory::factory()->create(['user_id' => $this->user->id]);
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id, 'category_id' => $category->id]);

    $response = $this->putJson("/api/v1/portfolios/{$portfolio->id}", [
        'category_id' => null,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.category_id', null);

    expect($portfolio->fresh()->category_id)->toBeNull();
});

test('updating a non-existent portfolio returns 404', function () {
    $response = $this->putJson('/api/v1/portfolios/99999', [
        'title' => 'Updated Title',
    ]);

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete a portfolio', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/v1/portfolios/{$portfolio->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Portfolio deleted successfully.');

    $this->assertDatabaseMissing('portfolios', ['id' => $portfolio->id]);
});

test('deleting a non-existent portfolio returns 404', function () {
    $response = $this->deleteJson('/api/v1/portfolios/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Publish
|--------------------------------------------------------------------------
*/

test('can publish a draft portfolio', function () {
    $portfolio = Portfolio::factory()->draft()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/portfolios/{$portfolio->id}/publish");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Portfolio published successfully.')
        ->assertJsonPath('data.status', PortfolioStatus::Published->value);

    expect($portfolio->fresh()->published_at)->not->toBeNull();
});

test('publishing a non-existent portfolio returns 404', function () {
    $response = $this->postJson('/api/v1/portfolios/99999/publish');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Archive
|--------------------------------------------------------------------------
*/

test('can archive a published portfolio', function () {
    $portfolio = Portfolio::factory()->published()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/v1/portfolios/{$portfolio->id}/archive");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Portfolio archived successfully.')
        ->assertJsonPath('data.status', PortfolioStatus::Archived->value);
});

test('archiving a non-existent portfolio returns 404', function () {
    $response = $this->postJson('/api/v1/portfolios/99999/archive');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot create portfolios', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->postJson('/api/v1/portfolios', [
        'title' => 'Test',
        'description' => 'Test description.',
    ]);

    $response->assertUnauthorized();
});

test('unauthenticated user cannot update portfolios', function () {
    $this->app['auth']->forgetGuards();

    $portfolio = Portfolio::factory()->create();

    $response = $this->putJson("/api/v1/portfolios/{$portfolio->id}", [
        'title' => 'Updated',
    ]);

    $response->assertUnauthorized();
});

test('unauthenticated user cannot delete portfolios', function () {
    $this->app['auth']->forgetGuards();

    $portfolio = Portfolio::factory()->create();

    $response = $this->deleteJson("/api/v1/portfolios/{$portfolio->id}");

    $response->assertUnauthorized();
});

/*
|--------------------------------------------------------------------------
| Public Access
|--------------------------------------------------------------------------
*/

test('unauthenticated user can list published portfolios', function () {
    $this->app['auth']->forgetGuards();

    Portfolio::factory()->published()->count(2)->create();
    Portfolio::factory()->draft()->count(1)->create();
    Portfolio::factory()->archived()->count(1)->create();

    $response = $this->getJson('/api/v1/portfolios');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

test('unauthenticated user can view a published portfolio', function () {
    $this->app['auth']->forgetGuards();

    $portfolio = Portfolio::factory()->published()->create();

    $response = $this->getJson("/api/v1/portfolios/{$portfolio->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $portfolio->id);
});

test('unauthenticated user cannot view a draft portfolio', function () {
    $this->app['auth']->forgetGuards();

    $portfolio = Portfolio::factory()->draft()->create();

    $response = $this->getJson("/api/v1/portfolios/{$portfolio->id}");

    $response->assertNotFound();
});

test('unauthenticated user cannot view an archived portfolio', function () {
    $this->app['auth']->forgetGuards();

    $portfolio = Portfolio::factory()->archived()->create();

    $response = $this->getJson("/api/v1/portfolios/{$portfolio->id}");

    $response->assertNotFound();
});

test('authenticated user can list all portfolios regardless of status', function () {
    Portfolio::factory()->published()->count(2)->create(['user_id' => $this->user->id]);
    Portfolio::factory()->draft()->count(1)->create(['user_id' => $this->user->id]);
    Portfolio::factory()->archived()->count(1)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/v1/portfolios');

    $response->assertSuccessful()
        ->assertJsonCount(4, 'data');
});

test('authenticated user can view a draft portfolio', function () {
    $portfolio = Portfolio::factory()->draft()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/v1/portfolios/{$portfolio->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $portfolio->id);
});
