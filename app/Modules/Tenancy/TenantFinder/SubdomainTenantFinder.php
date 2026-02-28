<?php

namespace App\Modules\Tenancy\TenantFinder;

use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

/**
 * Resolves a tenant from the request's subdomain.
 *
 * Expected format: {slug}.mahir.test
 * The first segment before the base domain is treated as the tenant slug.
 * Subdomains like "admin" and "api" (without a tenant prefix) are ignored.
 */
class SubdomainTenantFinder extends TenantFinder
{
    /** @var list<string> */
    protected array $reservedSubdomains = [
        'admin',
        'www',
    ];

    public function findForRequest(Request $request): ?IsTenant
    {
        $subdomain = $this->extractSubdomain($request);

        if ($subdomain === null) {
            return null;
        }

        return app(IsTenant::class)::query()
            ->where('slug', $subdomain)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Determine if the request requires tenant resolution.
     *
     * Returns false for requests to the base domain or reserved subdomains
     * (e.g. "admin.mahir.test", "www.mahir.test", "mahir.test").
     */
    public function requiresTenant(Request $request): bool
    {
        return $this->extractSubdomain($request) !== null;
    }

    /**
     * Extract the tenant slug from the request host.
     *
     * For "api.acme.mahir.test" → "acme"
     * For "acme.mahir.test" → "acme"
     * For "admin.mahir.test" → null (reserved)
     * For "mahir.test" → null (no subdomain)
     */
    protected function extractSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $baseDomain = config('multitenancy.base_domain', 'mahir.test');

        if (! str_ends_with($host, ".{$baseDomain}")) {
            return null;
        }

        $prefix = substr($host, 0, -(strlen($baseDomain) + 1));

        if ($prefix === '') {
            return null;
        }

        // Split on dots — for "api.acme" → ["api", "acme"]
        $segments = explode('.', $prefix);

        // Strip known prefixes like "api"
        $segments = array_filter(
            $segments,
            fn (string $segment): bool => ! in_array($segment, ['api'], true),
        );

        $slug = end($segments);

        if ($slug === false || in_array($slug, $this->reservedSubdomains, true)) {
            return null;
        }

        return $slug;
    }
}
