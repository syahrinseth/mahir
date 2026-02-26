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
5. Runs tenant seeders (roles, permissions, default users)
6. Fires `TenantCreated` event

### Via Admin Panel

Navigate to `admin.mahir.test` > Tenants > Create. Fill in name (slug/domain/database auto-generate).

---

## Migrations & Seeders

Both migration paths are registered in `AppServiceProvider::boot()` via `loadMigrationsFrom()`. Landlord and tenant seeders are **completely separate** — no context detection logic.

> **Note:** Laravel does NOT auto-route migrations by folder name. You must use both `--database` and `--path` flags together when running manually. `CreateTenantAction` handles this automatically for tenants.

### Landlord Database

```bash
# Step 1: Run landlord migrations
php artisan migrate --database=landlord --path=database/migrations/landlord

# Step 2: Seed landlord (admin users for Filament panel)
php artisan db:seed
```

| Step | Directory / Seeder | What it does |
|------|--------------------|--------------|
| 1 | `database/migrations/landlord/` | Creates tenants, admin_users, subscriptions, sessions, cache, jobs tables |
| 2 | `DatabaseSeeder` → `LandlordSeeder` | Seeds admin users for the Filament panel |

### Tenant Database

For **new tenants**, `CreateTenantAction` handles everything automatically (migrations + seeders). To run manually on existing tenants:

```bash
# Step 1: Run tenant migrations (all tenants)
php artisan tenants:artisan "migrate --database=tenant --path=database/migrations/tenant --force"

# Step 2: Seed tenant (roles, permissions, users)
php artisan tenants:artisan "db:seed --class=Database\\Seeders\\Tenant\\TenantSeeder"
```

| Step | Directory / Seeder | What it does |
|------|--------------------|--------------|
| 1 | `database/migrations/tenant/` | Creates users, personal_access_tokens, roles, permissions tables |
| 2 | `TenantSeeder` | Seeds roles, permissions, and default users |

### Spatie Permission Connection Gotcha

Spatie Permission models use the **default** DB connection (`landlord`), but permission tables live in **tenant** databases. `TenantSeeder` works around this by temporarily switching the default connection:

```php
$originalConnection = DB::getDefaultConnection();
DB::setDefaultConnection('tenant');
try {
    // seed roles & permissions
} finally {
    DB::setDefaultConnection($originalConnection);
}
```

---

## Queue Awareness

All queued jobs automatically restore tenant context (`queues_are_tenant_aware_by_default: true`). Use `TenantAware` or `NotTenantAware` interfaces for explicit control.

---

## Testing Tenancy

Tests use SQLite in-memory. `TestCase::setUp()` shares a single PDO across all connections. Feature tests skip subdomain resolution with:

```php
$this->withoutMiddleware(IdentifyTenant::class);
```
