<?php

namespace App\Modules\Article\Actions;

use App\Modules\Article\Services\ArticleService;
use App\Shared\Contracts\ActionContract;

/**
 * Delete an article permanently.
 */
class DeleteArticleAction implements ActionContract
{
    public function __construct(
        private ArticleService $articleService,
    ) {}

    public function execute(int $id): bool
    {
        return $this->articleService->deleteArticle($id);
    }
}
