<?php

namespace App\Modules\Portfolio\DTOs;

use App\Modules\Portfolio\Enums\PortfolioStatus;

class UpdatePortfolioDTO
{
    /**
     * @param  list<string>|null  $technologies
     */
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $slug = null,
        public readonly ?string $description = null,
        public readonly ?int $categoryId = null,
        public readonly ?string $clientName = null,
        public readonly ?string $projectUrl = null,
        public readonly ?string $featuredImage = null,
        public readonly ?array $technologies = null,
        public readonly ?PortfolioStatus $status = null,
        public readonly ?int $sortOrder = null,
        public readonly ?string $startedAt = null,
        public readonly ?string $endedAt = null,
        public readonly ?string $publishedAt = null,
    ) {}

    /**
     * @param  array{title?: string, slug?: string, description?: string, category_id?: int|null, client_name?: string|null, project_url?: string|null, featured_image?: string|null, technologies?: list<string>|null, status?: string, sort_order?: int, started_at?: string|null, ended_at?: string|null, published_at?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
            categoryId: $data['category_id'] ?? null,
            clientName: $data['client_name'] ?? null,
            projectUrl: $data['project_url'] ?? null,
            featuredImage: $data['featured_image'] ?? null,
            technologies: $data['technologies'] ?? null,
            status: isset($data['status']) ? PortfolioStatus::from($data['status']) : null,
            sortOrder: $data['sort_order'] ?? null,
            startedAt: $data['started_at'] ?? null,
            endedAt: $data['ended_at'] ?? null,
            publishedAt: $data['published_at'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'client_name' => $this->clientName,
            'project_url' => $this->projectUrl,
            'featured_image' => $this->featuredImage,
            'technologies' => $this->technologies,
            'status' => $this->status?->value,
            'sort_order' => $this->sortOrder,
            'started_at' => $this->startedAt,
            'ended_at' => $this->endedAt,
            'published_at' => $this->publishedAt,
        ], fn (mixed $value): bool => $value !== null);
    }
}
