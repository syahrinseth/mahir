<?php

namespace App\Modules\Subscription\Models;

use App\Modules\Subscription\Enums\PlanType;
use App\Modules\Subscription\Enums\SubscriptionStatus;
use App\Modules\Tenancy\Models\Tenant;
use App\Shared\Traits\UsesLandlordConnection;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Subscription plan assigned to a tenant.
 *
 * Lives in the landlord database since it tracks billing
 * and plan information across all tenants.
 *
 * @property int $id
 * @property int $tenant_id
 * @property PlanType $plan
 * @property SubscriptionStatus $status
 * @property \Illuminate\Support\Carbon|null $trial_ends_at
 * @property \Illuminate\Support\Carbon|null $starts_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
#[UseFactory(SubscriptionFactory::class)]
class Subscription extends Model
{
    use HasFactory, UsesLandlordConnection;

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'plan',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'plan' => PlanType::class,
            'status' => SubscriptionStatus::class,
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active;
    }

    public function isOnTrial(): bool
    {
        return $this->status === SubscriptionStatus::Trial
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === SubscriptionStatus::Expired
            || ($this->ends_at && $this->ends_at->isPast());
    }
}
