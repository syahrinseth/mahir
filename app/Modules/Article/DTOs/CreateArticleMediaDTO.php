<?php

namespace App\Modules\Article\DTOs;

class CreateArticleMediaDTO
{
    public function __construct(
        public readonly ?string $caption = null,
        public readonly ?string $altText = null,
        public readonly ?int $sortOrder = null,
    ) {}

    /**
     * @param  array{caption?: string|null, alt_text?: string|null, sort_order?: int|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            caption: $data['caption'] ?? null,
            altText: $data['alt_text'] ?? null,
            sortOrder: $data['sort_order'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'caption' => $this->caption,
            'alt_text' => $this->altText,
            'sort_order' => $this->sortOrder,
        ], fn ($value) => $value !== null);
    }
}
