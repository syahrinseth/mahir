<?php

namespace App\Modules\Portfolio\Services;

use App\Modules\Portfolio\DTOs\CreatePortfolioCategoryDTO;
use App\Modules\Portfolio\DTOs\CreatePortfolioDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioCategoryDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioDTO;
use App\Modules\Portfolio\Enums\PortfolioStatus;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Models\PortfolioCategory;
use App\Modules\Portfolio\Models\PortfolioMedia;
use App\Modules\Portfolio\Repositories\PortfolioCategoryRepository;
use App\Modules\Portfolio\Repositories\PortfolioRepository;
use App\Shared\Contracts\ServiceContract;
use Illuminate\Database\Eloquent\Collection;

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

        $dto = new UpdatePortfolioDTO(
            status: PortfolioStatus::Published,
            publishedAt: now()->toDateTimeString(),
        );

        $updated = $this->portfolioRepository->update($id, $dto->toArray());

        return $updated instanceof Portfolio ? $updated : null;
    }

    /**
     * Archive a portfolio item.
     */
    public function archivePortfolio(int $id): ?Portfolio
    {
        $dto = new UpdatePortfolioDTO(
            status: PortfolioStatus::Archived,
        );

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
    | Media
    |--------------------------------------------------------------------------
    */

    /**
     * Add a media item to a portfolio.
     *
     * @param  array<string, mixed>  $data
     */
    public function addMedia(array $data): PortfolioMedia
    {
        return PortfolioMedia::query()->create($data);
    }

    /**
     * Delete a media item.
     */
    public function deleteMedia(int $mediaId): bool
    {
        $media = PortfolioMedia::query()->find($mediaId);

        if (! $media) {
            return false;
        }

        return (bool) $media->delete();
    }

    /**
     * Get media for a portfolio.
     *
     * @return Collection<int, PortfolioMedia>
     */
    public function getMediaForPortfolio(int $portfolioId): Collection
    {
        return PortfolioMedia::query()
            ->where('portfolio_id', $portfolioId)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Reorder media items for a portfolio.
     *
     * @param  list<int>  $mediaIds  Ordered list of media IDs.
     */
    public function reorderMedia(int $portfolioId, array $mediaIds): void
    {
        foreach ($mediaIds as $index => $mediaId) {
            PortfolioMedia::query()
                ->where('id', $mediaId)
                ->where('portfolio_id', $portfolioId)
                ->update(['sort_order' => $index]);
        }
    }
}
