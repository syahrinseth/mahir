<?php

namespace App\Modules\Article\DTOs;

class CreateCommentDTO
{
    public function __construct(
        public readonly int $articleId,
        public readonly string $content,
        public readonly ?int $userId = null,
        public readonly bool $isApproved = false,
    ) {}

    /**
     * @param  array{article_id: int, content: string, user_id?: int|null, is_approved?: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            articleId: $data['article_id'],
            content: $data['content'],
            userId: $data['user_id'] ?? null,
            isApproved: $data['is_approved'] ?? false,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'content' => $this->content,
            'user_id' => $this->userId,
            'is_approved' => $this->isApproved,
        ];
    }
}
