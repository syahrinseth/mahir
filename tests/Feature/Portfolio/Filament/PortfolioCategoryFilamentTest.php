<?php

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Filament\Resources\PortfolioCategories\Pages\CreatePortfolioCategory;
use App\Modules\Portfolio\Filament\Resources\PortfolioCategories\Pages\EditPortfolioCategory;
use App\Modules\Portfolio\Filament\Resources\PortfolioCategories\Pages\ListPortfolioCategories;
use App\Modules\Portfolio\Models\PortfolioCategory;
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

test('can load the list portfolio categories page', function () {
    Livewire::test(ListPortfolioCategories::class)
        ->assertOk();
});

test('lists portfolio categories', function () {
    $categories = PortfolioCategory::factory()->count(3)->create();

    Livewire::test(ListPortfolioCategories::class)
        ->assertCanSeeTableRecords($categories);
});

/*
|--------------------------------------------------------------------------
| Create Page
|--------------------------------------------------------------------------
*/

test('can load the create portfolio category page', function () {
    Livewire::test(CreatePortfolioCategory::class)
        ->assertOk();
});

test('validates required fields on create', function (array $data, array $errors) {
    Livewire::test(CreatePortfolioCategory::class)
        ->fillForm(['name' => 'Test Category', 'slug' => 'test-category', ...$data])
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`slug` is required' => [['slug' => null], ['slug' => 'required']],
]);

test('can create a portfolio category', function () {
    Livewire::test(CreatePortfolioCategory::class)
        ->fillForm([
            'name' => 'Web Design',
            'slug' => 'web-design',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(PortfolioCategory::class, [
        'name' => 'Web Design',
        'slug' => 'web-design',
    ]);
});

/*
|--------------------------------------------------------------------------
| Edit Page
|--------------------------------------------------------------------------
*/

test('can load the edit portfolio category page', function () {
    $category = PortfolioCategory::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(EditPortfolioCategory::class, ['record' => $category->getRouteKey()])
        ->assertOk();
});

test('can update a portfolio category name', function () {
    $category = PortfolioCategory::factory()->create(['name' => 'Old Name', 'user_id' => $this->user->id]);

    Livewire::test(EditPortfolioCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['name' => 'New Name'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(PortfolioCategory::class, [
        'id' => $category->id,
        'name' => 'New Name',
    ]);
});
