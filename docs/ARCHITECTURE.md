# Mahir Architecture

## Overview

Mahir is a **headless multi-tenant SaaS API** built with Laravel 12. It uses **separate MySQL databases per tenant** (not shared tables), with **subdomain-based tenant detection**. The landlord admin panel is powered by Filament v5.

The codebase follows a **modular architecture** where all related code for a domain lives together inside its module directory under `app/Modules/`.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| PHP | 8.3+ |
| Database | MySQL (separate DB per tenant) |
| Multitenancy | Spatie Laravel Multitenancy v4 |
| Admin Panel | Filament v5 (landlord + tenant panels) |
| Auth (API) | Laravel Sanctum v4 (token-based) |
| Frontend | Livewire v4, Vite |
| Testing | Pest v4 |
| Code Style | Laravel Pint |
| Media | Spatie Media Library v11 (tenant-aware) |

## Directory Structure

```
app/
├── Http/
│   └── Middleware/
│       └── IdentifyTenant.php       # Resolves tenant from subdomain
├── Modules/                          # All domain logic lives here
│   ├── Article/                      # Article/blog management
│   ├── Auth/                         # Authentication & user management
│   ├── Portfolio/                    # Portfolio project showcase
│   ├── Subscription/                 # Subscription & billing management
│   └── Tenancy/                      # Tenant lifecycle & database management
├── Providers/
│   ├── AppServiceProvider.php        # Registers migration paths
│   └── Filament/
│       ├── LandlordPanelProvider.php  # Filament landlord panel (admin.mahir.test)
│       └── TenantPanelProvider.php    # Filament tenant panel ({slug}.mahir.test/admin)
├── Support/
│   └── MediaLibrary/                 # Spatie Media Library extensions
│       ├── MultiTenantPathGenerator.php  # Prefixes paths with tenants/{id}/
│       └── TenantAwareMedia.php          # Media model on tenant connection
└── Shared/                           # Cross-module contracts, traits, exceptions
    ├── Contracts/
    ├── Exceptions/
    └── Traits/

bootstrap/
├── app.php                           # Middleware, routing, exceptions
└── providers.php                     # Service provider registration

config/
├── auth.php                          # Guards: web, admin, tenant, api (sanctum)
├── database.php                      # Connections: landlord, tenant, sqlite
├── media-library.php                 # Spatie: TenantAwareMedia model, MultiTenantPathGenerator
├── multitenancy.php                  # Spatie config, tenant finder, switch tasks
└── permission.php                    # Spatie Permission: custom tenant-scoped models

database/
├── factories/                        # Eloquent model factories
├── migrations/
│   ├── landlord/                     # Landlord DB tables (tenants, admin_users, subscriptions, etc.)
│   └── tenant/                       # Tenant DB tables (users, personal_access_tokens, roles, permissions, articles, portfolios, etc.)
└── seeders/
    ├── DatabaseSeeder.php            # Entry point — calls LandlordSeeder only
    ├── landlord/
    │   └── LandlordSeeder.php        # Seeds admin users for Filament panel
    └── tenant/
        └── TenantSeeder.php          # Seeds roles, permissions, users for a tenant

routes/
├── api.php                           # Versioned API routes (v1) — auth, tenants, subscriptions
├── web.php                           # Web routes (minimal)
└── console.php                       # Artisan console commands

tests/
├── Pest.php                          # Pest config — extends TestCase for Feature + Unit
├── TestCase.php                      # Base test case — overrides DB connections for testing
├── Feature/
│   ├── Auth/                         # HTTP tests: login, register, logout, user
│   ├── Subscription/                 # HTTP tests: subscription CRUD
│   └── Tenancy/                      # HTTP tests: tenant CRUD
└── Unit/
    └── Auth/                         # Unit tests: AuthService, LoginAction, RegisterUserAction
```

## Module Structure

Each module in `app/Modules/{ModuleName}/` is self-contained with this structure:

```
{ModuleName}/
├── Actions/           # Single-responsibility operations (e.g., CreateTenantAction)
├── DTOs/              # Data Transfer Objects for type-safe data passing
├── Enums/             # PHP backed enums for statuses and types
├── Events/            # Domain events
├── Filament/          # Filament admin panel resources (Resource, Pages, Schemas, Tables)
│   └── Resources/
├── Http/
│   ├── Controllers/   # API controllers
│   └── Requests/      # Form Request validation classes
├── Listeners/         # Event listeners
├── Models/            # Eloquent models
├── Providers/         # Module service provider (registered in bootstrap/providers.php)
├── Repositories/      # Data access abstraction
└── Services/          # Business logic (multi-step orchestration)
```

