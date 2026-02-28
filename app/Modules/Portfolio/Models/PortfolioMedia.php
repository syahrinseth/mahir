<?php

namespace App\Modules\Portfolio\Models;

use App\Shared\Traits\UsesTenantConnection;
use Database\Factories\PortfolioMediaFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenant-scoped portfolio media model.
 *
 * Stores gallery images and files associated with a portfolio item.
 *
 * @property int $id
 * @property int $portfolio_id
 * @property string $file_path
 * @property string $file_name
 * @property string $mime_type
 * @property int $file_size
 * @property int $sort_order
 * @property string|null $caption
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
#[UseFactory(PortfolioMediaFactory::class)]
class PortfolioMedia extends Model
{
    use HasFactory, UsesTenantConnection;

    /** @var string */
    protected $table = 'portfolio_media';

    /** @var list<string> */
    protected $fillable = [
        'portfolio_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'sort_order',
        'caption',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Portfolio, $this>
     */
    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
