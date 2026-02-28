<?php

namespace App\Modules\Article\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Article\Models\ArticleRevision;
use App\Modules\Article\Repositories\ArticleRepository;
use App\Modules\Article\Services\ArticleService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Article Revisions', weight: 6)]
class ArticleRevisionController extends Controller
{
    public function __construct(
        private ArticleService $articleService,
        private ArticleRepository $articleRepository,
    ) {}

    /**
     * List revisions for an article.
     *
     * Retrieves all revision snapshots for a given article, ordered by most recent first.
     *
     * @param  int  $articleId  The article ID.
     */
    #[PathParameter('article', description: 'The article ID.', type: 'int', example: 1)]
    public function index(int $articleId): JsonResponse
    {
        $article = $this->articleRepository->findById($articleId);

        if (! $article) {
            abort(404, 'Article not found.');
        }

        $revisions = $this->articleService->getRevisionsForArticle($articleId);

        /**
         * List of revisions for the article.
         *
         * @body array{data: array<int, array{id: int, article_id: int, user_id: int, title: string, content: string, description: ?string, change_summary: ?string, created_at: string}>}
         */
        return response()->json([
            'data' => $revisions,
        ]);
    }

    /**
     * Get a specific revision.
     *
     * Retrieves a single revision snapshot by its ID.
     *
     * @param  int  $articleId  The article ID.
     * @param  int  $revisionId  The revision ID.
     */
    #[PathParameter('article', description: 'The article ID.', type: 'int', example: 1)]
    #[PathParameter('revision', description: 'The revision ID.', type: 'int', example: 1)]
    public function show(int $articleId, int $revisionId): JsonResponse
    {
        $revision = ArticleRevision::query()->find($revisionId);

        if (! $revision || $revision->article_id !== $articleId) {
            abort(404, 'Revision not found.');
        }

        /**
         * Revision details.
         *
         * @body array{data: array{id: int, article_id: int, user_id: int, title: string, content: string, description: ?string, change_summary: ?string, created_at: string}}
         */
        return response()->json([
            'data' => $revision->load('author'),
        ]);
    }

    /**
     * Restore an article to a previous revision.
     *
     * Rolls back the article content to match the specified revision. A new revision
     * snapshot is created before restoring so the current state is not lost.
     *
     * @param  int  $articleId  The article ID.
     * @param  int  $revisionId  The revision ID to restore.
     */
    #[PathParameter('article', description: 'The article ID.', type: 'int', example: 1)]
    #[PathParameter('revision', description: 'The revision ID to restore.', type: 'int', example: 1)]
    public function restore(Request $request, int $articleId, int $revisionId): JsonResponse
    {
        $article = $this->articleService->restoreRevision($articleId, $revisionId, $request->user()->id);

        if (! $article) {
            abort(404, 'Article or revision not found.');
        }

        /**
         * Article restored from revision successfully.
         *
         * @body array{message: string, data: array{id: int, title: string, content: string, description: ?string, status: string}}
         */
        return response()->json([
            'message' => 'Article restored from revision successfully.',
            'data' => $article,
        ]);
    }
}