Not every module has all directories — only the ones that are needed. For example, `Enums/` only exists in the Subscription module.

### Module Overview

| Module | Purpose | Models | DB Connection |
|--------|---------|--------|---------------|
| **Article** | Article/blog content management | `Article`, `ArticleSeries`, `ArticleComment`, `ArticleRevision` | tenant |
| **Auth** | User authentication, registration, admin users | `User`, `AdminUser`, `PersonalAccessToken`, `Permission`, `Role` | `User`/`Permission`/`Role` on tenant, `AdminUser` on landlord |
| **Portfolio** | Project showcase with media galleries | `Portfolio`, `PortfolioCategory`, `Testimonial` | tenant (media via Spatie `TenantAwareMedia`) |
| **Subscription** | Subscription & plan management | `Subscription` | landlord |
| **Tenancy** | Tenant lifecycle, database creation, subdomain resolution | `Tenant` | landlord |

### Architecture Rules

1. **All domain code lives inside its module** — controllers, requests, Filament resources, DTOs, enums, models, services, actions, and repositories all reside under `app/Modules/{ModuleName}/`
2. **Controllers are thin** — they validate input (via FormRequests), delegate to Services/Actions, and return JSON responses
3. **Services** orchestrate business logic involving multiple steps or dependencies
4. **Actions** are single-responsibility classes with one public `execute()` method
5. **Repositories** abstract database queries away from services
6. **DTOs** are used to pass data between layers (controllers -> services -> actions) instead of raw arrays
7. **Enums** replace magic strings for statuses and types (e.g., `SubscriptionStatus`, `PlanType`)
8. **Models** define relationships, attribute casts, and connection traits — no business logic

## Data Transfer Objects (DTOs)

DTOs provide type-safe data passing between layers. Each DTO uses constructor property promotion with `readonly` properties and includes a `fromArray()` static factory method.

| DTO | Module | Purpose |
|-----|--------|---------|
| `LoginCredentialsDTO` | Auth | Carries email, password, deviceName to AuthService |
| `RegisterUserDTO` | Auth | Carries name, email, password, deviceName to AuthService |
| `AuthResponseDTO` | Auth | Returned by login/register — wraps User + token string |
| `CreateSubscriptionDTO` | Subscription | Carries tenant_id, plan (PlanType), status, dates |
| `UpdateSubscriptionDTO` | Subscription | Carries optional plan, status, dates for partial updates |
| `CreateTenantDTO` | Tenancy | Carries name, slug, domain for tenant creation |
| `UpdateTenantDTO` | Tenancy | Carries optional name, slug, domain, is_active |

**Pattern:**
```php
class RegisterUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $deviceName = 'api',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            deviceName: $data['device_name'] ?? 'api',
        );
    }
}
```

## Enums

Backed string enums with helper methods (`label()`, `color()`). Used as Eloquent casts on models.

| Enum | Module | Values |
|------|--------|--------|
| `SubscriptionStatus` | Subscription | `active`, `trial`, `cancelled`, `expired` |
| `PlanType` | Subscription | `basic`, `pro`, `enterprise` |

**Note:** User and Tenant active status use a boolean `is_active` column, not enums.

## Shared Layer

`app/Shared/` contains cross-module contracts, traits, and exceptions:

### Contracts (Marker Interfaces)

| Contract | Purpose |
|----------|---------|
| `ActionContract` | Marker interface for action classes (no enforced method signature) |
| `RepositoryContract` | Defines `all()`, `findById()`, `create()`, `update()`, `delete()` |
| `ServiceContract` | Marker interface for service classes |

### Traits

| Trait | Purpose |
|-------|---------|
| `UsesLandlordConnection` | Returns `config('multitenancy.landlord_database_connection_name')` as the model connection |
| `UsesTenantConnection` | Returns `config('multitenancy.tenant_database_connection_name')` as the model connection |

### Exceptions

| Exception | Purpose |
|-----------|---------|
| `TenantDatabaseException` | Thrown when tenant database operations fail |
| `TenantNotFoundException` | Thrown when a tenant cannot be resolved |

## Multi-Tenancy Architecture

### Database Strategy

