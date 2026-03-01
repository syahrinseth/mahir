<?php

namespace App\Modules\Portfolio\Repositories;

use App\Modules\Portfolio\Enums\PortfolioStatus;
use App\Modules\Portfolio\Models\Portfolio;
use App\Shared\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PortfolioRepository implements RepositoryContract
{
    /**
     * @return Collection<int, Portfolio>
     */
    public function all(): Collection
    {
        return Portfolio::query()->with('author')->latest()->get();
    }

    public function findById(int $id): ?Portfolio
    {
        return Portfolio::query()->with(['author', 'category', 'media'])->find($id);
    }

    /**
     * @return Collection<int, Portfolio>
     */
    public function findByAuthor(int $userId): Collection
    {
        return Portfolio::query()
            ->where('user_id', $userId)
            ->with('author')
            ->latest()
            ->get();
    }

    /**
     * @return Collection<int, Portfolio>
     */
    public function findPublished(): Collection
    {
        return Portfolio::query()
            ->where('status', PortfolioStatus::Published->value)
            ->where('published_at', '<=', now())
            ->with(['author', 'category'])
            ->latest('published_at')
            ->get();
    }

    public function findBySlug(string $slug): ?Portfolio
    {
        return Portfolio::query()
            ->where('slug', $slug)
            ->with(['author', 'category', 'media'])
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Portfolio
    {
        return Portfolio::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Model
    {
        $portfolio = $this->findById($id);

        if (! $portfolio) {
            return null;
        }

        $portfolio->update($data);

        return $portfolio->fresh(['author', 'category', 'media']);
    }

    public function delete(int $id): bool
    {
        $portfolio = $this->findById($id);

        if (! $portfolio) {
            return false;
        }

        return (bool) $portfolio->delete();
    }
}
