<?php

use App\Modules\Article\Filament\Resources\ArticleSeries\Pages\CreateArticleSeries;
use App\Modules\Article\Filament\Resources\ArticleSeries\Pages\EditArticleSeries;
use App\Modules\Article\Filament\Resources\ArticleSeries\Pages\ListArticleSeries;
use App\Modules\Article\Models\ArticleSeries;
use App\Modules\Auth\Models\User;
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

test('can load the list article series page', function () {
    Livewire::test(ListArticleSeries::class)
        ->assertOk();
});

test('lists article series', function () {
    $series = ArticleSeries::factory()->count(3)->create();

    Livewire::test(ListArticleSeries::class)
        ->assertCanSeeTableRecords($series);
});

/*
|--------------------------------------------------------------------------
| Create Page
|--------------------------------------------------------------------------
*/

test('can load the create article series page', function () {
    Livewire::test(CreateArticleSeries::class)
        ->assertOk();
});

test('validates required fields on create', function (array $data, array $errors) {
    Livewire::test(CreateArticleSeries::class)
        ->fillForm(['title' => 'Test Series', 'slug' => 'test-series', ...$data])
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`title` is required' => [['title' => null], ['title' => 'required']],
    '`slug` is required' => [['slug' => null], ['slug' => 'required']],
]);

test('can create an article series', function () {
    Livewire::test(CreateArticleSeries::class)
        ->fillForm([
            'title' => 'My Series',
            'slug' => 'my-series',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(ArticleSeries::class, [
        'title' => 'My Series',
        'slug' => 'my-series',
    ]);
});

/*
|--------------------------------------------------------------------------
| Edit Page
|--------------------------------------------------------------------------
*/

test('can load the edit article series page', function () {
    $series = ArticleSeries::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(EditArticleSeries::class, ['record' => $series->getRouteKey()])
        ->assertOk();
});

test('can update an article series title', function () {
    $series = ArticleSeries::factory()->create(['title' => 'Old Title', 'user_id' => $this->user->id]);

    Livewire::test(EditArticleSeries::class, ['record' => $series->getRouteKey()])
        ->fillForm(['title' => 'New Title'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(ArticleSeries::class, [
        'id' => $series->id,
        'title' => 'New Title',
    ]);
});
