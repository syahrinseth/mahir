<?php

namespace App\Modules\Article\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Article\DTOs\CreateCommentDTO;
use App\Modules\Article\Http\Requests\CreateCommentRequest;
use App\Modules\Article\Repositories\ArticleRepository;
use App\Modules\Article\Services\ArticleService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Article Comments', weight: 5)]
class ArticleCommentController extends Controller
{
    public function __construct(
        private ArticleService $articleService,
        private ArticleRepository $articleRepository,
    ) {}

    /**
     * List comments for an article.
     *
     * Retrieves comments for a given article.
     * Unauthenticated requests only see approved comments on published articles.
     *
     * @param  int  $articleId  The article ID.
     */
    #[PathParameter('article', description: 'The article ID.', type: 'int', example: 1)]
    public function index(Request $request, int $articleId): JsonResponse
    {
        $article = $request->user()
            ? $this->articleRepository->findById($articleId)
            : $this->articleRepository->findPublishedById($articleId);

        if (! $article) {
            abort(404, 'Article not found.');
        }

        $comments = $request->user()
            ? $this->articleService->getCommentsForArticle($articleId)
            : $this->articleService->getApprovedCommentsForArticle($articleId);

        /**
         * List of comments for the article.
         *
         * @body array{data: array<int, array{id: int, article_id: int, user_id: ?int, content: string, is_approved: bool, created_at: string, updated_at: string}>}
         */
        return response()->json([
            'data' => $comments,
        ]);
    }

    /**
     * Add a comment to an article.
     *
     * Creates a new comment on the specified article. Comments require approval before becoming visible.
     *
     * @param  int  $articleId  The article ID.
     */
    #[PathParameter('article', description: 'The article ID.', type: 'int', example: 1)]
    #[Response(422, description: 'Validation error.', type: 'array{message: string, errors: array<string, string[]>}')]
    public function store(CreateCommentRequest $request, int $articleId): JsonResponse
    {
        $article = $this->articleRepository->findById($articleId);

        if (! $article) {
            abort(404, 'Article not found.');
        }

        $dto = CreateCommentDTO::fromArray([
            'article_id' => $articleId,
            'user_id' => $request->user()->id,
            'content' => $request->validated('content'),
        ]);

        $comment = $this->articleService->addComment($dto);

        /**
         * Comment created successfully.
         *
         * @status 201
         *
         * @body array{message: string, data: array{id: int, article_id: int, user_id: ?int, content: string, is_approved: bool, created_at: string, updated_at: string}}
         */
        return response()->json([
            'message' => 'Comment added successfully.',
            'data' => $comment->load('author'),
        ], 201);
    }

    /**
     * Delete a comment.
     *
     * Permanently deletes a comment from the article. Only the comment author or article author may delete.
     *
     * @param  int  $articleId  The article ID.
     * @param  int  $commentId  The comment ID.
     */
    #[PathParameter('article', description: 'The article ID.', type: 'int', example: 1)]
    #[PathParameter('comment', description: 'The comment ID.', type: 'int', example: 1)]
    public function destroy(int $articleId, int $commentId): JsonResponse
    {
        $deleted = $this->articleService->deleteComment($commentId);

        if (! $deleted) {
            abort(404, 'Comment not found.');
        }

        /**
         * Comment deleted successfully.
         *
         * @body array{message: string}
         */
        return response()->json([
            'message' => 'Comment deleted successfully.',
        ]);
    }
}
