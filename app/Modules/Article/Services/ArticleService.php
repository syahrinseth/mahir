<?php

namespace App\Modules\Article\Services;

use App\Modules\Article\DTOs\CreateArticleDTO;
use App\Modules\Article\DTOs\CreateCommentDTO;
use App\Modules\Article\DTOs\CreateSeriesDTO;
use App\Modules\Article\DTOs\UpdateArticleDTO;
use App\Modules\Article\DTOs\UpdateSeriesDTO;
use App\Modules\Article\Enums\ArticleStatus;
use App\Modules\Article\Models\Article;
use App\Modules\Article\Models\ArticleComment;
use App\Modules\Article\Models\ArticleRevision;
use App\Modules\Article\Models\ArticleSeries;
use App\Modules\Article\Repositories\ArticleRepository;
use App\Modules\Article\Repositories\ArticleSeriesRepository;
use App\Shared\Contracts\ServiceContract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Business logic for managing articles, series, comments, and revisions.
 */
class ArticleService implements ServiceContract
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private ArticleSeriesRepository $seriesRepository,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Articles
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new article.
     */
    public function createArticle(CreateArticleDTO $dto): Article
    {
        return $this->articleRepository->create($dto->toArray());
    }

    /**
     * Update an existing article and store a revision snapshot.
     */
    public function updateArticle(int $id, UpdateArticleDTO $dto, int $editorId): ?Article
    {
        $article = $this->articleRepository->findById($id);

        if (! $article) {
            return null;
        }

        $this->createRevisionFromArticle($article, $editorId);

        $updated = $this->articleRepository->update($id, $dto->toArray());

        return $updated instanceof Article ? $updated : null;
    }

    /**
     * Publish an article by setting its status and publish date.
     */
    public function publishArticle(int $id): ?Article
    {
        $article = $this->articleRepository->findById($id);

        if (! $article) {
            return null;
        }

        $dto = new UpdateArticleDTO(
            status: ArticleStatus::Published,
            publishedAt: now()->toDateTimeString(),
        );

        $updated = $this->articleRepository->update($id, $dto->toArray());

        return $updated instanceof Article ? $updated : null;
    }

    /**
     * Archive an article.
     */
    public function archiveArticle(int $id): ?Article
    {
        $dto = new UpdateArticleDTO(
            status: ArticleStatus::Archived,
        );

        $updated = $this->articleRepository->update($id, $dto->toArray());

        return $updated instanceof Article ? $updated : null;
    }

    /**
     * Delete an article.
     */
    public function deleteArticle(int $id): bool
    {
        return $this->articleRepository->delete($id);
    }

    /*
    |--------------------------------------------------------------------------
    | Series
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new article series.
     */
    public function createSeries(CreateSeriesDTO $dto): ArticleSeries
    {
        return $this->seriesRepository->create($dto->toArray());
    }

    /**
     * Update an existing article series.
     */
    public function updateSeries(int $id, UpdateSeriesDTO $dto): ?ArticleSeries
    {
        $series = $this->seriesRepository->update($id, $dto->toArray());

        return $series instanceof ArticleSeries ? $series : null;
    }

    /**
     * Delete an article series.
     */
    public function deleteSeries(int $id): bool
    {
        return $this->seriesRepository->delete($id);
    }

    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    */

    /**
     * Add a comment to an article.
     */
    public function addComment(CreateCommentDTO $dto): ArticleComment
    {
        return ArticleComment::query()->create($dto->toArray());
    }

    /**
     * Approve a pending comment.
     */
    public function approveComment(int $commentId): ?ArticleComment
    {
        $comment = ArticleComment::query()->find($commentId);

        if (! $comment) {
            return null;
        }

        $comment->approve();

        return $comment->fresh();
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(int $commentId): bool
    {
        $comment = ArticleComment::query()->find($commentId);

        if (! $comment) {
            return false;
        }

        return (bool) $comment->delete();
    }

    /**
     * Get comments for an article.
     *
     * @return Collection<int, ArticleComment>
     */
    public function getCommentsForArticle(int $articleId): Collection
    {
        return ArticleComment::query()
            ->where('article_id', $articleId)
            ->with('author')
            ->latest()
            ->get();
    }

    /**
     * Get approved comments for an article (public-facing).
     *
     * @return Collection<int, ArticleComment>
     */
    public function getApprovedCommentsForArticle(int $articleId): Collection
    {
        return ArticleComment::query()
            ->where('article_id', $articleId)
            ->where('is_approved', true)
            ->with('author')
            ->latest()
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Revisions
    |--------------------------------------------------------------------------
    */

    /**
     * Get revisions for an article.
     *
     * @return Collection<int, ArticleRevision>
     */
    public function getRevisionsForArticle(int $articleId): Collection
    {
        return ArticleRevision::query()
            ->where('article_id', $articleId)
            ->with('author')
            ->latest('created_at')
            ->get();
    }

    /**
     * Restore an article to a previous revision.
     */
    public function restoreRevision(int $articleId, int $revisionId, int $editorId): ?Article
    {
        $revision = ArticleRevision::query()->find($revisionId);

        if (! $revision || $revision->article_id !== $articleId) {
            return null;
        }

        $article = $this->articleRepository->findById($articleId);

        if (! $article) {
            return null;
        }

        $this->createRevisionFromArticle($article, $editorId, 'Restored from revision #'.$revisionId);

        $dto = new UpdateArticleDTO(
            title: $revision->title,
            content: $revision->content,
            description: $revision->description,
        );

        $updated = $this->articleRepository->update($articleId, $dto->toArray());

        return $updated instanceof Article ? $updated : null;
    }

    /**
     * Create a revision snapshot from the current article state.
     */
    private function createRevisionFromArticle(Article $article, int $editorId, ?string $changeSummary = null): ArticleRevision
    {
        return ArticleRevision::query()->create([
            'article_id' => $article->id,
            'user_id' => $editorId,
            'title' => $article->title,
            'content' => $article->content,
            'description' => $article->description,
            'change_summary' => $changeSummary,
            'created_at' => now(),
        ]);
    }
}
