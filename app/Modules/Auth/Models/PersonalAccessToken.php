<?php

namespace App\Modules\Auth\Models;

use App\Shared\Traits\UsesTenantConnection;
use Database\Factories\PersonalAccessTokenFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * Tenant-scoped personal access token model.
 *
 * Ensures Sanctum tokens are stored in and retrieved from
 * the tenant database, not the landlord database.
 */
#[UseFactory(PersonalAccessTokenFactory::class)]
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use HasFactory, UsesTenantConnection;
}