- **Landlord DB**: `mahir_landlord` — stores tenants, admin_users, subscriptions, sessions, cache, jobs
- **Tenant DBs**: `mahir_tenant_{slug}` — one per tenant, stores users, personal_access_tokens

### Connection Configuration (`config/database.php`)

| Connection | Purpose | Database |
|-----------|---------|----------|
| `landlord` | Central/admin data (default connection) | `mahir_landlord` |
| `tenant` | Tenant-scoped data (switched at runtime by Spatie) | `mahir_tenant_{slug}` |
| `sqlite` | Used in tests only | `:memory:` |

The default connection is `landlord`. The `tenant` connection has `database: null` — Spatie's `SwitchTenantDatabaseTask` sets it at runtime when a tenant becomes current.

### Model-to-Connection Mapping

| Model | Connection Trait | Database |
|-------|-----------------|----------|
| `Tenant` | `UsesLandlordConnection` | `mahir_landlord` |
| `AdminUser` | `UsesLandlordConnection` | `mahir_landlord` |
| `Subscription` | `UsesLandlordConnection` | `mahir_landlord` |
| `User` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `PersonalAccessToken` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `Permission` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `Role` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `Article` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `ArticleSeries` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `ArticleComment` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `ArticleRevision` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `Portfolio` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `PortfolioCategory` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `Testimonial` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `TenantAwareMedia` | `UsesTenantConnection` | `mahir_tenant_{slug}` |

### Tenant Resolution Flow

```
Request to {slug}.mahir.test/api/v1/*
  -> IdentifyTenant middleware (prepended to api group)
  -> SubdomainTenantFinder extracts slug from subdomain
  -> Tenant::where('slug', $slug)->first()
  -> $tenant->makeCurrent()
  -> SwitchTenantDatabaseTask sets tenant connection DB to mahir_tenant_{slug}
  -> PrefixCacheTask prefixes cache keys with tenant ID
  -> ResetPermissionsTask flushes Spatie Permission in-memory cache
  -> Controller handles request with tenant context active
```

### Subdomain Pattern

| URL | Resolves To |
|-----|------------|
| `acme.mahir.test/api/v1/*` | Tenant with slug `acme` |
| `widgets.mahir.test/api/v1/*` | Tenant with slug `widgets` |
| `acme.mahir.test/admin` | Tenant Filament panel for tenant `acme` |
| `admin.mahir.test` | Landlord Filament panel (no tenant) |
| `mahir.test` | Landing/web (no tenant) |

Reserved subdomains that skip tenant resolution: `admin`, `www`

### Migrations & Seeders

Both migration paths are registered in `AppServiceProvider::boot()` via `loadMigrationsFrom()`. Landlord and tenant seeders are completely separate — `DatabaseSeeder` only calls `LandlordSeeder`.

> **Note:** Laravel does NOT auto-route migrations by folder name. When running manually, use both `--database` and `--path` flags.

**Landlord:**

```bash
# Step 1: Migrate
php artisan migrate --database=landlord --path=database/migrations/landlord

# Step 2: Seed (admin users)
php artisan db:seed
```

**Tenant** (for existing tenants — new tenants are fully provisioned by `CreateTenantAction`):

```bash
# Step 1: Migrate
php artisan tenants:artisan "migrate --database=tenant --path=database/migrations/tenant --force"

# Step 2: Seed (roles, permissions, users)
php artisan tenants:artisan "db:seed --class=Database\\Seeders\\Tenant\\TenantSeeder"
```

## Authentication

### API (Tenant Users)

