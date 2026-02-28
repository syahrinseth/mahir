<?php

namespace App\Modules\Article\Models;

use App\Modules\Auth\Models\User;
use App\Shared\Traits\UsesTenantConnection;
use Database\Factories\ArticleCommentFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenant-scoped article comment model.
 *
 * Comments require approval before they are publicly visible.
 *
 * @property int $id
 * @property int $article_id
 * @property int|null $user_id
 * @property string $content
 * @property bool $is_approved
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
#[UseFactory(ArticleCommentFactory::class)]
class ArticleComment extends Model
{
    use HasFactory, UsesTenantConnection;

    /** @var string */
    protected $table = 'article_comments';

    /** @var list<string> */
    protected $fillable = [
        'article_id',
        'user_id',
        'content',
        'is_approved',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approve(): void
    {
        $this->update(['is_approved' => true]);
    }

    public function isPending(): bool
    {
        return ! $this->is_approved;
    }
}
