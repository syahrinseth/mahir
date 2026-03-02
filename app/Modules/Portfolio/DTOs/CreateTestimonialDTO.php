<?php

namespace App\Modules\Portfolio\DTOs;

class CreateTestimonialDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $clientName,
        public readonly string $content,
        public readonly ?int $portfolioId = null,
        public readonly ?string $clientPosition = null,
        public readonly ?string $clientCompany = null,
        public readonly ?int $rating = null,
        public readonly bool $isFeatured = false,
        public readonly int $sortOrder = 0,
        public readonly ?string $publishedAt = null,
    ) {}

    /**
     * @param  array{user_id: int, client_name: string, content: string, portfolio_id?: int|null, client_position?: string|null, client_company?: string|null, rating?: int|null, is_featured?: bool, sort_order?: int, published_at?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            clientName: $data['client_name'],
            content: $data['content'],
            portfolioId: $data['portfolio_id'] ?? null,
            clientPosition: $data['client_position'] ?? null,
            clientCompany: $data['client_company'] ?? null,
            rating: $data['rating'] ?? null,
            isFeatured: $data['is_featured'] ?? false,
            sortOrder: $data['sort_order'] ?? 0,
            publishedAt: $data['published_at'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'client_name' => $this->clientName,
            'content' => $this->content,
            'portfolio_id' => $this->portfolioId,
            'client_position' => $this->clientPosition,
            'client_company' => $this->clientCompany,
            'rating' => $this->rating,
            'is_featured' => $this->isFeatured,
            'sort_order' => $this->sortOrder,
            'published_at' => $this->publishedAt,
        ];
    }
}
