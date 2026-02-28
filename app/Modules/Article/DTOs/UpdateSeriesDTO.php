<?php

namespace App\Modules\Article\DTOs;

class UpdateSeriesDTO
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $slug = null,
        public readonly ?string $description = null,
    ) {}

    /**
     * @param  array{title?: string, slug?: string, description?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
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
            'description' => $this->description,
        ], fn (mixed $value): bool => $value !== null);
    }
}
