<?php

namespace App\Modules\Portfolio\Repositories;

use App\Modules\Portfolio\Models\PortfolioCategory;
use App\Shared\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PortfolioCategoryRepository implements RepositoryContract
{
    /**
     * @return Collection<int, PortfolioCategory>
     */
    public function all(): Collection
    {
        return PortfolioCategory::query()->with('creator')->latest()->get();
    }

    public function findById(int $id): ?PortfolioCategory
    {
        return PortfolioCategory::query()->with(['creator', 'orderedPortfolios'])->find($id);
    }

    public function findBySlug(string $slug): ?PortfolioCategory
    {
        return PortfolioCategory::query()
            ->where('slug', $slug)
            ->with(['creator', 'orderedPortfolios'])
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PortfolioCategory
    {
        return PortfolioCategory::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Model
    {
        $category = $this->findById($id);

        if (! $category) {
            return null;
        }

        $category->update($data);

        return $category->fresh(['creator', 'orderedPortfolios']);
    }

    public function delete(int $id): bool
    {
        $category = $this->findById($id);

        if (! $category) {
            return false;
        }

        return (bool) $category->delete();
    }
}
