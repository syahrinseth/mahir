<?php

namespace App\Modules\Portfolio\Models;

use App\Modules\Auth\Models\User;
use App\Shared\Traits\UsesTenantConnection;
use Database\Factories\PortfolioCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tenant-scoped portfolio category model.
 *
 * Organizes portfolio items into logical groups for display.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
#[UseFactory(PortfolioCategoryFactory::class)]
class PortfolioCategory extends Model
{
    use HasFactory, UsesTenantConnection;

    /** @var string */
    protected $table = 'portfolio_categories';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<Portfolio, $this>
     */
    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class, 'category_id');
    }

    /**
     * Get portfolios ordered by their sort position.
     *
     * @return HasMany<Portfolio, $this>
     */
    public function orderedPortfolios(): HasMany
    {
        return $this->portfolios()->orderBy('sort_order');
    }
}
