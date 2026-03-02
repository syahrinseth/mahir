<?php

namespace App\Modules\Article\DTOs;

use App\Modules\Article\Enums\ArticleStatus;

class UpdateArticleDTO
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $slug = null,
        public readonly ?string $content = null,
        public readonly ?string $description = null,
        public readonly ?ArticleStatus $status = null,
        public readonly ?string $publishedAt = null,
        public readonly ?int $seriesId = null,
        public readonly ?int $seriesOrder = null,
    ) {}

    /**
     * @param  array{title?: string, slug?: string, content?: string, description?: string|null, status?: string, published_at?: string|null, series_id?: int|null, series_order?: int|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            slug: $data['slug'] ?? null,
            content: $data['content'] ?? null,
            description: $data['description'] ?? null,
            status: isset($data['status']) ? ArticleStatus::from($data['status']) : null,
            publishedAt: $data['published_at'] ?? null,
            seriesId: $data['series_id'] ?? null,
            seriesOrder: $data['series_order'] ?? null,
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
            'content' => $this->content,
            'description' => $this->description,
            'status' => $this->status?->value,
            'published_at' => $this->publishedAt,
            'series_id' => $this->seriesId,
            'series_order' => $this->seriesOrder,
        ], fn (mixed $value): bool => $value !== null);
    }
}
