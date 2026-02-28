<?php

namespace App\Modules\Article\DTOs;

class CreateSeriesDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $description = null,
    ) {}

    /**
     * @param  array{user_id: int, title: string, slug: string, description?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            title: $data['title'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
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
            'description' => $this->description,
        ];
    }
}
