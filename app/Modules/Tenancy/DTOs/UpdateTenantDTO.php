<?php

namespace App\Modules\Tenancy\DTOs;

/**
 * Data Transfer Object for updating tenants.
 *
 * Tracks which fields were explicitly provided in the request to support
 * partial updates. This allows falsy values (false, 0, empty strings) to be
 * saved correctly, unlike array_filter() which would remove them.
 */
class UpdateTenantDTO
{
    /**
     * @param  set<string>  $providedFields  Fields that were explicitly provided in the request
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $slug = null,
        public readonly ?string $domain = null,
        public readonly ?bool $isActive = null,
        private readonly array $providedFields = [],
    ) {}

    /**
     * Create DTO from array, tracking which fields were explicitly provided.
     *
     * @param  array{name?: string, slug?: string, domain?: string, is_active?: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            domain: $data['domain'] ?? null,
            isActive: $data['is_active'] ?? null,
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
            'domain' => 'domain',
            'is_active' => 'isActive',
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
