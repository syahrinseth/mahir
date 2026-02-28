<?php

namespace App\Modules\Portfolio\DTOs;

class CreatePortfolioCategoryDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly int $sortOrder = 0,
    ) {}

    /**
     * @param  array{user_id: int, name: string, slug: string, description?: string|null, sort_order?: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            sortOrder: $data['sort_order'] ?? 0,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sortOrder,
        ];
    }
}
