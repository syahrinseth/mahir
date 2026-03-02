<?php

namespace App\Modules\Portfolio\DTOs;

class UpdateTestimonialDTO
{
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
    ) {}

    /**
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
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'client_name' => $this->clientName,
            'content' => $this->content,
            'portfolio_id' => $this->portfolioId,
            'client_position' => $this->clientPosition,
            'client_company' => $this->clientCompany,
            'rating' => $this->rating,
            'is_featured' => $this->isFeatured,
            'sort_order' => $this->sortOrder,
            'published_at' => $this->publishedAt,
        ], fn (mixed $value): bool => $value !== null);
    }
}
