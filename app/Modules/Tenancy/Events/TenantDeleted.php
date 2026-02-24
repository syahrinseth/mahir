<?php

namespace App\Modules\Tenancy\Events;

use Illuminate\Foundation\Events\Dispatchable;

class TenantDeleted
{
    use Dispatchable;

    public function __construct(
        public string $tenantName,
        public string $databaseName,
    ) {}
}
