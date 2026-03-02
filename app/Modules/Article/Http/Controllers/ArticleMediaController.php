<?php

namespace App\Modules\Article\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Article\Http\Requests\StoreArticleMediaRequest;
use App\Modules\Article\Repositories\ArticleRepository;
use App\Modules\Article\Services\ArticleService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Article Media', weight: 4)]
class ArticleMediaController extends Controller
{
    public function __construct(
        private ArticleService $articleService,
        private ArticleRepository $articleRepository,
    ) {}

    /**
     * List all media for an article.
     *
     * Unauthenticated requests can only access media for published articles.
     * Pass `?collection=gallery` or `?collection=featured` to filter by collection.
     */
    public function index(Request $request, int $articleId): JsonResponse
    {
        $article = $request->user()
            ? $this->articleRepository->findById($articleId)
            : $this->articleRepository->findPublishedById($articleId);

        if (! $article) {
            abort(404, 'Article not found.');
        }

        /** @var string|null $collection Filter by media collection name (gallery, featured). */
        $collection = $request->query('collection');

        $media = $collection
            ? $this->articleService->getMediaForArticle($article, $collection)
            : $article->getMedia('*');

        return response()->json(['data' => $media]);
    }

    /**
     * Upload a media file for an article.
     */
    public function store(StoreArticleMediaRequest $request, int $articleId): JsonResponse
    {
        $article = $this->articleRepository->findById($articleId);

        if (! $article) {
            abort(404, 'Article not found.');
        }

        $media = $this->articleService->addMediaToArticle(
            article: $article,
            file: $request->file('file'),
            collection: $request->validated('collection', 'gallery'),
            properties: [
                'caption' => $request->validated('caption'),
                'alt_text' => $request->validated('alt_text'),
                'sort_order' => $request->validated('sort_order', 0),
            ],
        );

        return response()->json([
            'message' => 'Media uploaded successfully.',
            'data' => $media,
        ], 201);
    }

    /**
     * Delete a media item from an article.
     */
    public function destroy(int $articleId, int $mediaId): JsonResponse
    {
        $deleted = $this->articleService->deleteMedia($mediaId);

        if (! $deleted) {
            abort(404, 'Media not found.');
        }

        return response()->json(['message' => 'Media deleted successfully.']);
    }

    /**
     * Reorder media items for an article.
     */
    public function reorder(Request $request, int $articleId): JsonResponse
    {
        $article = $this->articleRepository->findById($articleId);

        if (! $article) {
            abort(404, 'Article not found.');
        }

        $validated = $request->validate([
            /** Ordered list of media IDs. */
            'media_ids' => ['required', 'array'],
            /** Each media ID. */
            'media_ids.*' => ['integer'],
        ]);

        $this->articleService->reorderMedia($article, $validated['media_ids']);

        return response()->json(['message' => 'Media reordered successfully.']);
    }
}
