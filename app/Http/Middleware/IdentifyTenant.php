<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Identifies the current tenant from the request and makes it current.
 *
 * Uses the configured TenantFinder to resolve the tenant from the subdomain.
 * If no tenant is found, returns a 404 JSON response.
 */
class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var TenantFinder|null $tenantFinder */
        $tenantFinder = app(TenantFinder::class);

        if (! $tenantFinder) {
            return $next($request);
        }

        $tenant = $tenantFinder->findForRequest($request);

        if (! $tenant) {
            abort(Response::HTTP_NOT_FOUND, 'Tenant not found.');
        }

        $tenant->makeCurrent();

        return $next($request);
    }
}
