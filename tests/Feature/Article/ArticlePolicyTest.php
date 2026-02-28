<?php

use App\Modules\Article\Models\Article;
use App\Modules\Article\Models\ArticleComment;
use App\Modules\Auth\Models\User;

/*
|--------------------------------------------------------------------------
| ArticlePolicy
|--------------------------------------------------------------------------
*/

test('any user can view any articles', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', Article::class))->toBeTrue();
});

test('any user can view a specific article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create();

    expect($user->can('view', $article))->toBeTrue();
});

test('any user can create articles', function () {
    $user = User::factory()->create();

    expect($user->can('create', Article::class))->toBeTrue();
});

test('article author can update their article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    expect($user->can('update', $article))->toBeTrue();
});

test('non-author cannot update an article', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $other->id]);

    expect($user->can('update', $article))->toBeFalse();
});

test('article author can delete their article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    expect($user->can('delete', $article))->toBeTrue();
});

test('non-author cannot delete an article', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $other->id]);

    expect($user->can('delete', $article))->toBeFalse();
});

test('article author can publish their article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    expect($user->can('publish', $article))->toBeTrue();
});

test('non-author cannot publish an article', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $other->id]);

    expect($user->can('publish', $article))->toBeFalse();
});

test('article author can archive their article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    expect($user->can('archive', $article))->toBeTrue();
});

test('non-author cannot archive an article', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $other->id]);

    expect($user->can('archive', $article))->toBeFalse();
});

test('article author can restore revisions', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    expect($user->can('restoreRevision', $article))->toBeTrue();
});

test('non-author cannot restore revisions', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $other->id]);

    expect($user->can('restoreRevision', $article))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| ArticleCommentPolicy
|--------------------------------------------------------------------------
*/

test('any user can view comments', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', ArticleComment::class))->toBeTrue();
});

test('any user can create comments', function () {
    $user = User::factory()->create();

    expect($user->can('create', ArticleComment::class))->toBeTrue();
});

test('article author can approve comments on their article', function () {
    $author = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $author->id]);
    $comment = ArticleComment::factory()->create([
        'article_id' => $article->id,
        'user_id' => User::factory()->create()->id,
    ]);

    expect($author->can('approve', $comment))->toBeTrue();
});

test('non-article-author cannot approve comments', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $author->id]);
    $comment = ArticleComment::factory()->create([
        'article_id' => $article->id,
        'user_id' => $other->id,
    ]);

    expect($other->can('approve', $comment))->toBeFalse();
});

test('comment author can delete their own comment', function () {
    $commentAuthor = User::factory()->create();
    $article = Article::factory()->create();
    $comment = ArticleComment::factory()->create([
        'article_id' => $article->id,
        'user_id' => $commentAuthor->id,
    ]);

    expect($commentAuthor->can('delete', $comment))->toBeTrue();
});

test('article author can delete any comment on their article', function () {
    $articleAuthor = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $articleAuthor->id]);
    $comment = ArticleComment::factory()->create([
        'article_id' => $article->id,
        'user_id' => User::factory()->create()->id,
    ]);

    expect($articleAuthor->can('delete', $comment))->toBeTrue();
});

test('unrelated user cannot delete a comment', function () {
    $unrelated = User::factory()->create();
    $article = Article::factory()->create();
    $comment = ArticleComment::factory()->create([
        'article_id' => $article->id,
        'user_id' => User::factory()->create()->id,
    ]);

    expect($unrelated->can('delete', $comment))->toBeFalse();
});
