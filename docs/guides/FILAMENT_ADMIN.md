# Filament Admin Guide

Using and extending the Mahir admin panel built with Filament v5.

---

## Access

| Detail | Value |
|--------|-------|
| URL | `admin.mahir.test` |
| Auth guard | `admin` (session-based) |
| User model | `AdminUser` (landlord DB) |

Login with an `AdminUser` account. Create one via seeder or tinker:

```php
AdminUser::create([
    'name' => 'Admin',
    'email' => 'admin@mahir.test',
    'password' => Hash::make('password'),
]);
```

---

## Navigation

The admin panel has three resource sections:

| Section | Resource | Module | Manages |
|---------|----------|--------|---------|
| Administration | Admin Users | Auth | Admin accounts for the panel |
| Tenancy | Tenants | Tenancy | Tenant records (name, slug, domain, status) |
| Tenancy | Subscriptions | Subscription | Tenant subscription plans & statuses |

Each resource supports full CRUD: list, create, edit, delete.

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
2. Follow an existing resource as a template (e.g., `Tenants/`)
3. Register discovery in `app/Providers/Filament/AdminPanelProvider.php`:

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

Always check the [Filament v5 docs](https://filamentphp.com/docs) or use the `search-docs` tool for version-specific guidance.