- **Guard**: `sanctum` (via `auth:sanctum` middleware)
- **Model**: `App\Modules\Auth\Models\User` (tenant connection)
- **Token type**: Sanctum personal access tokens
- **Endpoints**:

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/auth/register` | No | Register new tenant user |
| POST | `/api/v1/auth/login` | No | Login, receive bearer token |
| POST | `/api/v1/auth/logout` | Yes | Revoke current token |
| GET | `/api/v1/auth/user` | Yes | Get authenticated user |

### Admin Panel (Landlord)

- **Guard**: `admin` (session driver, `admin_users` provider)
- **Model**: `App\Modules\Auth\Models\AdminUser` (landlord connection, implements `FilamentUser`)
- **URL**: `admin.mahir.test` (Filament v5)

## API Routes

All API routes are prefixed with `/api/v1` (configured in `bootstrap/app.php` via `apiPrefix`).

### Auth Routes

| Method | Endpoint | Name | Auth |
|--------|----------|------|------|
| POST | `/api/v1/auth/register` | `api.auth.register` | No |
| POST | `/api/v1/auth/login` | `api.auth.login` | No |
| POST | `/api/v1/auth/logout` | `api.auth.logout` | `auth:sanctum` |
| GET | `/api/v1/auth/user` | `api.auth.user` | `auth:sanctum` |

### Tenant CRUD Routes

| Method | Endpoint | Name | Auth |
|--------|----------|------|------|
| GET | `/api/v1/tenants` | `api.tenants.index` | `auth:sanctum` |
| POST | `/api/v1/tenants` | `api.tenants.store` | `auth:sanctum` |
| GET | `/api/v1/tenants/{tenant}` | `api.tenants.show` | `auth:sanctum` |
| PUT | `/api/v1/tenants/{tenant}` | `api.tenants.update` | `auth:sanctum` |
| DELETE | `/api/v1/tenants/{tenant}` | `api.tenants.destroy` | `auth:sanctum` |

### Subscription CRUD Routes

| Method | Endpoint | Name | Auth |
|--------|----------|------|------|
| GET | `/api/v1/subscriptions` | `api.subscriptions.index` | `auth:sanctum` |
| POST | `/api/v1/subscriptions` | `api.subscriptions.store` | `auth:sanctum` |
| GET | `/api/v1/subscriptions/{subscription}` | `api.subscriptions.show` | `auth:sanctum` |
| PUT | `/api/v1/subscriptions/{subscription}` | `api.subscriptions.update` | `auth:sanctum` |
| DELETE | `/api/v1/subscriptions/{subscription}` | `api.subscriptions.destroy` | `auth:sanctum` |

### Utility Routes

| Method | Endpoint | Name | Auth |
|--------|----------|------|------|
| GET | `/api/v1/ping` | `api.ping` | No |

## Filament Admin Panels

Mahir has **two Filament panels**:

### Landlord Panel (`admin.mahir.test`)

Uses the `admin` auth guard and `AdminUser` model (landlord DB). Configured in `LandlordPanelProvider`.

| Resource | Module Path | Manages |
|----------|------------|---------|
| `AdminUserResource` | `Modules/Auth/Filament/Resources/AdminUsers/` | Admin user accounts |
| `SubscriptionResource` | `Modules/Subscription/Filament/Resources/Subscriptions/` | Tenant subscriptions |
| `TenantResource` | `Modules/Tenancy/Filament/Resources/Tenants/` | Tenant records |

### Tenant Panel (`{slug}.mahir.test/admin`)

Uses the `tenant` auth guard and `User` model (tenant DB). Configured in `TenantPanelProvider`. The `EnsureTenantPanel` middleware resolves and activates the tenant from the subdomain for Filament requests.

| Resource | Module Path | Manages |
|----------|------------|---------|
| `ArticleResource` | `Modules/Article/Filament/Resources/Articles/` | Blog articles |
| `ArticleSeriesResource` | `Modules/Article/Filament/Resources/ArticleSeries/` | Article series |
| `PortfolioCategoryResource` | `Modules/Portfolio/Filament/Resources/PortfolioCategories/` | Portfolio categories |
| `PortfolioResource` | `Modules/Portfolio/Filament/Resources/Portfolios/` | Portfolio projects |
| `TestimonialResource` | `Modules/Portfolio/Filament/Resources/Testimonials/` | Testimonials |

### Resource Structure (both panels)
```
{ResourceName}/
├── {ResourceName}Resource.php    # Resource class with navigation, relations
├── Pages/
│   ├── Create{Name}.php
│   ├── Edit{Name}.php
│   └── List{Names}.php
├── Schemas/
│   └── {Name}Form.php            # Form schema (shared between create/edit)
└── Tables/
    └── {Names}Table.php          # Table schema (columns, filters, actions)
```

Resource discovery is configured in `LandlordPanelProvider` (for landlord resources) and `TenantPanelProvider` (for tenant resources) with `discoverResources()` calls per module.

## Factories

All factories live in `database/factories/` and are linked to module models via `#[UseFactory]` attributes:

