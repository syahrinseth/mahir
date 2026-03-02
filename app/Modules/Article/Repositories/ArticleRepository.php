<?php

namespace App\Modules\Article\Repositories;

use App\Modules\Article\Enums\ArticleStatus;
use App\Modules\Article\Models\Article;
use App\Shared\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ArticleRepository implements RepositoryContract
{
    /**
     * @return Collection<int, Article>
     */
    public function all(): Collection
    {
        return Article::query()->with('author')->latest()->get();
    }

    public function findById(int $id): ?Article
    {
        return Article::query()->with(['author', 'series'])->find($id);
    }

    /**
     * @return Collection<int, Article>
     */
    public function findByAuthor(int $userId): Collection
    {
        return Article::query()
            ->where('user_id', $userId)
            ->with('author')
            ->latest()
            ->get();
    }

    /**
     * @return Collection<int, Article>
     */
    public function findPublished(): Collection
    {
        return Article::query()
            ->where('status', ArticleStatus::Published->value)
            ->where('published_at', '<=', now())
            ->with('author')
            ->latest('published_at')
            ->get();
    }

    /**
     * Find a published article by its ID.
     */
    public function findPublishedById(int $id): ?Article
    {
        return Article::query()
            ->where('status', ArticleStatus::Published->value)
            ->where('published_at', '<=', now())
            ->with(['author', 'series'])
            ->find($id);
    }

    public function findBySlug(string $slug): ?Article
    {
        return Article::query()
            ->where('slug', $slug)
            ->with(['author', 'series'])
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Article
    {
        return Article::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Model
    {
        $article = $this->findById($id);

        if (! $article) {
            return null;
        }

        $article->update($data);

        return $article->fresh(['author', 'series']);
    }

    public function delete(int $id): bool
    {
        $article = $this->findById($id);

        if (! $article) {
            return false;
        }

        return (bool) $article->delete();
    }
}
