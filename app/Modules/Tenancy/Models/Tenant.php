<?php

namespace App\Modules\Tenancy\Models;

use App\Modules\Subscription\Models\Subscription;
use App\Shared\Traits\UsesLandlordConnection;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

#[UseFactory(TenantFactory::class)]
class Tenant extends BaseTenant
{
    use HasFactory, UsesLandlordConnection;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the database name for this tenant.
     *
     * Used by Spatie's SwitchTenantDatabaseTask to set the
     * tenant connection's database at runtime.
     */
    public function getDatabaseName(): string
    {
        return $this->database;
    }

    /**
     * @return HasOne<Subscription, $this>
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }
}