| Factory | Model | Notable States |
|---------|-------|---------------|
| `UserFactory` | `Auth\Models\User` | `inactive()` |
| `AdminUserFactory` | `Auth\Models\AdminUser` | — |
| `PersonalAccessTokenFactory` | `Auth\Models\PersonalAccessToken` | — |
| `TenantFactory` | `Tenancy\Models\Tenant` | `active()`, `inactive()` |
| `SubscriptionFactory` | `Subscription\Models\Subscription` | `onTrial()`, `cancelled()`, `expired()`, `withPlan()` |
| `PortfolioFactory` | `Portfolio\Models\Portfolio` | `draft()`, `published()` |
| `PortfolioCategoryFactory` | `Portfolio\Models\PortfolioCategory` | — |

Models in non-standard namespaces (under `App\Modules\*`) use the `#[UseFactory(FactoryClass::class)]` attribute for Laravel to resolve the correct factory.

## Testing

### Setup

- **Framework**: Pest v4
- **Config**: `tests/Pest.php` extends `Tests\TestCase` with `RefreshDatabase` for both `Feature` and `Unit` test directories
- **Database**: All tests use SQLite in-memory. `TestCase::setUp()` overrides the `tenant` and `landlord` connections to share the same SQLite PDO instance, so all models write to a single test database regardless of their connection trait
- **Tenant middleware**: Feature tests that call API endpoints use `$this->withoutMiddleware(IdentifyTenant::class)` to skip subdomain resolution

### Test Structure

```
tests/
├── Feature/Auth/
│   ├── LoginTest.php              # 13 tests — login validation, credentials, tokens
│   ├── RegisterTest.php           # Tests — registration, validation, uniqueness
│   ├── LogoutTest.php             # 3 tests — logout, token revocation, unauth
│   └── UserTest.php               # Tests — authenticated user endpoint
├── Feature/Article/
│   └── Filament/
│       ├── ArticleFilamentTest.php        # Filament CRUD for articles
│       └── ArticleSeriesFilamentTest.php  # Filament CRUD for article series
├── Feature/Portfolio/
│   └── Filament/
│       ├── PortfolioCategoryFilamentTest.php
│       ├── PortfolioFilamentTest.php
│       └── TestimonialFilamentTest.php
├── Feature/Subscription/
│   └── SubscriptionCrudTest.php   # 15 tests — full CRUD + validation + auth
├── Feature/Tenancy/
│   ├── TenantCrudTest.php         # 15 tests — full CRUD + validation + auth
│   ├── SpatiePermissionConnectionTest.php # 9 tests — permission connection isolation
│   └── Filament/
│       ├── AdminUserFilamentTest.php
│       └── SubscriptionFilamentTest.php
└── Unit/Auth/
    ├── AuthServiceTest.php        # 9 tests — registerUser, attemptLogin, logout
    ├── LoginActionTest.php        # 5 tests — execute with valid/invalid/inactive
    └── RegisterUserActionTest.php # 4 tests — execute, persistence, tokens
```

### Running Tests

```bash
# Run all tests (use explicit memory limit — default 128M is insufficient)
php -d memory_limit=256M vendor/bin/pest --compact

# Run a specific test file
php -d memory_limit=256M vendor/bin/pest --compact --filter=LoginTest

# Run only unit tests
php -d memory_limit=256M vendor/bin/pest --compact --testsuite=Unit
```

## Queue Awareness

- `queues_are_tenant_aware_by_default` is `true` — all queued jobs automatically restore tenant context
- Jobs can implement `TenantAware` or `NotTenantAware` interfaces for explicit control
- Cache keys are automatically prefixed per tenant via `PrefixCacheTask`

## Media Library (Spatie)

Portfolio (and any future models) use **Spatie Media Library v11** for polymorphic file management. Media records are stored in each tenant's own database for isolation.

### Key Configuration (`config/media-library.php`)

| Setting | Value |
|---------|-------|
| `media_model` | `App\Support\MediaLibrary\TenantAwareMedia` |
| `path_generator` | `App\Support\MediaLibrary\MultiTenantPathGenerator` |
| `disk_name` | `public` |

### Multi-Tenant Integration

- **`TenantAwareMedia`** (`app/Support/MediaLibrary/TenantAwareMedia.php`) — extends Spatie's `Media` model with the `UsesTenantConnection` trait so media records are stored in the tenant database
- **`MultiTenantPathGenerator`** (`app/Support/MediaLibrary/MultiTenantPathGenerator.php`) — prefixes all file storage paths with `tenants/{tenant_id}/{media_id}/` for file-level isolation on a shared disk
- No `tenant_id` column on the media table — isolation is achieved at the database level (separate DB per tenant)

