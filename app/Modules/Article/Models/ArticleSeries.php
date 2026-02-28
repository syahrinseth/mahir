<?php

namespace App\Modules\Article\Models;

use App\Modules\Auth\Models\User;
use App\Shared\Traits\UsesTenantConnection;
use Database\Factories\ArticleSeriesFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tenant-scoped article series model.
 *
 * Groups related articles into an ordered collection.
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
#[UseFactory(ArticleSeriesFactory::class)]
class ArticleSeries extends Model
{
    use HasFactory, UsesTenantConnection;

    /** @var string */
    protected $table = 'article_series';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'series_id');
    }

    /**
     * Get articles ordered by their series position.
     *
     * @return HasMany<Article, $this>
     */
    public function orderedArticles(): HasMany
    {
        return $this->articles()->orderBy('series_order');
    }
}
