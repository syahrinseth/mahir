<?php

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\DTOs\CreatePortfolioCategoryDTO;
use App\Modules\Portfolio\DTOs\CreatePortfolioDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioCategoryDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioDTO;
use App\Modules\Portfolio\Enums\PortfolioStatus;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Models\PortfolioCategory;
use App\Modules\Portfolio\Services\PortfolioService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('addMedia creates a media record via Spatie', function () {
    Storage::fake('public');
    $service = app(PortfolioService::class);
    $portfolio = Portfolio::factory()->create();
    $file = UploadedFile::fake()->image('screenshot.jpg', 800, 600);

    $media = $service->addMedia($portfolio, $file, 'gallery', [
        'caption' => 'Screenshot caption',
        'sort_order' => 0,
    ]);

    expect($media)
        ->toBeInstanceOf(\Spatie\MediaLibrary\MediaCollections\Models\Media::class)
        ->file_name->toBe('screenshot.jpg')
        ->collection_name->toBe('gallery');
    expect($media->getCustomProperty('caption'))->toBe('Screenshot caption');
});

test('deleteMedia removes a media record', function () {
    Storage::fake('public');
    $service = app(PortfolioService::class);
    $portfolio = Portfolio::factory()->create();

    $media = $portfolio->addMedia(UploadedFile::fake()->image('screenshot.jpg'))
        ->toMediaCollection('gallery');

    $result = $service->deleteMedia($media->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('media', ['id' => $media->id]);
});

test('deleteMedia returns false for non-existent media', function () {
    $service = app(PortfolioService::class);

    $result = $service->deleteMedia(99999);

    expect($result)->toBeFalse();
});

test('getMediaForPortfolio returns media collection', function () {
    Storage::fake('public');
    $service = app(PortfolioService::class);
    $portfolio = Portfolio::factory()->create();

    $portfolio->addMedia(UploadedFile::fake()->image('photo1.jpg'))->toMediaCollection('gallery');
    $portfolio->addMedia(UploadedFile::fake()->image('photo2.jpg'))->toMediaCollection('gallery');
    $portfolio->addMedia(UploadedFile::fake()->image('photo3.jpg'))->toMediaCollection('gallery');

    $media = $service->getMediaForPortfolio($portfolio, 'gallery');

    expect($media)->toHaveCount(3);
});

test('reorderMedia updates order_column for media items', function () {
    Storage::fake('public');
    $service = app(PortfolioService::class);
    $portfolio = Portfolio::factory()->create();

    $media1 = $portfolio->addMedia(UploadedFile::fake()->image('photo1.jpg'))->toMediaCollection('gallery');
    $media2 = $portfolio->addMedia(UploadedFile::fake()->image('photo2.jpg'))->toMediaCollection('gallery');
    $media3 = $portfolio->addMedia(UploadedFile::fake()->image('photo3.jpg'))->toMediaCollection('gallery');

    $service->reorderMedia($portfolio, [$media3->id, $media1->id, $media2->id]);

    expect($media3->fresh()->order_column)->toBe(1);
    expect($media1->fresh()->order_column)->toBe(2);
    expect($media2->fresh()->order_column)->toBe(3);
});
