<?php

use App\Modules\Portfolio\DTOs\CreatePortfolioCategoryDTO;
use App\Modules\Portfolio\DTOs\CreatePortfolioDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioCategoryDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioDTO;
use App\Modules\Portfolio\Enums\PortfolioStatus;

/*
|--------------------------------------------------------------------------
| CreatePortfolioDTO
|--------------------------------------------------------------------------
*/

test('CreatePortfolioDTO fromArray creates instance with required fields', function () {
    $dto = CreatePortfolioDTO::fromArray([
        'user_id' => 1,
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
        'description' => 'A description.',
    ]);

    expect($dto)
        ->userId->toBe(1)
        ->title->toBe('My Portfolio')
        ->slug->toBe('my-portfolio')
        ->description->toBe('A description.')
        ->status->toBe(PortfolioStatus::Draft)
        ->categoryId->toBeNull()
        ->clientName->toBeNull()
        ->technologies->toBeNull()
        ->sortOrder->toBe(0);
});

test('CreatePortfolioDTO fromArray accepts all optional fields', function () {
    $dto = CreatePortfolioDTO::fromArray([
        'user_id' => 1,
        'title' => 'Full Portfolio',
        'slug' => 'full-portfolio',
        'description' => 'Full description.',
        'category_id' => 5,
        'client_name' => 'Acme Corp',
        'project_url' => 'https://acme.com',
        'featured_image' => '/images/hero.jpg',
        'technologies' => ['Laravel', 'React'],
        'status' => 'published',
        'sort_order' => 3,
        'started_at' => '2026-01-01',
        'ended_at' => '2026-02-28',
        'published_at' => '2026-02-15 10:00:00',
    ]);

    expect($dto)
        ->categoryId->toBe(5)
        ->clientName->toBe('Acme Corp')
        ->projectUrl->toBe('https://acme.com')
        ->featuredImage->toBe('/images/hero.jpg')
        ->technologies->toBe(['Laravel', 'React'])
        ->status->toBe(PortfolioStatus::Published)
        ->sortOrder->toBe(3)
        ->startedAt->toBe('2026-01-01')
        ->endedAt->toBe('2026-02-28')
        ->publishedAt->toBe('2026-02-15 10:00:00');
});

test('CreatePortfolioDTO toArray returns all fields', function () {
    $dto = new CreatePortfolioDTO(
        userId: 1,
        title: 'Test',
        slug: 'test',
        description: 'Desc',
        technologies: ['PHP'],
    );

    $array = $dto->toArray();

    expect($array)
        ->toBeArray()
        ->toHaveKey('user_id', 1)
        ->toHaveKey('title', 'Test')
        ->toHaveKey('slug', 'test')
        ->toHaveKey('description', 'Desc')
        ->toHaveKey('technologies', ['PHP'])
        ->toHaveKey('status', 'draft');
});

/*
|--------------------------------------------------------------------------
| UpdatePortfolioDTO
|--------------------------------------------------------------------------
*/

test('UpdatePortfolioDTO fromArray creates instance with partial fields', function () {
    $dto = UpdatePortfolioDTO::fromArray([
        'title' => 'Updated Title',
    ]);

    expect($dto)
        ->title->toBe('Updated Title')
        ->slug->toBeNull()
        ->description->toBeNull()
        ->status->toBeNull();
});

test('UpdatePortfolioDTO toArray filters null values', function () {
    $dto = UpdatePortfolioDTO::fromArray([
        'title' => 'Only Title',
    ]);

    $array = $dto->toArray();

    expect($array)
        ->toBeArray()
        ->toHaveKey('title', 'Only Title')
        ->not->toHaveKey('slug')
        ->not->toHaveKey('description')
        ->not->toHaveKey('status');
});

test('UpdatePortfolioDTO toArray includes all non-null values', function () {
    $dto = UpdatePortfolioDTO::fromArray([
        'title' => 'Updated',
        'status' => 'published',
        'technologies' => ['Vue', 'Laravel'],
    ]);

    $array = $dto->toArray();

    expect($array)
        ->toHaveCount(3)
        ->toHaveKey('title', 'Updated')
        ->toHaveKey('status', 'published')
        ->toHaveKey('technologies', ['Vue', 'Laravel']);
});

/*
|--------------------------------------------------------------------------
| CreatePortfolioCategoryDTO
|--------------------------------------------------------------------------
*/

test('CreatePortfolioCategoryDTO fromArray creates instance', function () {
    $dto = CreatePortfolioCategoryDTO::fromArray([
        'user_id' => 1,
        'name' => 'Web Dev',
        'slug' => 'web-dev',
        'description' => 'Web development projects.',
        'sort_order' => 2,
    ]);

    expect($dto)
        ->userId->toBe(1)
        ->name->toBe('Web Dev')
        ->slug->toBe('web-dev')
        ->description->toBe('Web development projects.')
        ->sortOrder->toBe(2);
});

test('CreatePortfolioCategoryDTO toArray returns all fields', function () {
    $dto = new CreatePortfolioCategoryDTO(
        userId: 1,
        name: 'Design',
        slug: 'design',
    );

    $array = $dto->toArray();

    expect($array)
        ->toBeArray()
        ->toHaveKey('user_id', 1)
        ->toHaveKey('name', 'Design')
        ->toHaveKey('slug', 'design')
        ->toHaveKey('description', null)
        ->toHaveKey('sort_order', 0);
});

/*
|--------------------------------------------------------------------------
| UpdatePortfolioCategoryDTO
|--------------------------------------------------------------------------
*/

test('UpdatePortfolioCategoryDTO fromArray creates instance with partial fields', function () {
    $dto = UpdatePortfolioCategoryDTO::fromArray([
        'name' => 'Updated Name',
    ]);

    expect($dto)
        ->name->toBe('Updated Name')
        ->slug->toBeNull()
        ->description->toBeNull()
        ->sortOrder->toBeNull();
});

test('UpdatePortfolioCategoryDTO toArray filters null values', function () {
    $dto = UpdatePortfolioCategoryDTO::fromArray([
        'name' => 'Only Name',
    ]);

    $array = $dto->toArray();

    expect($array)
        ->toBeArray()
        ->toHaveCount(1)
        ->toHaveKey('name', 'Only Name');
});

test('UpdatePortfolioCategoryDTO toArray includes all non-null values', function () {
    $dto = UpdatePortfolioCategoryDTO::fromArray([
        'name' => 'Updated',
        'slug' => 'updated',
        'sort_order' => 5,
    ]);

    $array = $dto->toArray();

    expect($array)
        ->toHaveCount(3)
        ->toHaveKey('name', 'Updated')
        ->toHaveKey('slug', 'updated')
        ->toHaveKey('sort_order', 5);
});

/*
|--------------------------------------------------------------------------
| PortfolioStatus Enum
|--------------------------------------------------------------------------
*/

test('PortfolioStatus has correct values', function () {
    expect(PortfolioStatus::Draft->value)->toBe('draft');
    expect(PortfolioStatus::Published->value)->toBe('published');
    expect(PortfolioStatus::Archived->value)->toBe('archived');
});

test('PortfolioStatus has labels', function () {
    expect(PortfolioStatus::Draft->label())->toBe('Draft');
    expect(PortfolioStatus::Published->label())->toBe('Published');
    expect(PortfolioStatus::Archived->label())->toBe('Archived');
});

test('PortfolioStatus has colors', function () {
    expect(PortfolioStatus::Draft->color())->toBe('gray');
    expect(PortfolioStatus::Published->color())->toBe('success');
    expect(PortfolioStatus::Archived->color())->toBe('warning');
});
