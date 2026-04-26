<?php

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Filament\Resources\Portfolios\Pages\CreatePortfolio;
use App\Modules\Portfolio\Filament\Resources\Portfolios\Pages\EditPortfolio;
use App\Modules\Portfolio\Filament\Resources\Portfolios\Pages\ListPortfolios;
use App\Modules\Portfolio\Models\Portfolio;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $user = User::factory()->create(['is_active' => true]);
    $this->user = $user;
    $this->actingAs($user, 'tenant');

    Filament::setCurrentPanel(
        Filament::getPanel('tenant'),
    );
});

/*
|--------------------------------------------------------------------------
| List Page
|--------------------------------------------------------------------------
*/

test('can load the list portfolios page', function () {
    Livewire::test(ListPortfolios::class)
        ->assertOk();
});

test('lists portfolios', function () {
    $portfolios = Portfolio::factory()->count(3)->create();

    Livewire::test(ListPortfolios::class)
        ->assertCanSeeTableRecords($portfolios);
});

/*
|--------------------------------------------------------------------------
| Create Page
|--------------------------------------------------------------------------
*/

test('can load the create portfolio page', function () {
    Livewire::test(CreatePortfolio::class)
        ->assertOk();
});

test('validates required fields on create', function (array $data, array $errors) {
    Livewire::test(CreatePortfolio::class)
        ->fillForm([
            'title' => 'Test Portfolio',
            'slug' => 'test-portfolio',
            'description' => 'A portfolio description.',
            'status' => 'draft',
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`title` is required' => [['title' => null], ['title' => 'required']],
    '`slug` is required' => [['slug' => null], ['slug' => 'required']],
    '`description` is required' => [['description' => null], ['description' => 'required']],
    '`status` is required' => [['status' => null], ['status' => 'required']],
]);

test('can create a portfolio', function () {
    Livewire::test(CreatePortfolio::class)
        ->fillForm([
            'title' => 'My Portfolio',
            'slug' => 'my-portfolio',
            'description' => 'Portfolio description.',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Portfolio::class, [
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
    ]);
});

/*
|--------------------------------------------------------------------------
| Edit Page
|--------------------------------------------------------------------------
*/

test('can load the edit portfolio page', function () {
    $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(EditPortfolio::class, ['record' => $portfolio->getRouteKey()])
        ->assertOk();
});

test('can update a portfolio title', function () {
    $portfolio = Portfolio::factory()->create(['title' => 'Old Title', 'user_id' => $this->user->id]);

    Livewire::test(EditPortfolio::class, ['record' => $portfolio->getRouteKey()])
        ->fillForm(['title' => 'New Title'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Portfolio::class, [
        'id' => $portfolio->id,
        'title' => 'New Title',
    ]);
});
