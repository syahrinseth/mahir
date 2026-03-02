<?php

namespace App\Modules\Article\Actions;

use App\Modules\Article\DTOs\UpdateArticleDTO;
use App\Modules\Article\Models\Article;
use App\Modules\Article\Services\ArticleService;
use App\Shared\Contracts\ActionContract;

/**
 * Update an existing article and create a revision snapshot.
 */
class UpdateArticleAction implements ActionContract
{
    public function __construct(
        private ArticleService $articleService,
    ) {}

    /**
     * @param  array{title?: string, slug?: string, content?: string, description?: string|null, status?: string, published_at?: string|null, series_id?: int|null, series_order?: int|null}  $data
     */
    public function execute(int $id, array $data, int $editorId): ?Article
    {
        $dto = UpdateArticleDTO::fromArray($data);

        return $this->articleService->updateArticle($id, $dto, $editorId);
    }
}