### Portfolio Media Collections

The `Portfolio` model implements `HasMedia` and defines two named collections:

| Collection | Type | Accepted MIME Types |
|-----------|------|-------------------|
| `gallery` | Multiple files | jpg, png, webp, gif, svg, pdf |
| `featured` | Single file (replaces previous) | jpg, png, webp |

### Image Conversions

Three conversions are registered on the Portfolio model (all non-queued):

| Conversion | Dimensions | Purpose |
|-----------|-----------|---------|
| `thumb` | 300x300 | Thumbnail for grid views |
| `medium` | 600x400 | Medium preview |
| `display` | 1200x600 | Full display image |

### Custom Properties

Caption and sort order are stored in Spatie's `custom_properties` JSON column:
- `caption` (string|null) — image caption/description
- `sort_order` (int|null) — display order within the collection

### Adding Media to Other Models

To add Spatie media to a new model:

1. Implement `Spatie\MediaLibrary\HasMedia` interface
2. Use `Spatie\MediaLibrary\InteractsWithMedia` trait
3. Define `registerMediaCollections()` for named collections
4. Define `registerMediaConversions()` for image variants
5. The `TenantAwareMedia` model and `MultiTenantPathGenerator` apply automatically via config

## Bootstrap Configuration

### `bootstrap/app.php`

- Routes: web, api (prefix `api/v1`), console, health check at `/up`
- Middleware: `IdentifyTenant` is prepended to the `api` middleware group

### `bootstrap/providers.php`

Registered providers (order matters):
1. `AppServiceProvider` — registers migration paths
2. `LandlordPanelProvider` — Filament landlord panel configuration
3. `TenantPanelProvider` — Filament tenant panel configuration
4. `TenancyServiceProvider` — tenancy bindings
5. `AuthServiceProvider` — auth module bindings
6. `SubscriptionServiceProvider` — subscription module bindings
7. `ArticleServiceProvider` — article module bindings
8. `PortfolioServiceProvider` — portfolio module bindings

## Adding a New Module

1. Create directory structure under `app/Modules/{ModuleName}/` following the module pattern above
2. Create a `{ModuleName}ServiceProvider` and register it in `bootstrap/providers.php`
3. Models: use `UsesLandlordConnection` or `UsesTenantConnection` trait, add `#[UseFactory]` attribute
4. Factories: place in `database/factories/` with `protected $model` set
5. Migrations: place in `database/migrations/landlord/` or `database/migrations/tenant/`
6. DTOs: create in `{ModuleName}/DTOs/` with `readonly` constructor properties and `fromArray()`
7. Enums: create in `{ModuleName}/Enums/` as backed string enums with `label()` method
8. Filament: create resources in `{ModuleName}/Filament/Resources/` and add `discoverResources()` to `LandlordPanelProvider` (landlord resources) or `TenantPanelProvider` (tenant resources)
9. Tests: create feature tests in `tests/Feature/{ModuleName}/`, unit tests in `tests/Unit/{ModuleName}/`
10. Follow existing patterns from `Tenancy`, `Auth`, or `Subscription` modules

## Adding a New Tenant-Scoped Feature

1. Create model with `UsesTenantConnection` trait and `#[UseFactory]` attribute
2. Create migration in `database/migrations/tenant/`
3. Create factory in `database/factories/`
4. Create DTO(s) in the module's `DTOs/` directory
5. Create repository, service, and/or action classes as needed
6. Create API controller in the module's `Http/Controllers/` directory
7. Create FormRequest(s) in the module's `Http/Requests/` directory
8. Add routes in `routes/api.php`
9. Routes automatically inherit `IdentifyTenant` middleware from the `api` group

## Key Configuration Files

| File | Purpose |
|------|---------|
| `config/multitenancy.php` | Tenant finder, switch tasks, queue awareness, connection names |
| `config/database.php` | Landlord + tenant MySQL connections, SQLite for tests |
| `config/auth.php` | Guards (web, admin, tenant, sanctum) and user providers |
| `config/permission.php` | Spatie Permission: custom Permission and Role models (tenant-scoped) |
| `config/media-library.php` | Spatie Media Library: model, path generator, disk settings |
| `bootstrap/app.php` | Middleware pipeline, routing config, API prefix |
| `bootstrap/providers.php` | All service provider registrations |
| `phpunit.xml` | Test environment — SQLite in-memory, env overrides |
