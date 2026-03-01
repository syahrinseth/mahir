<?php

namespace App\Support\MediaLibrary;

use App\Shared\Traits\UsesTenantConnection;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * Tenant-aware Media model.
 *
 * Uses the tenant database connection so media records are stored
 * in each tenant's own database, providing row-level isolation.
 */
class TenantAwareMedia extends BaseMedia
{
    use UsesTenantConnection;
}
