<?php

namespace App\Modules\Portfolio\DTOs;

use App\Modules\Portfolio\Enums\PortfolioStatus;

/**
 * Data Transfer Object for updating portfolios.
 *
 * Tracks which fields were explicitly provided in the request to support
 * partial updates. This allows falsy values (false, 0, empty strings) to be
 * saved correctly, unlike array_filter() which would remove them.
 */
class UpdatePortfolioDTO
{
    /**
     * @param  list<string>|null  $technologies
     * @param  set<string>  $providedFields  Fields that were explicitly provided in the request
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
        private readonly array $providedFields = [],
    ) {}

    /**
     * Create DTO from array, tracking which fields were explicitly provided.
     *
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
            'description' => 'description',
            'category_id' => 'categoryId',
            'client_name' => 'clientName',
            'project_url' => 'projectUrl',
            'featured_image' => 'featuredImage',
            'technologies' => 'technologies',
            'status' => 'status',
            'sort_order' => 'sortOrder',
            'started_at' => 'startedAt',
            'ended_at' => 'endedAt',
            'published_at' => 'publishedAt',
        ];

        $result = [];
        foreach ($this->providedFields as $field) {
            if (! isset($fieldMapping[$field])) {
                continue;
            }

            $property = $fieldMapping[$field];
            $value = $this->{$property};

            // For status enum, convert to its string value
            if ($value instanceof PortfolioStatus) {
                $result[$field] = $value->value;
            } else {
                $result[$field] = $value;
            }
        }

        return $result;
    }
}
