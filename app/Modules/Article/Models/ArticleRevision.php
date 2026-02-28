<?php

namespace App\Modules\Article\Models;

use App\Modules\Auth\Models\User;
use App\Shared\Traits\UsesTenantConnection;
use Database\Factories\ArticleRevisionFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenant-scoped article revision model.
 *
 * Stores a full content snapshot each time an article is updated.
 *
 * @property int $id
 * @property int $article_id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property string|null $description
 * @property string|null $change_summary
 * @property \Illuminate\Support\Carbon $created_at
 */
#[UseFactory(ArticleRevisionFactory::class)]
class ArticleRevision extends Model
{
    use HasFactory, UsesTenantConnection;

    /** @var string */
    protected $table = 'article_revisions';

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'article_id',
        'user_id',
        'title',
        'content',
        'description',
        'change_summary',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
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
}
