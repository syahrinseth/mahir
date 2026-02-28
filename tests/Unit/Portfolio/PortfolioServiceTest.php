<?php

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\DTOs\CreatePortfolioCategoryDTO;
use App\Modules\Portfolio\DTOs\CreatePortfolioDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioCategoryDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioDTO;
use App\Modules\Portfolio\Enums\PortfolioStatus;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Models\PortfolioCategory;
use App\Modules\Portfolio\Models\PortfolioMedia;
use App\Modules\Portfolio\Services\PortfolioService;

/*
|--------------------------------------------------------------------------
| Portfolio CRUD
|--------------------------------------------------------------------------
*/

test('createPortfolio creates a new portfolio', function () {
    $service = app(PortfolioService::class);
    $user = User::factory()->create();

    $dto = new CreatePortfolioDTO(
        userId: $user->id,
        title: 'Test Portfolio',
        slug: 'test-portfolio',
        description: 'A test portfolio item.',
    );

    $portfolio = $service->createPortfolio($dto);

    expect($portfolio)
        ->toBeInstanceOf(Portfolio::class)
        ->title->toBe('Test Portfolio')
        ->slug->toBe('test-portfolio')
        ->user_id->toBe($user->id)
        ->status->toBe(PortfolioStatus::Draft);
});

test('createPortfolio persists to database', function () {
    $service = app(PortfolioService::class);
    $user = User::factory()->create();

    $dto = new CreatePortfolioDTO(
        userId: $user->id,
        title: 'Persisted Portfolio',
        slug: 'persisted-portfolio',
        description: 'Persisted to DB.',
    );

    $service->createPortfolio($dto);

    $this->assertDatabaseHas('portfolios', [
        'title' => 'Persisted Portfolio',
        'slug' => 'persisted-portfolio',
    ]);
});

test('updatePortfolio updates an existing portfolio', function () {
    $service = app(PortfolioService::class);
    $portfolio = Portfolio::factory()->create();

    $dto = new UpdatePortfolioDTO(title: 'Updated Title');
    $updated = $service->updatePortfolio($portfolio->id, $dto);

    expect($updated)
        ->toBeInstanceOf(Portfolio::class)
        ->title->toBe('Updated Title');
});

test('updatePortfolio returns null for non-existent portfolio', function () {
    $service = app(PortfolioService::class);

    $dto = new UpdatePortfolioDTO(title: 'Updated Title');
    $result = $service->updatePortfolio(99999, $dto);

    expect($result)->toBeNull();
});

test('publishPortfolio sets status to published and sets published_at', function () {
    $service = app(PortfolioService::class);
    $portfolio = Portfolio::factory()->draft()->create();

    $published = $service->publishPortfolio($portfolio->id);

    expect($published)
        ->toBeInstanceOf(Portfolio::class)
        ->status->toBe(PortfolioStatus::Published);
    expect($published->published_at)->not->toBeNull();
});

test('publishPortfolio returns null for non-existent portfolio', function () {
    $service = app(PortfolioService::class);

    $result = $service->publishPortfolio(99999);

    expect($result)->toBeNull();
});

test('archivePortfolio sets status to archived', function () {
    $service = app(PortfolioService::class);
    $portfolio = Portfolio::factory()->published()->create();

    $archived = $service->archivePortfolio($portfolio->id);

    expect($archived)
        ->toBeInstanceOf(Portfolio::class)
        ->status->toBe(PortfolioStatus::Archived);
});

