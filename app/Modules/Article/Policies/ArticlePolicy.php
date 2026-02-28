<?php

namespace App\Modules\Article\Policies;

use App\Modules\Article\Models\Article;
use App\Modules\Auth\Models\User;

/**
 * Policy for authorizing actions on articles.
 *
 * Tenant users can view and create articles freely.
 * Only the article author can update, delete, publish, or archive.
 */
class ArticlePolicy
{
    /**
     * Determine whether the user can view a list of articles.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific article.
     */
    public function view(User $user, Article $article): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create articles.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the given article.
     */
    public function update(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }

    /**
     * Determine whether the user can delete the given article.
     */
    public function delete(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }

    /**
     * Determine whether the user can publish the given article.
     */
    public function publish(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }

    /**
     * Determine whether the user can archive the given article.
     */
    public function archive(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }

    /**
     * Determine whether the user can restore a revision for the given article.
     */
    public function restoreRevision(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }
}
