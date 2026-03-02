<?php

namespace App\Modules\Article\DTOs;

use App\Modules\Article\Enums\ArticleStatus;

/**
 * Data Transfer Object for updating articles.
 *
 * Tracks which fields were explicitly provided in the request to support
 * partial updates. This allows falsy values (false, 0, empty strings) to be
 * saved correctly, unlike array_filter() which would remove them.
 */
class UpdateArticleDTO
{
    /**
     * @param  set<string>  $providedFields  Fields that were explicitly provided in the request
     */
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $slug = null,
        public readonly ?string $content = null,
        public readonly ?string $description = null,
        public readonly ?ArticleStatus $status = null,
        public readonly ?string $publishedAt = null,
        public readonly ?int $seriesId = null,
        public readonly ?int $seriesOrder = null,
        private readonly array $providedFields = [],
    ) {}

    /**
     * Create DTO from array, tracking which fields were explicitly provided.
     *
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
            providedFields: array_keys($data),
        );
    }

    /**
     * Convert to array for database updates, only including explicitly provided fields.
     *
     * This approach preserves falsy values (false, 0, empty strings) unlike
     * array_filter(), ensuring partial updates work correctly.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $fieldMapping = [
            'title' => 'title',
            'slug' => 'slug',
            'content' => 'content',
            'description' => 'description',
            'status' => 'status',
            'published_at' => 'publishedAt',
            'series_id' => 'seriesId',
            'series_order' => 'seriesOrder',
        ];

        $result = [];
        foreach ($this->providedFields as $field) {
            if (! isset($fieldMapping[$field])) {
                continue;
            }

            $property = $fieldMapping[$field];
            $value = $this->{$property};

            // For status enum, convert to its string value
            if ($value instanceof ArticleStatus) {
                $result[$field] = $value->value;
            } else {
                $result[$field] = $value;
            }
        }

        return $result;
    }
}
