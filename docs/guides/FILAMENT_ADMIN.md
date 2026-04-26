# Filament Admin Guide

Using and extending the Mahir admin panels built with Filament v5.

---

## Overview

Mahir has **two Filament panels**:

| Panel | URL | Auth Guard | User Model | Provider |
|-------|-----|-----------|-----------|----------|
| Landlord | `admin.mahir.test` | `admin` (session) | `AdminUser` (landlord DB) | `LandlordPanelProvider` |
| Tenant | `{slug}.mahir.test/admin` | `tenant` (session) | `User` (tenant DB) | `TenantPanelProvider` |

---

## Landlord Panel

### Access

Login with an `AdminUser` account. Create one via seeder or tinker:

```php
AdminUser::create([
    'name' => 'Admin',
    'email' => 'admin@mahir.test',
    'password' => Hash::make('password'),
]);
```

### Navigation

| Section | Resource | Module | Manages |
|---------|----------|--------|---------|
| Administration | Admin Users | Auth | Admin accounts for the panel |
| Tenancy | Tenants | Tenancy | Tenant records (name, slug, domain, status) |
| Tenancy | Subscriptions | Subscription | Tenant subscription plans & statuses |

---

## Tenant Panel

### Access

Login at `{slug}.mahir.test/admin` with a tenant `User` account. The subdomain determines which tenant's data is shown. The `EnsureTenantPanel` middleware (`app/Http/Middleware/EnsureTenantPanel.php`) resolves and activates the correct tenant from the subdomain before Filament handles the request.

### Navigation

| Section | Resource | Module | Manages |
|---------|----------|--------|---------|
| Content | Articles | Article | Blog articles |
| Content | Article Series | Article | Article series groupings |
| Portfolio | Portfolio Categories | Portfolio | Portfolio category taxonomy |
| Portfolio | Portfolios | Portfolio | Portfolio projects with media |
| Portfolio | Testimonials | Portfolio | Client testimonials |

---

## Resource Structure

Every Filament resource lives inside its module at `app/Modules/{Module}/Filament/Resources/{PluralNoun}/`:

```
{PluralNoun}/
├── {Noun}Resource.php          # Main resource class
├── Pages/
│   ├── Create{Noun}.php        # Create page
│   ├── Edit{Noun}.php          # Edit page (includes delete action)
│   └── List{PluralNoun}.php    # List page (includes create action)
├── Schemas/
│   └── {Noun}Form.php          # Form fields (shared between create/edit)
└── Tables/
    └── {PluralNoun}Table.php   # Table columns, filters, actions
```

---

## Adding a New Resource

1. Create the directory structure above inside your module
2. Follow an existing resource as a template (e.g., `Tenants/` for landlord, `Articles/` for tenant)
3. Register discovery in the appropriate panel provider:

**Landlord resource** → `app/Providers/Filament/LandlordPanelProvider.php`:

```php
->discoverResources(
    in: app_path('Modules/{Module}/Filament/Resources'),
    for: 'App\\Modules\\{Module}\\Filament\\Resources'
)
```

**Tenant resource** → `app/Providers/Filament/TenantPanelProvider.php`:

```php
->discoverResources(
    in: app_path('Modules/{Module}/Filament/Resources'),
    for: 'App\\Modules\\{Module}\\Filament\\Resources'
)
```

---

## Key Filament v5 Notes

- **Schema class**: Use `Filament\Schemas\Schema` (not `Filament\Forms\Form`)
- **Table actions**: Use `recordActions` and `toolbarActions` (not `actions` / `bulkActions`)
- **Icons**: Use Heroicon enum constants (e.g., `Heroicon::OutlinedUsers`)
- **Enum integration**: Use `Rule::enum()` for selects and badge colors via `color()` method on enums

Always use the `search-docs` tool for version-specific Filament guidance.
