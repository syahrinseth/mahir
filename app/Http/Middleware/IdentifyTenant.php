<?php

namespace App\Http\Middleware;

use App\Modules\Tenancy\TenantFinder\SubdomainTenantFinder;
use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Identifies the current tenant from the request and makes it current.
 *
 * Uses the configured TenantFinder to resolve the tenant from the subdomain.
 * Requests to reserved subdomains (e.g. admin, www) or the base domain are
 * allowed through without tenant resolution. Only requests that target an
 * actual tenant subdomain will abort with 404 if the tenant is not found.
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

        if ($tenantFinder instanceof SubdomainTenantFinder && ! $tenantFinder->requiresTenant($request)) {
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
