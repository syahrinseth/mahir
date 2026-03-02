<?php

namespace App\Modules\Portfolio\Services;

use App\Modules\Portfolio\DTOs\CreatePortfolioCategoryDTO;
use App\Modules\Portfolio\DTOs\CreatePortfolioDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioCategoryDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioDTO;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Models\PortfolioCategory;
use App\Modules\Portfolio\Repositories\PortfolioCategoryRepository;
use App\Modules\Portfolio\Repositories\PortfolioRepository;
use App\Shared\Contracts\ServiceContract;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Business logic for managing portfolios, categories, and media.
 */
class PortfolioService implements ServiceContract
{
    public function __construct(
        private PortfolioRepository $portfolioRepository,
        private PortfolioCategoryRepository $categoryRepository,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Portfolios
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new portfolio item.
     */
    public function createPortfolio(CreatePortfolioDTO $dto): Portfolio
    {
        return $this->portfolioRepository->create($dto->toArray());
    }

    /**
     * Update an existing portfolio item.
     */
    public function updatePortfolio(int $id, UpdatePortfolioDTO $dto): ?Portfolio
    {
        $updated = $this->portfolioRepository->update($id, $dto->toArray());

        return $updated instanceof Portfolio ? $updated : null;
    }

    /**
     * Publish a portfolio item by setting its status and publish date.
     */
    public function publishPortfolio(int $id): ?Portfolio
    {
        $portfolio = $this->portfolioRepository->findById($id);

        if (! $portfolio) {
            return null;
        }

        $dto = UpdatePortfolioDTO::fromArray([
            'status' => 'published',
            'published_at' => now()->toDateTimeString(),
        ]);

        $updated = $this->portfolioRepository->update($id, $dto->toArray());

        return $updated instanceof Portfolio ? $updated : null;
    }

    /**
     * Archive a portfolio item.
     */
    public function archivePortfolio(int $id): ?Portfolio
    {
        $dto = UpdatePortfolioDTO::fromArray([
            'status' => 'archived',
        ]);

        $updated = $this->portfolioRepository->update($id, $dto->toArray());

        return $updated instanceof Portfolio ? $updated : null;
    }

    /**
     * Delete a portfolio item.
     */
    public function deletePortfolio(int $id): bool
    {
        return $this->portfolioRepository->delete($id);
    }

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new portfolio category.
     */
    public function createCategory(CreatePortfolioCategoryDTO $dto): PortfolioCategory
    {
        return $this->categoryRepository->create($dto->toArray());
    }

    /**
     * Update an existing portfolio category.
     */
    public function updateCategory(int $id, UpdatePortfolioCategoryDTO $dto): ?PortfolioCategory
    {
        $category = $this->categoryRepository->update($id, $dto->toArray());

        return $category instanceof PortfolioCategory ? $category : null;
    }

    /**
     * Delete a portfolio category.
     */
    public function deleteCategory(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }

    /*
    |--------------------------------------------------------------------------
    | Media (Spatie Media Library)
    |--------------------------------------------------------------------------
    */

    /**
     * Add a media file to a portfolio collection.
     *
     * @param  array{caption?: string|null, sort_order?: int}  $properties
     */
    public function addMedia(
        Portfolio $portfolio,
        UploadedFile $file,
        string $collection = 'gallery',
        array $properties = [],
    ): Media {
        $customProperties = array_filter([
            'caption' => $properties['caption'] ?? null,
            'sort_order' => $properties['sort_order'] ?? null,
        ], fn ($value) => $value !== null);

        return $portfolio
            ->addMedia($file)
            ->withCustomProperties($customProperties)
            ->toMediaCollection($collection);
    }

    /**
     * Delete a media item.
     */
    public function deleteMedia(int $mediaId): bool
    {
        $mediaClass = config('media-library.media_model');
        $media = $mediaClass::query()->find($mediaId);

        if (! $media) {
            return false;
        }

        $media->delete();

        return true;
    }

    /**
     * Get all gallery media for a portfolio, ordered by order_column.
     *
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media>
     */
    public function getMediaForPortfolio(Portfolio $portfolio, string $collection = 'gallery'): \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection
    {
        return $portfolio->getMedia($collection);
    }

    /**
     * Reorder media items for a portfolio.
     *
     * @param  list<int>  $mediaIds  Ordered list of media IDs.
     */
    public function reorderMedia(Portfolio $portfolio, array $mediaIds): void
    {
        Media::setNewOrder($mediaIds);
    }
}
