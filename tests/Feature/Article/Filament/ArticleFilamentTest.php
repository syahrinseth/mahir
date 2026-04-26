<?php

use App\Modules\Article\Filament\Resources\Articles\Pages\CreateArticle;
use App\Modules\Article\Filament\Resources\Articles\Pages\EditArticle;
use App\Modules\Article\Filament\Resources\Articles\Pages\ListArticles;
use App\Modules\Article\Models\Article;
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

test('can load the list articles page', function () {
    Livewire::test(ListArticles::class)
        ->assertOk();
});

test('lists articles', function () {
    $articles = Article::factory()->count(3)->create();

    Livewire::test(ListArticles::class)
        ->assertCanSeeTableRecords($articles);
});

/*
|--------------------------------------------------------------------------
| Create Page
|--------------------------------------------------------------------------
*/

test('can load the create article page', function () {
    Livewire::test(CreateArticle::class)
        ->assertOk();
});

test('validates required fields on create', function (array $data, array $errors) {
    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => 'Test Title',
            'slug' => 'test-title',
            'content' => 'Some content here.',
            'status' => 'draft',
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`title` is required' => [['title' => null], ['title' => 'required']],
    '`slug` is required' => [['slug' => null], ['slug' => 'required']],
    '`content` is required' => [['content' => null], ['content' => 'required']],
    '`status` is required' => [['status' => null], ['status' => 'required']],
]);

test('can create an article', function () {
    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => 'My Article',
            'slug' => 'my-article',
            'content' => 'Article body content.',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Article::class, [
        'title' => 'My Article',
        'slug' => 'my-article',
    ]);
});

/*
|--------------------------------------------------------------------------
| Edit Page
|--------------------------------------------------------------------------
*/

test('can load the edit article page', function () {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->assertOk();
});

test('can update an article title', function () {
    $article = Article::factory()->create(['title' => 'Old Title', 'user_id' => $this->user->id]);

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm(['title' => 'New Title'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Article::class, [
        'id' => $article->id,
        'title' => 'New Title',
    ]);
});
