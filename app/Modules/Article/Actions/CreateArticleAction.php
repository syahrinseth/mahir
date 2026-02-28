<?php

namespace App\Modules\Article\Actions;

use App\Modules\Article\DTOs\CreateArticleDTO;
use App\Modules\Article\Models\Article;
use App\Modules\Article\Services\ArticleService;
use App\Shared\Contracts\ActionContract;

/**
 * Create a new article.
 */
class CreateArticleAction implements ActionContract
{
    public function __construct(
        private ArticleService $articleService,
    ) {}

    /**
     * @param  array{user_id: int, title: string, slug: string, content: string, description?: string|null, status?: string, featured_image?: string|null, published_at?: string|null, series_id?: int|null, series_order?: int|null}  $data
     */
    public function execute(array $data): Article
    {
        $dto = CreateArticleDTO::fromArray($data);

        return $this->articleService->createArticle($dto);
    }
}
