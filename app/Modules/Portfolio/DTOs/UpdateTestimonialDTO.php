<?php

namespace App\Modules\Portfolio\DTOs;

/**
 * Data Transfer Object for updating testimonials.
 *
 * Tracks which fields were explicitly provided in the request to support
 * partial updates. This allows falsy values (false, 0, empty strings) to be
 * saved correctly, unlike array_filter() which would remove them.
 */
class UpdateTestimonialDTO
{
    /**
     * @param  set<string>  $providedFields  Fields that were explicitly provided in the request
     */
    public function __construct(
        public readonly ?string $clientName = null,
        public readonly ?string $content = null,
        public readonly ?int $portfolioId = null,
        public readonly ?string $clientPosition = null,
        public readonly ?string $clientCompany = null,
        public readonly ?int $rating = null,
        public readonly ?bool $isFeatured = null,
        public readonly ?int $sortOrder = null,
        public readonly ?string $publishedAt = null,
        private readonly array $providedFields = [],
    ) {}

    /**
     * Create DTO from array, tracking which fields were explicitly provided.
     *
     * @param  array{client_name?: string, content?: string, portfolio_id?: int|null, client_position?: string|null, client_company?: string|null, rating?: int|null, is_featured?: bool, sort_order?: int, published_at?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clientName: $data['client_name'] ?? null,
            content: $data['content'] ?? null,
            portfolioId: $data['portfolio_id'] ?? null,
            clientPosition: $data['client_position'] ?? null,
            clientCompany: $data['client_company'] ?? null,
            rating: $data['rating'] ?? null,
            isFeatured: $data['is_featured'] ?? null,
            sortOrder: $data['sort_order'] ?? null,
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
            'client_name' => 'clientName',
            'content' => 'content',
            'portfolio_id' => 'portfolioId',
            'client_position' => 'clientPosition',
            'client_company' => 'clientCompany',
            'rating' => 'rating',
            'is_featured' => 'isFeatured',
            'sort_order' => 'sortOrder',
            'published_at' => 'publishedAt',
        ];

        $result = [];
        foreach ($this->providedFields as $field) {
            if (isset($fieldMapping[$field])) {
                $property = $fieldMapping[$field];
                $result[$field] = $this->{$property};
            }
        }

        return $result;
    }
}
