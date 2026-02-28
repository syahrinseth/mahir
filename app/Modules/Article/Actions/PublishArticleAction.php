<?php

namespace App\Modules\Article\Actions;

use App\Modules\Article\Models\Article;
use App\Modules\Article\Services\ArticleService;
use App\Shared\Contracts\ActionContract;

/**
 * Publish an article by setting its status and publish date.
 */
class PublishArticleAction implements ActionContract
{
    public function __construct(
        private ArticleService $articleService,
    ) {}

    public function execute(int $id): ?Article
    {
        return $this->articleService->publishArticle($id);
    }
}
