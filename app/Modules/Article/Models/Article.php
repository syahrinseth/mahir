<?php

namespace App\Modules\Article\Models;

use App\Modules\Article\Enums\ArticleStatus;
use App\Modules\Auth\Models\User;
use App\Shared\Traits\UsesTenantConnection;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Tenant-scoped article model.
 *
 * Each tenant has its own articles table in its own database.
 * Content is stored as Markdown for frontend-agnostic rendering.
 *
 * Media is handled by Spatie Media Library with two named collections:
 * - 'gallery' (multiple files: jpg, png, webp, gif, svg, pdf)
 * - 'featured' (single file: jpg, png, webp)
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $series_id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string|null $description
 * @property ArticleStatus $status
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int $views_count
 * @property int|null $series_order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
#[UseFactory(ArticleFactory::class)]
class Article extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, UsesTenantConnection;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'series_id',
        'title',
        'slug',
        'content',
        'description',
        'status',
        'published_at',
        'views_count',
        'series_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
            'views_count' => 'integer',
            'series_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
                'image/svg+xml',
                'application/pdf',
            ]);

        $this->addMediaCollection('featured')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(400)
            ->nonQueued();

        $this->addMediaConversion('display')
            ->width(1200)
            ->height(600)
            ->nonQueued();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<ArticleSeries, $this>
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(ArticleSeries::class, 'series_id');
    }

    /**
     * @return HasMany<ArticleComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ArticleComment::class);
    }

    /**
     * @return HasMany<ArticleRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(ArticleRevision::class);
    }

    public function isDraft(): bool
    {
        return $this->status === ArticleStatus::Draft;
    }

    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::Published;
    }

    public function isArchived(): bool
    {
        return $this->status === ArticleStatus::Archived;
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
