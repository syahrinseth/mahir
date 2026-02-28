<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Tenancy\Models\Tenant;

test('the application returns a successful response', function () {
    $this->withoutMiddleware(IdentifyTenant::class);

    $tenant = Tenant::factory()->create();
    $tenantBaseUrl = 'http://'.$tenant->slug.'.'.config('multitenancy.base_domain');

    $response = $this->get($tenantBaseUrl.'/');

    $response->assertSuccessful();
});
