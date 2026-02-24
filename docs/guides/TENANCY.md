# Tenancy Guide

How multi-tenancy works in Mahir: database isolation, subdomain resolution, and tenant lifecycle.

---

## Strategy

Mahir uses **separate MySQL databases per tenant** (not shared tables). Each tenant gets its own database named `mahir_tenant_{slug}`.

| Database | Stores | Connection Name |
|----------|--------|-----------------|
| `mahir_landlord` | Tenants, admin users, subscriptions, sessions, cache, jobs | `landlord` (default) |
| `mahir_tenant_{slug}` | Users, personal access tokens | `tenant` |

---

## Tenant Resolution Flow

```
Request: acme.mahir.test/api/v1/auth/login
  1. IdentifyTenant middleware extracts "acme" from subdomain
  2. SubdomainTenantFinder queries: Tenant::where('slug', 'acme')->first()
  3. $tenant->makeCurrent() activates Spatie's tenant context
  4. SwitchTenantDatabaseTask sets tenant connection to mahir_tenant_acme
  5. PrefixCacheTask prefixes cache keys with tenant ID
  6. Controller handles request — all tenant-scoped models now query the correct DB
```

### Reserved Subdomains

`admin` and `www` skip tenant resolution.

| URL | Resolves To |
|-----|------------|
| `acme.mahir.test/api/v1/*` | Tenant "acme" |
| `admin.mahir.test` | Filament admin panel (no tenant) |
| `mahir.test` | Landing/web (no tenant) |

---

## Model Connection Mapping

Models declare their connection via traits:

| Model | Trait | Database |
|-------|-------|----------|
| `Tenant` | `UsesLandlordConnection` | landlord |
| `AdminUser` | `UsesLandlordConnection` | landlord |
| `Subscription` | `UsesLandlordConnection` | landlord |
| `User` | `UsesTenantConnection` | tenant |
| `PersonalAccessToken` | `UsesTenantConnection` | tenant |

---

## Creating a Tenant

### Via API

```
POST /api/v1/tenants
{
    "name": "Acme Corp",
    "slug": "acme-corp",
    "domain": "acme-corp.mahir.test"
}
```

This triggers `CreateTenantAction` which:
1. Generates DB name: `mahir_tenant_acme_corp`
2. Creates the MySQL database
3. Creates the tenant record in landlord DB
4. Runs tenant migrations on the new database
5. Fires `TenantCreated` event

### Via Admin Panel

Navigate to `admin.mahir.test` > Tenants > Create. Fill in name (slug/domain/database auto-generate).

---

## Migrations

| Directory | Target | Command |
|-----------|--------|---------|
| `database/migrations/landlord/` | Landlord DB | `php artisan migrate --database=landlord` |
| `database/migrations/tenant/` | All tenant DBs | Via `CreateTenantAction` or `tenants:artisan "migrate"` |

Both paths are registered in `AppServiceProvider::boot()`.

---

## Queue Awareness

All queued jobs automatically restore tenant context (`queues_are_tenant_aware_by_default: true`). Use `TenantAware` or `NotTenantAware` interfaces for explicit control.

---

## Testing Tenancy

Tests use SQLite in-memory. `TestCase::setUp()` shares a single PDO across all connections. Feature tests skip subdomain resolution with:

```php
$this->withoutMiddleware(IdentifyTenant::class);
```
