<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the request is being served in the context of a resolved tenant.
 *
 * Prevents the tenant panel from being accessed on the landlord domain
 * (e.g. admin.mahir.test) where no tenant subdomain is present.
 */
class EnsureTenantPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Tenant::current() === null) {
            abort(Response::HTTP_FORBIDDEN, 'No tenant resolved for this request.');
        }

        return $next($request);
    }
}
