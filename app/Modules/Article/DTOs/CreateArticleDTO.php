<?php

namespace App\Modules\Article\DTOs;

use App\Modules\Article\Enums\ArticleStatus;

class CreateArticleDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $content,
        public readonly ?string $description = null,
        public readonly ArticleStatus $status = ArticleStatus::Draft,
        public readonly ?string $featuredImage = null,
        public readonly ?string $publishedAt = null,
        public readonly ?int $seriesId = null,
        public readonly ?int $seriesOrder = null,
    ) {}

    /**
     * @param  array{user_id: int, title: string, slug: string, content: string, description?: string|null, status?: string, featured_image?: string|null, published_at?: string|null, series_id?: int|null, series_order?: int|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            title: $data['title'],
            slug: $data['slug'],
            content: $data['content'],
            description: $data['description'] ?? null,
            status: isset($data['status']) ? ArticleStatus::from($data['status']) : ArticleStatus::Draft,
            featuredImage: $data['featured_image'] ?? null,
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
        return [
            'user_id' => $this->userId,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'description' => $this->description,
            'status' => $this->status->value,
            'featured_image' => $this->featuredImage,
            'published_at' => $this->publishedAt,
            'series_id' => $this->seriesId,
            'series_order' => $this->seriesOrder,
        ];
    }
}