test('deletePortfolio removes portfolio from database', function () {
    $service = app(PortfolioService::class);
    $portfolio = Portfolio::factory()->create();

    $result = $service->deletePortfolio($portfolio->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('portfolios', ['id' => $portfolio->id]);
});

test('deletePortfolio returns false for non-existent portfolio', function () {
    $service = app(PortfolioService::class);

    $result = $service->deletePortfolio(99999);

    expect($result)->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Category CRUD
|--------------------------------------------------------------------------
*/

test('createCategory creates a new portfolio category', function () {
    $service = app(PortfolioService::class);
    $user = User::factory()->create();

    $dto = new CreatePortfolioCategoryDTO(
        userId: $user->id,
        name: 'Web Development',
        slug: 'web-development',
    );

    $category = $service->createCategory($dto);

    expect($category)
        ->toBeInstanceOf(PortfolioCategory::class)
        ->name->toBe('Web Development')
        ->slug->toBe('web-development');
});

test('updateCategory updates an existing category', function () {
    $service = app(PortfolioService::class);
    $category = PortfolioCategory::factory()->create();

    $dto = new UpdatePortfolioCategoryDTO(name: 'Updated Category');
    $updated = $service->updateCategory($category->id, $dto);

    expect($updated)
        ->toBeInstanceOf(PortfolioCategory::class)
        ->name->toBe('Updated Category');
});

test('updateCategory returns null for non-existent category', function () {
    $service = app(PortfolioService::class);

    $dto = new UpdatePortfolioCategoryDTO(name: 'Updated');
    $result = $service->updateCategory(99999, $dto);

    expect($result)->toBeNull();
});

test('deleteCategory removes category from database', function () {
    $service = app(PortfolioService::class);
    $category = PortfolioCategory::factory()->create();

    $result = $service->deleteCategory($category->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('portfolio_categories', ['id' => $category->id]);
});

/*
|--------------------------------------------------------------------------
| Media
|--------------------------------------------------------------------------
*/

test('addMedia creates a media record', function () {
    $service = app(PortfolioService::class);
    $portfolio = Portfolio::factory()->create();

    $media = $service->addMedia([
        'portfolio_id' => $portfolio->id,
        'file_path' => 'portfolios/screenshot.jpg',
        'file_name' => 'screenshot.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 1024,
        'sort_order' => 0,
    ]);

    expect($media)
        ->toBeInstanceOf(PortfolioMedia::class)
        ->file_name->toBe('screenshot.jpg');
});

test('deleteMedia removes a media record', function () {
    $service = app(PortfolioService::class);
    $media = PortfolioMedia::factory()->create();

    $result = $service->deleteMedia($media->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('portfolio_media', ['id' => $media->id]);
});

test('deleteMedia returns false for non-existent media', function () {
    $service = app(PortfolioService::class);

    $result = $service->deleteMedia(99999);

    expect($result)->toBeFalse();
});

test('getMediaForPortfolio returns ordered media', function () {
    $service = app(PortfolioService::class);
    $portfolio = Portfolio::factory()->create();

    PortfolioMedia::factory()->create(['portfolio_id' => $portfolio->id, 'sort_order' => 2]);
    PortfolioMedia::factory()->create(['portfolio_id' => $portfolio->id, 'sort_order' => 0]);
    PortfolioMedia::factory()->create(['portfolio_id' => $portfolio->id, 'sort_order' => 1]);

    $media = $service->getMediaForPortfolio($portfolio->id);

    expect($media)->toHaveCount(3);
    expect($media[0]->sort_order)->toBe(0);
    expect($media[1]->sort_order)->toBe(1);
    expect($media[2]->sort_order)->toBe(2);
});

test('reorderMedia updates sort_order for media items', function () {
    $service = app(PortfolioService::class);
    $portfolio = Portfolio::factory()->create();

    $media1 = PortfolioMedia::factory()->create(['portfolio_id' => $portfolio->id, 'sort_order' => 0]);
    $media2 = PortfolioMedia::factory()->create(['portfolio_id' => $portfolio->id, 'sort_order' => 1]);
    $media3 = PortfolioMedia::factory()->create(['portfolio_id' => $portfolio->id, 'sort_order' => 2]);

    $service->reorderMedia($portfolio->id, [$media3->id, $media1->id, $media2->id]);

    expect($media3->fresh()->sort_order)->toBe(0);
    expect($media1->fresh()->sort_order)->toBe(1);
    expect($media2->fresh()->sort_order)->toBe(2);
});
