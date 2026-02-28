<?php

namespace App\Modules\Article\Repositories;

use App\Modules\Article\Models\ArticleSeries;
use App\Shared\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ArticleSeriesRepository implements RepositoryContract
{
    /**
     * @return Collection<int, ArticleSeries>
     */
    public function all(): Collection
    {
        return ArticleSeries::query()->with('author')->latest()->get();
    }

    public function findById(int $id): ?ArticleSeries
    {
        return ArticleSeries::query()->with(['author', 'orderedArticles'])->find($id);
    }

    public function findBySlug(string $slug): ?ArticleSeries
    {
        return ArticleSeries::query()
            ->where('slug', $slug)
            ->with(['author', 'orderedArticles'])
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ArticleSeries
    {
        return ArticleSeries::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Model
    {
        $series = $this->findById($id);

        if (! $series) {
            return null;
        }

        $series->update($data);

        return $series->fresh(['author', 'orderedArticles']);
    }

    public function delete(int $id): bool
    {
        $series = $this->findById($id);

        if (! $series) {
            return false;
        }

        return (bool) $series->delete();
    }
}
