<?php

namespace App\Modules\Portfolio\DTOs;

/**
 * Data Transfer Object for updating portfolio categories.
 *
 * Tracks which fields were explicitly provided in the request to support
 * partial updates. This allows falsy values (false, 0, empty strings) to be
 * saved correctly, unlike array_filter() which would remove them.
 */
class UpdatePortfolioCategoryDTO
{
    /**
     * @param  set<string>  $providedFields  Fields that were explicitly provided in the request
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $slug = null,
        public readonly ?string $description = null,
        public readonly ?int $sortOrder = null,
        private readonly array $providedFields = [],
    ) {}

    /**
     * Create DTO from array, tracking which fields were explicitly provided.
     *
     * @param  array{name?: string, slug?: string, description?: string|null, sort_order?: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
            sortOrder: $data['sort_order'] ?? null,
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
            'name' => 'name',
            'slug' => 'slug',
            'description' => 'description',
            'sort_order' => 'sortOrder',
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
