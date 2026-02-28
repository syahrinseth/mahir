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

/**
 * Tenant-scoped portfolio model.
 *
 * Each tenant has its own portfolios table in its own database.
 * Used to showcase projects and work with rich media galleries.
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
class Portfolio extends Model
{
    use HasFactory, UsesTenantConnection;

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
     * @return HasMany<PortfolioMedia, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(PortfolioMedia::class);
    }

    /**
     * Get media ordered by their sort position.
     *
     * @return HasMany<PortfolioMedia, $this>
     */
    public function orderedMedia(): HasMany
    {
        return $this->media()->orderBy('sort_order');
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
