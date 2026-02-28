<?php

namespace App\Modules\Portfolio\DTOs;

class UpdatePortfolioCategoryDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $slug = null,
        public readonly ?string $description = null,
        public readonly ?int $sortOrder = null,
    ) {}

    /**
     * @param  array{name?: string, slug?: string, description?: string|null, sort_order?: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
            sortOrder: $data['sort_order'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sortOrder,
        ], fn (mixed $value): bool => $value !== null);
    }
}
