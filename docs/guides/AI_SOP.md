# AI Standard Operating Procedure (SOP) for Mahir

This document is the **authoritative reference** for any AI agent working on the Mahir codebase. Read this before making any changes. Follow every rule. No exceptions.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Architecture Rules](#2-architecture-rules)
3. [Module Structure](#3-module-structure)
4. [Code Patterns & Conventions](#4-code-patterns--conventions)
5. [Adding a New Module](#5-adding-a-new-module)
6. [Adding a Feature to an Existing Module](#6-adding-a-feature-to-an-existing-module)
7. [Database & Multi-Tenancy](#7-database--multi-tenancy)
8. [API & Routing](#8-api--routing)
9. [Filament Admin Panel](#9-filament-admin-panel)
10. [Testing](#10-testing)
11. [Pre-Commit Checklist](#11-pre-commit-checklist)
12. [Common Pitfalls](#12-common-pitfalls)
13. [Reference: File Patterns](#13-reference-file-patterns)

---

## 1. Project Overview

**Mahir** is a headless multi-tenant SaaS API built with:

| Layer | Technology | Version |
|-------|-----------|---------|
| Framework | Laravel | 12 |
| PHP | PHP | 8.3+ |
| Database | MySQL | Separate DB per tenant |
| Multitenancy | Spatie Laravel Multitenancy | v4 |
| Admin Panel | Filament | v5 |
| Auth (API) | Laravel Sanctum | v4 |
| Frontend | Livewire | v4 |
| Testing | Pest | v4 |
| Code Style | Laravel Pint | latest |

**Architecture**: Modular. All domain code lives under `app/Modules/{ModuleName}/`. There are currently 3 modules: **Auth**, **Subscription**, **Tenancy**.

**Tenancy model**: Separate MySQL databases per tenant, resolved via subdomain. Landlord data (tenants, admin_users, subscriptions) lives in `mahir_landlord`. Tenant data (users, tokens) lives in `mahir_tenant_{slug}`.

---

## 2. Architecture Rules

These are non-negotiable. Violating them creates technical debt.

### DO

1. **All domain code lives inside its module** — controllers, requests, DTOs, enums, models, services, actions, repositories, Filament resources, events, and listeners all reside under `app/Modules/{ModuleName}/`.
2. **Use DTOs** for passing data between layers (controller -> service -> action). Never pass raw arrays between layers.
3. **Use Enums** for any string-based status or type field. Never use magic strings.
4. **Use FormRequest classes** for all validation — never inline validation in controllers.
5. **Use constructor property promotion** (`public function __construct(private Service $service) {}`) everywhere.
6. **Use explicit return types** on all methods and functions.
7. **Use PHPDoc blocks** with array shape annotations on DTOs and anywhere array structures are used.
8. **Controllers are thin** — validate input (via FormRequest), delegate to Service/Action, return JSON response. No business logic.
9. **Services** orchestrate multi-step business logic and coordinate between repositories, actions, and external integrations.
10. **Actions** are single-responsibility classes with one public `execute()` method.
11. **Repositories** abstract all Eloquent queries away from services.
12. **Models** define relationships, attribute casts, and connection traits only — no business logic.
13. **Use `Model::query()`** instead of `DB::` facade. Never use raw queries unless absolutely necessary.
14. **Use eager loading** (`with()`) to prevent N+1 query problems.
15. **Use `config()` helper** for environment values. Never use `env()` outside `config/*.php` files.
16. **Use named routes** and the `route()` function for URL generation.

### DO NOT

1. **DO NOT** create files outside the module directory structure. No controllers in `app/Http/Controllers/`, no requests in `app/Http/Requests/`, no models in `app/Models/`.
2. **DO NOT** create new base folders without explicit approval.
3. **DO NOT** change dependencies (composer require/remove) without explicit approval.
4. **DO NOT** modify `bootstrap/app.php` or `bootstrap/providers.php` without understanding the impact on middleware ordering and tenant resolution.
5. **DO NOT** create documentation files unless explicitly requested.
6. **DO NOT** use `DB::` facade. Use `Model::query()`.
7. **DO NOT** pass raw arrays between architectural layers. Use DTOs.
8. **DO NOT** put business logic in controllers or models.
9. **DO NOT** use inline validation in controllers. Always create FormRequest classes.

---

## 3. Module Structure

Every module follows this directory structure. Only create directories that are needed.

```
app/Modules/{ModuleName}/
├── Actions/           # Single-responsibility operations
│   └── {Verb}{Noun}Action.php
├── DTOs/              # Data Transfer Objects
│   ├── Create{Noun}DTO.php
│   └── Update{Noun}DTO.php
├── Enums/             # PHP backed string enums
│   └── {Noun}Status.php
├── Events/            # Domain events
│   └── {Noun}Created.php
├── Filament/          # Admin panel resources
│   └── Resources/
│       └── {PluralNoun}/
│           ├── {Noun}Resource.php
│           ├── Pages/
│           │   ├── Create{Noun}.php
│           │   ├── Edit{Noun}.php
│           │   └── List{PluralNoun}.php
│           ├── Schemas/
│           │   └── {Noun}Form.php
│           └── Tables/
│               └── {PluralNoun}Table.php
├── Http/
│   ├── Controllers/
│   │   └── {Noun}Controller.php
│   └── Requests/
│       ├── Create{Noun}Request.php
│       └── Update{Noun}Request.php
├── Listeners/         # Event listeners
├── Models/
│   └── {Noun}.php
├── Providers/
│   └── {ModuleName}ServiceProvider.php
├── Repositories/
│   └── {Noun}Repository.php
└── Services/
    └── {Noun}Service.php
```

### Current Modules

| Module | Purpose | Models | DB Connection |
|--------|---------|--------|---------------|
| **Auth** | User authentication, registration, admin users | `User` (tenant), `AdminUser` (landlord), `PersonalAccessToken` (tenant) | Mixed |
| **Subscription** | Subscription & plan management | `Subscription` (landlord) | landlord |
| **Tenancy** | Tenant lifecycle, DB provisioning, subdomain resolution | `Tenant` (landlord) | landlord |

---

## 4. Code Patterns & Conventions

Before writing any code, **check sibling files** in the same directory to match the existing style exactly. The patterns below are the canonical forms.

### 4.1 DTOs

DTOs pass typed data between layers. All properties are `readonly` with constructor property promotion. Every DTO has a `fromArray()` static factory method. DTOs that are sent outward (responses, writes) also have `toArray()`.

```php
<?php

namespace App\Modules\{Module}\DTOs;

class Create{Noun}DTO
{
    public function __construct(
        public readonly int $tenantId,
        public readonly string $name,
        public readonly ?string $optionalField = null,
    ) {}

    /**
     * @param  array{tenant_id: int, name: string, optional_field?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tenantId: $data['tenant_id'],
            name: $data['name'],
            optionalField: $data['optional_field'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'name' => $this->name,
            'optional_field' => $this->optionalField,
        ];
    }
}
```

**Update DTOs** use nullable properties and `toArray()` filters out nulls:

```php
public function toArray(): array
{
    return array_filter([
        'name' => $this->name,
        'status' => $this->status?->value,
    ], fn (mixed $value): bool => $value !== null);
}
```

### 4.2 Enums

Backed string enums with TitleCase keys. Always include a `label()` method. Include `color()` if the enum is displayed in the UI.

```php
<?php

namespace App\Modules\{Module}\Enums;

enum {Noun}Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'danger',
        };
    }
}
```

### 4.3 Models

Models use connection traits, `#[UseFactory]` attribute, `HasFactory`, and define `casts()` as a method (not `$casts` property). Enum fields are cast to their enum class.

```php
<?php

namespace App\Modules\{Module}\Models;

use App\Shared\Traits\UsesLandlordConnection; // or UsesTenantConnection
use Database\Factories\{Noun}Factory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
#[UseFactory({Noun}Factory::class)]
class {Noun} extends Model
{
    use HasFactory, UsesLandlordConnection;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => {Noun}Status::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

**Key rules:**
- Landlord models use `UsesLandlordConnection` trait
- Tenant models use `UsesTenantConnection` trait
- Module-namespaced models MUST have `#[UseFactory(FactoryClass::class)]` attribute
- Define `$fillable`, not `$guarded`
- Use `casts()` method, not `$casts` property

### 4.4 Repositories

Repositories implement `RepositoryContract` and abstract all Eloquent queries.

```php
<?php

namespace App\Modules\{Module}\Repositories;

use App\Modules\{Module}\Models\{Noun};
use App\Shared\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class {Noun}Repository implements RepositoryContract
{
    /**
     * @return Collection<int, {Noun}>
     */
    public function all(): Collection
    {
        return {Noun}::query()->latest()->get();
    }

    public function findById(int $id): ?{Noun}
    {
        return {Noun}::query()->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): {Noun}
    {
        return {Noun}::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): ?Model
    {
        $record = $this->findById($id);

        if (! $record) {
            return null;
        }

        $record->update($data);

        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        $record = $this->findById($id);

        if (! $record) {
            return false;
        }

        return (bool) $record->delete();
    }
}
```

### 4.5 Services

Services implement `ServiceContract`, receive repositories via constructor injection, and contain business logic.

```php
<?php

namespace App\Modules\{Module}\Services;

use App\Modules\{Module}\DTOs\Create{Noun}DTO;
use App\Modules\{Module}\DTOs\Update{Noun}DTO;
use App\Modules\{Module}\Models\{Noun};
use App\Modules\{Module}\Repositories\{Noun}Repository;
use App\Shared\Contracts\ServiceContract;

class {Noun}Service implements ServiceContract
{
    public function __construct(
        private {Noun}Repository $repository,
    ) {}

    public function create{Noun}(Create{Noun}DTO $dto): {Noun}
    {
        return $this->repository->create($dto->toArray());
    }

    public function update{Noun}(int $id, Update{Noun}DTO $dto): ?{Noun}
    {
        $result = $this->repository->update($id, $dto->toArray());

        return $result instanceof {Noun} ? $result : null;
    }
}
```

### 4.6 Actions

Actions implement `ActionContract`, are single-responsibility, and have one public `execute()` method. They receive raw arrays (from controllers) and internally convert to DTOs.

```php
<?php

namespace App\Modules\{Module}\Actions;

use App\Modules\{Module}\DTOs\Create{Noun}DTO;
use App\Modules\{Module}\Models\{Noun};
use App\Modules\{Module}\Services\{Noun}Service;
use App\Shared\Contracts\ActionContract;

class Create{Noun}Action implements ActionContract
{
    public function __construct(
        private {Noun}Service $service,
    ) {}

    /**
     * @param  array{name: string, slug: string}  $data
     */
    public function execute(array $data): {Noun}
    {
        $dto = Create{Noun}DTO::fromArray($data);

        return $this->service->create{Noun}($dto);
    }
}
```

### 4.7 Controllers

Controllers extend `App\Http\Controllers\Controller`, are thin, and delegate all logic.

```php
<?php

namespace App\Modules\{Module}\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\{Module}\DTOs\Create{Noun}DTO;
use App\Modules\{Module}\DTOs\Update{Noun}DTO;
use App\Modules\{Module}\Http\Requests\Create{Noun}Request;
use App\Modules\{Module}\Http\Requests\Update{Noun}Request;
use App\Modules\{Module}\Repositories\{Noun}Repository;
use App\Modules\{Module}\Services\{Noun}Service;
use Illuminate\Http\JsonResponse;

class {Noun}Controller extends Controller
{
    public function __construct(
        private {Noun}Service $service,
        private {Noun}Repository $repository,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->repository->all(),
        ]);
    }

    public function store(Create{Noun}Request $request): JsonResponse
    {
        $dto = Create{Noun}DTO::fromArray($request->validated());
        $record = $this->service->create{Noun}($dto);

        return response()->json([
            'message' => '{Noun} created successfully.',
            'data' => $record,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->repository->findById($id);

        if (! $record) {
            return response()->json([
                'message' => '{Noun} not found.',
            ], 404);
        }

        return response()->json([
            'data' => $record,
        ]);
    }

    public function update(Update{Noun}Request $request, int $id): JsonResponse
    {
        $dto = Update{Noun}DTO::fromArray($request->validated());
        $record = $this->service->update{Noun}($id, $dto);

        if (! $record) {
            return response()->json([
                'message' => '{Noun} not found.',
            ], 404);
        }

        return response()->json([
            'message' => '{Noun} updated successfully.',
            'data' => $record,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->repository->delete($id);

        if (! $deleted) {
            return response()->json([
                'message' => '{Noun} not found.',
            ], 404);
        }

        return response()->json([
            'message' => '{Noun} deleted successfully.',
        ]);
    }
}
```

**Response format** is always:

```json
{
    "message": "Human-readable message.",
    "data": { ... }
}
```

- `201` for successful creates
- `200` for successful reads/updates/deletes
- `404` for not found
- `401` for unauthorized
- `422` for validation errors (automatic via FormRequest)

### 4.8 Form Requests

FormRequests include `authorize()`, `rules()`, and `messages()`. Use array-based rules (not string-based). Use `Rule::enum()` for enum validation. Reference the correct database connection in unique/exists rules.

```php
<?php

namespace App\Modules\{Module}\Http\Requests;

use App\Modules\{Module}\Enums\{Noun}Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Create{Noun}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'integer', 'exists:landlord.tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::enum({Noun}Status::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tenant_id.required' => 'Tenant is required.',
            'tenant_id.exists' => 'The selected tenant does not exist.',
            'name.required' => 'Name is required.',
        ];
    }
}
```

**Key rules:**
- Landlord table references use `landlord.` prefix: `'exists:landlord.tenants,id'`
- Tenant table references use `tenant.` prefix: `'unique:tenant.users,email'`
- Enum fields use `Rule::enum(EnumClass::class)`

### 4.9 Service Providers

Each module has a service provider registered in `bootstrap/providers.php`. Register repositories and services as singletons. Services with constructor dependencies use explicit factory closures.

```php
<?php

namespace App\Modules\{Module}\Providers;

use App\Modules\{Module}\Repositories\{Noun}Repository;
use App\Modules\{Module}\Services\{Noun}Service;
use Illuminate\Support\ServiceProvider;

class {Module}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton({Noun}Repository::class);

        $this->app->singleton({Noun}Service::class, function ($app) {
            return new {Noun}Service(
                $app->make({Noun}Repository::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
```

### 4.10 Factories

Factories live in `database/factories/` (not inside modules). Set `protected $model`. Use `fake()` helper (not `$this->faker`). Define meaningful states.

```php
<?php

namespace Database\Factories;

use App\Modules\{Module}\Models\{Noun};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<{Noun}>
 */
class {Noun}Factory extends Factory
{
    protected $model = {Noun}::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the record is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the record is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
```

### 4.11 Shared Contracts & Traits

Located in `app/Shared/`:

| Item | Type | Purpose |
|------|------|---------|
| `ActionContract` | Interface | Marker interface for action classes (no method signature enforced) |
| `RepositoryContract` | Interface | Defines `all()`, `findById()`, `create()`, `update()`, `delete()` |
| `ServiceContract` | Interface | Marker interface for service classes |
| `UsesLandlordConnection` | Trait | Sets model connection to landlord DB |
| `UsesTenantConnection` | Trait | Sets model connection to tenant DB |
| `TenantDatabaseException` | Exception | Thrown when tenant DB operations fail |
| `TenantNotFoundException` | Exception | Thrown when a tenant cannot be resolved |

### 4.12 PHP Conventions

- Always use **curly braces** for control structures, even single-line bodies
- Use **PHP 8 constructor property promotion** — no empty constructors with zero parameters
- Use **`! $variable`** with a space after `!` (Laravel style, enforced by Pint)
- Use `readonly` on DTO constructor properties
- Enum keys are **TitleCase**: `Active`, `Trial`, `Enterprise`
- Prefer PHPDoc blocks over inline comments
- No comments within code unless logic is exceptionally complex

---

## 5. Adding a New Module

Follow this checklist exactly. Do not skip steps.

### Step 1: Create the module directory structure

```
app/Modules/{ModuleName}/
├── DTOs/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
├── Providers/
├── Repositories/
└── Services/
```

Only add `Actions/`, `Enums/`, `Events/`, `Listeners/`, `Filament/` if needed.

### Step 2: Create the Model

- Use `UsesLandlordConnection` or `UsesTenantConnection` trait
- Add `#[UseFactory(FactoryClass::class)]` attribute
- Define `$fillable`, `casts()` method, and relationships
- Add `@property` PHPDoc annotations

### Step 3: Create the Factory

- Place in `database/factories/{Noun}Factory.php`
- Set `protected $model`
- Define useful states (`active()`, `inactive()`, etc.)

### Step 4: Create the Migration

- Landlord tables: `database/migrations/landlord/`
- Tenant tables: `database/migrations/tenant/`
- Run with `php artisan migrate --database=landlord` or via tenant artisan

### Step 5: Create DTOs

- `Create{Noun}DTO` with required fields
- `Update{Noun}DTO` with all-nullable fields
- Both need `fromArray()` and `toArray()` methods

### Step 6: Create Enums (if applicable)

- Backed string enum with `label()` method
- Add `color()` if displayed in UI
- Cast in model's `casts()` method

### Step 7: Create Repository

- Implement `RepositoryContract`
- Standard CRUD methods: `all()`, `findById()`, `create()`, `update()`, `delete()`
- Add custom finder methods as needed

### Step 8: Create Service

- Implement `ServiceContract`
- Inject repository via constructor
- Orchestrate business logic using DTOs

### Step 9: Create Actions (if applicable)

- Implement `ActionContract`
- Single `execute()` method
- Convert raw array input to DTO internally

### Step 10: Create Controller

- Extend `App\Http\Controllers\Controller`
- Inject service and repository
- Standard CRUD methods: `index()`, `store()`, `show()`, `update()`, `destroy()`
- Use FormRequest for validation, DTO for data passing

### Step 11: Create Form Requests

- `Create{Noun}Request` with all required fields
- `Update{Noun}Request` with `sometimes` on all fields
- Include `authorize()`, `rules()`, `messages()`

### Step 12: Create Service Provider

- Register repository and service as singletons
- Register in `bootstrap/providers.php`

### Step 13: Add Routes

- Add route group in `routes/api.php`
- Follow existing naming convention: `api.{plural_noun}.{action}`
- Auth-protected routes use `auth:sanctum` middleware

### Step 14: Create Filament Resources (if admin management needed)

- Follow the exact directory structure from Section 3
- Add `discoverResources()` call in `AdminPanelProvider.php`

### Step 15: Create Tests

- Feature tests in `tests/Feature/{ModuleName}/`
- Unit tests in `tests/Unit/{ModuleName}/`
- See Section 10 for testing patterns

### Step 16: Run Pint and Tests

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
```

---

## 6. Adding a Feature to an Existing Module

### Before writing any code:

1. **Read sibling files** — check existing controllers, services, DTOs, requests in the same module to match the exact style
2. **Check if a similar pattern exists** — reuse existing code structures
3. **Determine the database connection** — landlord or tenant?

### Checklist:

1. Create/update migration (correct directory: `landlord/` or `tenant/`)
2. Update model if needed (new fields in `$fillable`, new relationships, new casts)
3. Update or create DTO(s)
4. Update or create enum(s) if new statuses/types are introduced
5. Update repository with new query methods if needed
6. Update service with new business logic
7. Create action if the operation is a focused, single-responsibility task
8. Update or create controller method
9. Create FormRequest if new endpoint requires validation
10. Add route in `routes/api.php`
11. Update Filament resource form/table if the admin panel should reflect the change
12. Update factory states if new fields/states are added
13. Write tests
14. Run `vendor/bin/pint --dirty --format agent`
15. Run `php artisan test --compact`

---

## 7. Database & Multi-Tenancy

### Connections

| Connection | Purpose | Database |
|-----------|---------|----------|
| `landlord` | Central/admin data (default) | `mahir_landlord` |
| `tenant` | Tenant-scoped data (runtime-switched) | `mahir_tenant_{slug}` |
| `sqlite` | Tests only | `:memory:` |

### Model-to-Connection Mapping

| Model | Trait | Database |
|-------|-------|----------|
| `Tenant` | `UsesLandlordConnection` | `mahir_landlord` |
| `AdminUser` | `UsesLandlordConnection` | `mahir_landlord` |
| `Subscription` | `UsesLandlordConnection` | `mahir_landlord` |
| `User` | `UsesTenantConnection` | `mahir_tenant_{slug}` |
| `PersonalAccessToken` | `UsesTenantConnection` | `mahir_tenant_{slug}` |

### Migration Directories

| Directory | Target |
|-----------|--------|
| `database/migrations/landlord/` | Landlord DB tables |
| `database/migrations/tenant/` | Tenant DB tables |
| `database/migrations/_legacy/` | Unused. Do not touch. |

Both paths are loaded in `AppServiceProvider::boot()` via `loadMigrationsFrom()`.

### Tenant Resolution Flow

```
HTTP request to {slug}.mahir.test/api/v1/*
  -> IdentifyTenant middleware (prepended to `api` group in bootstrap/app.php)
  -> SubdomainTenantFinder extracts slug from subdomain
  -> Tenant::where('slug', $slug)->first()
  -> $tenant->makeCurrent()
  -> SwitchTenantDatabaseTask sets `tenant` connection DB to mahir_tenant_{slug}
  -> PrefixCacheTask prefixes cache keys with tenant ID
  -> Controller handles request with tenant context
```

### Subdomain Patterns

| URL | Purpose |
|-----|---------|
| `{slug}.mahir.test/api/v1/*` | Tenant API |
| `admin.mahir.test` | Filament admin panel |
| `mahir.test` | Landing/web |

Reserved subdomains that skip tenant resolution: `admin`, `www`.

### Validation Rules for Cross-Connection References

```php
// Referencing landlord tables
'tenant_id' => ['required', 'exists:landlord.tenants,id'],

// Referencing tenant tables
'email' => ['required', 'unique:tenant.users,email'],
```

---

## 8. API & Routing

### Route Configuration

- All API routes prefixed with `/api/v1` (configured in `bootstrap/app.php` via `apiPrefix`)
- Routes defined in `routes/api.php`
- `IdentifyTenant` middleware is prepended to the `api` middleware group

### Route Structure Pattern

```php
use App\Modules\{Module}\Http\Controllers\{Noun}Controller;

Route::middleware('auth:sanctum')->prefix('{plural_noun}')->group(function () {
    Route::get('/', [{Noun}Controller::class, 'index'])->name('api.{plural_noun}.index');
    Route::post('/', [{Noun}Controller::class, 'store'])->name('api.{plural_noun}.store');
    Route::get('/{{noun}}', [{Noun}Controller::class, 'show'])->name('api.{plural_noun}.show');
    Route::put('/{{noun}}', [{Noun}Controller::class, 'update'])->name('api.{plural_noun}.update');
    Route::delete('/{{noun}}', [{Noun}Controller::class, 'destroy'])->name('api.{plural_noun}.destroy');
});
```

### Route Naming Convention

All API route names follow: `api.{plural_noun}.{action}`

### Current Routes

| Method | Endpoint | Name | Auth |
|--------|----------|------|------|
| GET | `/api/v1/ping` | `api.ping` | No |
| POST | `/api/v1/auth/register` | `api.auth.register` | No |
| POST | `/api/v1/auth/login` | `api.auth.login` | No |
| POST | `/api/v1/auth/logout` | `api.auth.logout` | `auth:sanctum` |
| GET | `/api/v1/auth/user` | `api.auth.user` | `auth:sanctum` |
| GET | `/api/v1/tenants` | `api.tenants.index` | `auth:sanctum` |
| POST | `/api/v1/tenants` | `api.tenants.store` | `auth:sanctum` |
| GET | `/api/v1/tenants/{tenant}` | `api.tenants.show` | `auth:sanctum` |
| PUT | `/api/v1/tenants/{tenant}` | `api.tenants.update` | `auth:sanctum` |
| DELETE | `/api/v1/tenants/{tenant}` | `api.tenants.destroy` | `auth:sanctum` |
| GET | `/api/v1/subscriptions` | `api.subscriptions.index` | `auth:sanctum` |
| POST | `/api/v1/subscriptions` | `api.subscriptions.store` | `auth:sanctum` |
| GET | `/api/v1/subscriptions/{subscription}` | `api.subscriptions.show` | `auth:sanctum` |
| PUT | `/api/v1/subscriptions/{subscription}` | `api.subscriptions.update` | `auth:sanctum` |
| DELETE | `/api/v1/subscriptions/{subscription}` | `api.subscriptions.destroy` | `auth:sanctum` |

---

## 9. Filament Admin Panel

### Overview

The admin panel at `admin.mahir.test` uses the `admin` auth guard with `AdminUser` model. Filament v5 is used.

### Resource Directory Structure

```
{Module}/Filament/Resources/{PluralNoun}/
├── {Noun}Resource.php
├── Pages/
│   ├── Create{Noun}.php        # extends CreateRecord
│   ├── Edit{Noun}.php          # extends EditRecord, has DeleteAction
│   └── List{PluralNoun}.php    # extends ListRecords, has CreateAction
├── Schemas/
│   └── {Noun}Form.php          # Static configure(Schema $schema) method
└── Tables/
    └── {PluralNoun}Table.php   # Static configure(Table $table) method
```

### Registration

Resources are auto-discovered via `AdminPanelProvider.php` with `discoverResources()` calls per module:

```php
->discoverResources(
    in: app_path('Modules/{Module}/Filament/Resources'),
    for: 'App\\Modules\\{Module}\\Filament\\Resources'
)
```

When adding a new module's Filament resources, add another `discoverResources()` call.

### Key Filament v5 Differences

- Uses `Filament\Schemas\Schema` (not `Filament\Forms\Form`)
- Uses `recordActions` / `toolbarActions` (not `actions` / `bulkActions`)
- Uses Heroicon enum constants (e.g., `Heroicon::OutlinedUsers`)

---

## 10. Testing

### Framework & Configuration

- **Pest v4** with `RefreshDatabase`
- `tests/Pest.php` extends `Tests\TestCase` with `RefreshDatabase` for both `Feature` and `Unit` directories
- All tests use SQLite in-memory via `phpunit.xml`
- `TestCase::setUp()` shares a single SQLite PDO across `sqlite`, `tenant`, and `landlord` connections

### Test Directory Structure

```
tests/
├── Pest.php          # Pest config
├── TestCase.php      # Base test case with DB connection sharing
├── Feature/
│   └── {ModuleName}/
│       └── {Noun}CrudTest.php    # or {Noun}Test.php
└── Unit/
    └── {ModuleName}/
        └── {Noun}ServiceTest.php  # or {Noun}ActionTest.php
```

### Feature Test Pattern (CRUD)

```php
<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;
use App\Modules\{Module}\Models\{Noun};

beforeEach(function () {
    $this->withoutMiddleware(IdentifyTenant::class);
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
});

/*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
*/

test('can list all {plural_noun}', function () {
    {Noun}::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/{plural_noun}');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing {plural_noun} returns empty array when none exist', function () {
    $response = $this->getJson('/api/v1/{plural_noun}');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

test('can create a {noun}', function () {
    $response = $this->postJson('/api/v1/{plural_noun}', [
        // ... valid data
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', '{Noun} created successfully.');
});

test('creating {noun} fails without required_field', function () {
    $response = $this->postJson('/api/v1/{plural_noun}', [
        // ... missing required field
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['required_field']);
});

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

test('can show a {noun}', function () {
    $record = {Noun}::factory()->create();

    $response = $this->getJson("/api/v1/{plural_noun}/{$record->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $record->id);
});

test('showing a non-existent {noun} returns 404', function () {
    $response = $this->getJson('/api/v1/{plural_noun}/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

test('can update a {noun}', function () {
    $record = {Noun}::factory()->create();

    $response = $this->putJson("/api/v1/{plural_noun}/{$record->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', '{Noun} updated successfully.')
        ->assertJsonPath('data.name', 'Updated Name');
});

test('updating a non-existent {noun} returns 404', function () {
    $response = $this->putJson('/api/v1/{plural_noun}/99999', [
        'name' => 'Updated',
    ]);

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete a {noun}', function () {
    $record = {Noun}::factory()->create();

    $response = $this->deleteJson("/api/v1/{plural_noun}/{$record->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', '{Noun} deleted successfully.');

    $this->assertDatabaseMissing('{table_name}', ['id' => $record->id]);
});

test('deleting a non-existent {noun} returns 404', function () {
    $response = $this->deleteJson('/api/v1/{plural_noun}/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot access {plural_noun}', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/{plural_noun}');

    $response->assertUnauthorized();
});
```

### Unit Test Pattern (Services/Actions)

```php
<?php

use App\Modules\{Module}\DTOs\Create{Noun}DTO;
use App\Modules\{Module}\Models\{Noun};
use App\Modules\{Module}\Services\{Noun}Service;

test('can create a {noun} via service', function () {
    $service = app({Noun}Service::class);

    $dto = new Create{Noun}DTO(
        name: 'Test Name',
    );

    $result = $service->create{Noun}($dto);

    expect($result)
        ->toBeInstanceOf({Noun}::class)
        ->name->toBe('Test Name');
});

test('{noun} is persisted to database', function () {
    $service = app({Noun}Service::class);

    $dto = new Create{Noun}DTO(
        name: 'Persisted',
    );

    $service->create{Noun}($dto);

    $this->assertDatabaseHas('{table_name}', [
        'name' => 'Persisted',
    ]);
});
```

### Key Testing Rules

1. **Feature tests MUST** call `$this->withoutMiddleware(IdentifyTenant::class)` in `beforeEach` to skip subdomain resolution
2. **Authenticated tests MUST** use `$this->actingAs($user, 'sanctum')`
3. **Use factories** with states — check factory for existing states before manually setting up data
4. **Use `fake()` helper** (not `$this->faker`)
5. **Test happy paths AND validation failures AND 404s AND auth**
6. **Group tests** with section headers using `/* |--- Section ---| */` comments
7. **Most tests should be Feature tests** — use `php artisan make:test --pest {Name}` (feature by default) or `--unit` for unit tests
8. **Mock external services** (like `TenantDatabaseService`) that interact with real databases in tests
9. **To mock Artisan commands** in tenant creation tests: `Artisan::shouldReceive('call')`

### Running Tests

```bash
# All tests
php artisan test --compact

# Specific test file
php artisan test --compact --filter={TestName}

# Only unit tests
php artisan test --compact --testsuite=Unit

# Only feature tests
php artisan test --compact --testsuite=Feature
```

---

## 11. Pre-Commit Checklist

Run this every time before considering your work complete:

```bash
# 1. Format code
vendor/bin/pint --dirty --format agent

# 2. Run all tests
php artisan test --compact
```

### Verify:

- [ ] All new PHP files follow the module directory structure
- [ ] No files created outside `app/Modules/` for domain logic
- [ ] DTOs used for all data passing between layers
- [ ] Enums used for all status/type string fields
- [ ] FormRequests used for all validation (no inline validation)
- [ ] Models have `#[UseFactory]` attribute, correct connection trait, `casts()` method
- [ ] Factory created in `database/factories/` with useful states
- [ ] Migration in correct directory (`landlord/` or `tenant/`)
- [ ] Routes added with correct naming convention and middleware
- [ ] Service provider updated and registered in `bootstrap/providers.php`
- [ ] Feature tests cover: CRUD operations, validation errors, 404s, unauthenticated access
- [ ] Unit tests cover: service methods, action execution
- [ ] Pint runs clean with no formatting issues
- [ ] All tests pass

---

## 12. Common Pitfalls

### Factory Resolution for Module Models

Models under `App\Modules\*` namespaces break Laravel's auto-discovery for factories. **Always** add `#[UseFactory(FactoryClass::class)]` attribute on module models.

### SQLite In-Memory and Multi-Connection Testing

Each named `:memory:` SQLite connection gets its own isolated database. `TestCase::setUp()` must share a single PDO instance across all connections. If you add a new database connection, update `TestCase.php` to share the PDO.

### Sanctum `actingAs()` Limitation

When using `$this->actingAs($user, 'sanctum')` in tests, `$user->currentAccessToken()` returns `null`. That's why `AuthService::logout()` uses the null-safe operator: `$user->currentAccessToken()?->delete()`.

### Tenant Middleware in Tests

Feature tests hitting API endpoints must call `$this->withoutMiddleware(IdentifyTenant::class)` because there is no real subdomain in the test environment.

### Validation Rule Connection Prefixes

When referencing tables in validation rules, use the connection prefix:
- `'exists:landlord.tenants,id'` (not `'exists:tenants,id'`)
- `'unique:tenant.users,email'` (not `'unique:users,email'`)

Without the prefix, validation rules may query the wrong database.

### Migration Column Modifications

In Laravel 12, when modifying a column, the migration must include ALL attributes previously defined on the column. Omitted attributes will be dropped.

### Queue Tenant Awareness

`queues_are_tenant_aware_by_default` is `true`. All queued jobs automatically restore tenant context. Use `TenantAware` or `NotTenantAware` interfaces for explicit control.

### Filament v5 API Differences

Filament v5 uses different APIs than v3. Always search documentation first:
- `Schema` instead of `Form`
- `recordActions` / `toolbarActions` instead of `actions` / `bulkActions`
- Heroicon enum constants instead of string icon names

---

## 13. Reference: File Patterns

### Bootstrap & Configuration

| File | Purpose |
|------|---------|
| `bootstrap/app.php` | Middleware, routing, exceptions. IdentifyTenant prepended to api group. |
| `bootstrap/providers.php` | All service provider registrations (order matters). |
| `config/database.php` | landlord, tenant, sqlite connections. |
| `config/multitenancy.php` | Spatie config: tenant finder, switch tasks, queue awareness. |
| `config/auth.php` | Guards (web, admin, sanctum) and user providers. |
| `phpunit.xml` | Test environment: SQLite in-memory, env overrides. |

### Provider Registration Order

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Modules\Tenancy\Providers\TenancyServiceProvider::class,
    App\Modules\Auth\Providers\AuthServiceProvider::class,
    App\Modules\Subscription\Providers\SubscriptionServiceProvider::class,
];
```

When adding a new module provider, append it to this list.

### Shared Layer

| File | Purpose |
|------|---------|
| `app/Shared/Contracts/ActionContract.php` | Marker interface for actions |
| `app/Shared/Contracts/RepositoryContract.php` | CRUD interface for repositories |
| `app/Shared/Contracts/ServiceContract.php` | Marker interface for services |
| `app/Shared/Traits/UsesLandlordConnection.php` | Sets model DB to landlord |
| `app/Shared/Traits/UsesTenantConnection.php` | Sets model DB to tenant |
| `app/Shared/Exceptions/TenantDatabaseException.php` | Tenant DB operation failures |
| `app/Shared/Exceptions/TenantNotFoundException.php` | Tenant not found errors |
