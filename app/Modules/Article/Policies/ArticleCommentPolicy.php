<?php

namespace App\Modules\Article\Policies;

use App\Modules\Article\Models\ArticleComment;
use App\Modules\Auth\Models\User;

/**
 * Policy for authorizing actions on article comments.
 *
 * Any authenticated tenant user can view and create comments.
 * Only the comment author or the article author can delete a comment.
 */
class ArticleCommentPolicy
{
    /**
     * Determine whether the user can view comments for an article.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create a comment.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can approve a comment.
     */
    public function approve(User $user, ArticleComment $comment): bool
    {
        return $user->id === $comment->article->user_id;
    }

    /**
     * Determine whether the user can delete a comment.
     *
     * Either the comment author or the article author may delete it.
     */
    public function delete(User $user, ArticleComment $comment): bool
    {
        if ($user->id === $comment->user_id) {
            return true;
        }

        return $user->id === $comment->article->user_id;
    }
}
