<?php

namespace App\Modules\Portfolio\Models;

use App\Modules\Auth\Models\User;
use App\Shared\Traits\UsesTenantConnection;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Tenant-scoped testimonial model.
 *
 * Each tenant has its own testimonials table in its own database.
 * Used to showcase client reviews and feedback, optionally tied to a portfolio project.
 *
 * Media is handled by Spatie Media Library with one named collection:
 * - 'featured' (single file: jpg, png, webp) for client headshot or logo
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $portfolio_id
 * @property string $client_name
 * @property string|null $client_position
 * @property string|null $client_company
 * @property string $content
 * @property int|null $rating
 * @property bool $is_featured
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
#[UseFactory(TestimonialFactory::class)]
class Testimonial extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, UsesTenantConnection;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'portfolio_id',
        'client_name',
        'client_position',
        'client_company',
        'content',
        'rating',
        'is_featured',
        'sort_order',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
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
     * @return BelongsTo<Portfolio, $this>
     */
    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class, 'portfolio_id');
    }

    public function isDraft(): bool
    {
        return $this->published_at === null;
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}
