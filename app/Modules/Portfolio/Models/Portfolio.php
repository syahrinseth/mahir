<?php

namespace App\Modules\Portfolio\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Enums\PortfolioStatus;
use App\Shared\Traits\UsesTenantConnection;
use Database\Factories\PortfolioFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Tenant-scoped portfolio model.
 *
 * Each tenant has its own portfolios table in its own database.
 * Used to showcase projects and work with rich media galleries.
 *
 * Media is handled by Spatie Media Library with two named collections:
 * - 'gallery' (multiple files: jpg, png, webp, gif, svg, pdf)
 * - 'featured' (single file: jpg, png, webp)
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string|null $client_name
 * @property string|null $project_url
 * @property string|null $featured_image
 * @property array<int, string>|null $technologies
 * @property PortfolioStatus $status
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
#[UseFactory(PortfolioFactory::class)]
class Portfolio extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, UsesTenantConnection;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'client_name',
        'project_url',
        'featured_image',
        'technologies',
        'status',
        'sort_order',
        'started_at',
        'ended_at',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PortfolioStatus::class,
            'technologies' => 'array',
            'sort_order' => 'integer',
            'started_at' => 'date',
            'ended_at' => 'date',
            'published_at' => 'datetime',
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
     * @return BelongsTo<PortfolioCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PortfolioCategory::class, 'category_id');
    }

    /**
     * @return HasMany<Testimonial, $this>
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class, 'portfolio_id');
    }

    public function isDraft(): bool
    {
        return $this->status === PortfolioStatus::Draft;
    }

    public function isPublished(): bool
    {
        return $this->status === PortfolioStatus::Published;
    }

    public function isArchived(): bool
    {
        return $this->status === PortfolioStatus::Archived;
    }
}
