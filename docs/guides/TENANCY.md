# Tenancy Guide

How multi-tenancy works in Mahir: database isolation, subdomain resolution, and tenant lifecycle.

---

## Strategy

Mahir uses **separate MySQL databases per tenant** (not shared tables). Each tenant gets its own database named `mahir_tenant_{slug}`.

| Database | Stores | Connection Name |
|----------|--------|-----------------|
| `mahir_landlord` | Tenants, admin users, subscriptions, sessions, cache, jobs | `landlord` (default) |
| `mahir_tenant_{slug}` | Users, personal access tokens, roles, permissions, articles, portfolios, testimonials | `tenant` |

---

## Tenant Resolution Flow

```
Request: acme.mahir.test/api/v1/auth/login
  1. IdentifyTenant middleware extracts "acme" from subdomain
  2. SubdomainTenantFinder queries: Tenant::where('slug', 'acme')->first()
  3. $tenant->makeCurrent() activates Spatie's tenant context
  4. SwitchTenantDatabaseTask sets tenant connection to mahir_tenant_acme
  5. PrefixCacheTask prefixes cache keys with tenant ID
  6. ResetPermissionsTask flushes Spatie Permission in-memory cache
  7. Controller handles request — all tenant-scoped models now query the correct DB
```

### Reserved Subdomains

`admin` and `www` skip tenant resolution.

| URL | Resolves To |
|-----|------------|
| `acme.mahir.test/api/v1/*` | Tenant "acme" |
| `acme.mahir.test/admin` | Tenant Filament panel for "acme" |
| `admin.mahir.test` | Landlord Filament panel (no tenant) |
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
| `Permission` | `UsesTenantConnection` | tenant |
| `Role` | `UsesTenantConnection` | tenant |
| `Article` | `UsesTenantConnection` | tenant |
| `ArticleSeries` | `UsesTenantConnection` | tenant |
| `ArticleComment` | `UsesTenantConnection` | tenant |
| `ArticleRevision` | `UsesTenantConnection` | tenant |
| `Portfolio` | `UsesTenantConnection` | tenant |
| `PortfolioCategory` | `UsesTenantConnection` | tenant |
| `Testimonial` | `UsesTenantConnection` | tenant |
| `TenantAwareMedia` | `UsesTenantConnection` | tenant |

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
| 1 | `database/migrations/tenant/` | Creates users, personal_access_tokens, roles, permissions, articles, portfolios, testimonials, and related tables |
| 2 | `TenantSeeder` | Seeds roles, permissions, and default users |

### Spatie Permission Tenant Connection

Spatie Permission's built-in models default to the application's default DB connection (`landlord`), but permission tables live in **tenant** databases.

**The fix**: Custom `Permission` and `Role` models in `app/Modules/Auth/Models/` extend Spatie's models with the `UsesTenantConnection` trait, forcing all queries to the tenant connection. These custom models are registered in `config/permission.php`:

```php
'models' => [
    'permission' => App\Modules\Auth\Models\Permission::class,
    'role' => App\Modules\Auth\Models\Role::class,
],
```

`ResetPermissionsTask` (registered in `config/multitenancy.php` under `switch_tenant_tasks`) flushes Spatie's in-memory permission cache on every tenant switch, so the correct tenant's permissions are always loaded.

---

## Creating Tenant-Scoped Modules

When building new modules that store tenant data, follow these rules:

### No `tenant_id` Column in Tenant Tables

Because each tenant has its **own isolated database**, tenant-scoped tables do **NOT** need a `tenant_id` column. The database itself provides the isolation. All data in a tenant database belongs exclusively to that tenant.

```
CORRECT (tenant database):
articles: id, user_id, title, slug, content, ...
comments: id, article_id, user_id, content, ...

WRONG (tenant database):
articles: id, tenant_id, user_id, title, ...   ← tenant_id is redundant
```

### When `tenant_id` IS Needed

Only landlord-scoped tables that reference tenants need `tenant_id`. For example, `subscriptions` lives in the landlord database and uses `tenant_id` to link to a specific tenant.

| Table Location | Has `tenant_id`? | Reason |
|----------------|------------------|--------|
| Tenant database (`users`, `articles`) | No | Entire DB is isolated per tenant |
| Landlord database (`subscriptions`) | Yes | Shared DB, needs FK to identify tenant |

### Model Trait Selection

| Data Scope | Trait | Migration Directory |
|------------|-------|---------------------|
| Per-tenant data | `UsesTenantConnection` | `database/migrations/tenant/` |
| Global/shared data | `UsesLandlordConnection` | `database/migrations/landlord/` |

### Module Checklist

1. **Migration**: Place in `database/migrations/tenant/` (no `tenant_id` column)
2. **Model**: Use `UsesTenantConnection` trait
3. **Factory**: Reference the model with `#[UseFactory]` attribute
4. **Service Provider**: Register in `bootstrap/providers.php`
5. **Filament Resource**: Will be auto-discovered via `AdminPanelProvider`
6. **Routes**: Add to `routes/api.php` under appropriate middleware group

---

## Queue Awareness

All queued jobs automatically restore tenant context (`queues_are_tenant_aware_by_default: true`). Use `TenantAware` or `NotTenantAware` interfaces for explicit control.

---

## Testing Tenancy

Tests use SQLite in-memory. `TestCase::setUp()` shares a single PDO across all connections. Feature tests skip subdomain resolution with:

```php
$this->withoutMiddleware(IdentifyTenant::class);
```
