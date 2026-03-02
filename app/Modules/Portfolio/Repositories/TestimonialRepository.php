<?php

namespace App\Modules\Portfolio\Repositories;

use App\Modules\Portfolio\Models\Testimonial;
use App\Shared\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TestimonialRepository implements RepositoryContract
{
    /**
     * @return Collection<int, Testimonial>
     */
    public function all(): Collection
    {
        return Testimonial::query()->with('author')->latest()->get();
    }

    public function findById(int $id): ?Testimonial
    {
        return Testimonial::query()->with(['author', 'portfolio', 'media'])->find($id);
    }

    /**
     * @return Collection<int, Testimonial>
     */
    public function findPublished(): Collection
    {
        return Testimonial::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['author', 'portfolio'])
            ->latest('published_at')
            ->get();
    }

    /**
     * Find a published testimonial by its ID.
     */
    public function findPublishedById(int $id): ?Testimonial
    {
        return Testimonial::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['author', 'portfolio', 'media'])
            ->find($id);
    }

    /**
     * Find all testimonials tied to a specific portfolio.
     *
     * @return Collection<int, Testimonial>
     */
    public function findByPortfolio(int $portfolioId): Collection
    {
        return Testimonial::query()
            ->where('portfolio_id', $portfolioId)
            ->with('author')
            ->latest()
            ->get();
    }

    /**
     * Find all featured testimonials.
     *
     * @return Collection<int, Testimonial>
     */
    public function findFeatured(): Collection
    {
        return Testimonial::query()
            ->where('is_featured', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['author', 'portfolio'])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Testimonial
    {
        return Testimonial::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Model
    {
        $testimonial = $this->findById($id);

        if (! $testimonial) {
            return null;
        }

        $testimonial->update($data);

        return $testimonial->fresh(['author', 'portfolio', 'media']);
    }

    public function delete(int $id): bool
    {
        $testimonial = $this->findById($id);

        if (! $testimonial) {
            return false;
        }

        return (bool) $testimonial->delete();
    }
}
