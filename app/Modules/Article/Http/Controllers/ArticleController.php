<?php

namespace App\Modules\Article\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Article\DTOs\CreateArticleDTO;
use App\Modules\Article\DTOs\UpdateArticleDTO;
use App\Modules\Article\Http\Requests\CreateArticleRequest;
use App\Modules\Article\Http\Requests\UpdateArticleRequest;
use App\Modules\Article\Repositories\ArticleRepository;
use App\Modules\Article\Services\ArticleService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

#[Group('Articles', weight: 3)]
class ArticleController extends Controller
{
    public function __construct(
        private ArticleService $articleService,
        private ArticleRepository $repository,
    ) {}

    /**
     * List all articles.
     *
     * Retrieves a list of all articles for the current tenant.
     */
    public function index(): JsonResponse
    {
        $articles = $this->repository->all();

        /**
         * List of all articles.
         *
         * @body array{data: array<int, array{id: int, user_id: int, title: string, slug: string, status: string, published_at: ?string, views_count: int, created_at: string, updated_at: string}>}
         */
        return response()->json([
            'data' => $articles,
        ]);
    }

    /**
     * Create a new article.
     *
     * Creates a new article for the authenticated user. Content should be in Markdown format.
     */
    #[Response(422, description: 'Validation error.', type: 'array{message: string, errors: array<string, string[]>}')]
    public function store(CreateArticleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

        $dto = CreateArticleDTO::fromArray($data);
        $article = $this->articleService->createArticle($dto);

        /**
         * Article created successfully.
         *
         * @status 201
         *
         * @body array{message: string, data: array{id: int, user_id: int, title: string, slug: string, content: string, description: ?string, status: string, featured_image: ?string, published_at: ?string, views_count: int, created_at: string, updated_at: string}}
         */
        return response()->json([
            'message' => 'Article created successfully.',
            'data' => $article->load('author'),
        ], 201);
    }

    /**
     * Get an article.
     *
     * Retrieves a single article by its ID and increments the view counter.
     *
     * @param  int  $id  The article ID.
     */
    #[PathParameter('article', description: 'The article ID.', type: 'int', example: 1)]
    public function show(int $id): JsonResponse
    {
        $article = $this->repository->findById($id);

        if (! $article) {
            abort(404, 'Article not found.');
        }

        $article->incrementViews();

        /**
         * Article details.
         *
         * @body array{data: array{id: int, user_id: int, title: string, slug: string, content: string, description: ?string, status: string, featured_image: ?string, published_at: ?string, views_count: int, created_at: string, updated_at: string}}
         */
        return response()->json([
            'data' => $article->load(['author', 'series']),
        ]);
    }

    /**
     * Update an article.
     *
     * Updates an existing article. Only the provided fields will be updated.
     * A revision snapshot is created before the update.
     *
     * @param  int  $id  The article ID.
     */
    #[PathParameter('article', description: 'The article ID.', type: 'int', example: 1)]
    #[Response(422, description: 'Validation error.', type: 'array{message: string, errors: array<string, string[]>}')]
    public function update(UpdateArticleRequest $request, int $id): JsonResponse
    {
        $dto = UpdateArticleDTO::fromArray($request->validated());
        $article = $this->articleService->updateArticle($id, $dto, $request->user()->id);

        if (! $article) {
            abort(404, 'Article not found.');
        }

        /**
         * Article updated successfully.
         *
         * @body array{message: string, data: array{id: int, user_id: int, title: string, slug: string, content: string, description: ?string, status: string, featured_image: ?string, published_at: ?string, views_count: int, created_at: string, updated_at: string}}
         */
        return response()->json([
            'message' => 'Article updated successfully.',
            'data' => $article->load(['author', 'series']),
        ]);
    }

    /**
     * Delete an article.
     *
     * Permanently deletes an article and all its comments and revisions.
     *
     * @param  int  $id  The article ID.
     */
    #[PathParameter('article', description: 'The article ID.', type: 'int', example: 1)]
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->repository->delete($id);

        if (! $deleted) {
            abort(404, 'Article not found.');
        }

        /**
         * Article deleted successfully.
         *
         * @body array{message: string}
         */
        return response()->json([
            'message' => 'Article deleted successfully.',
        ]);
    }

    /**
     * Publish an article.
     *
     * Sets the article status to published and records the publish date.
     *
     * @param  int  $id  The article ID.
     */
    #[PathParameter('article', description: 'The article ID.', type: 'int', example: 1)]
    public function publish(int $id): JsonResponse
    {
        $article = $this->articleService->publishArticle($id);

        if (! $article) {
            abort(404, 'Article not found.');
        }

        /**
         * Article published successfully.
         *
         * @body array{message: string, data: array{id: int, status: string, published_at: string}}
         */
        return response()->json([
            'message' => 'Article published successfully.',
            'data' => $article,
        ]);
    }

    /**
     * Archive an article.
     *
     * Sets the article status to archived, removing it from public visibility.
     *
     * @param  int  $id  The article ID.
     */
    #[PathParameter('article', description: 'The article ID.', type: 'int', example: 1)]
    public function archive(int $id): JsonResponse
    {
        $article = $this->articleService->archiveArticle($id);

        if (! $article) {
            abort(404, 'Article not found.');
        }

        /**
         * Article archived successfully.
         *
         * @body array{message: string, data: array{id: int, status: string}}
         */
        return response()->json([
            'message' => 'Article archived successfully.',
            'data' => $article,
        ]);
    }
}
