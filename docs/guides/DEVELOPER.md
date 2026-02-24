# Developer Guide

Local setup, testing, and daily development workflows for Mahir.

---

## Prerequisites

- PHP 8.3+
- MySQL 8.0+
- Composer
- Node.js & npm
- [Laravel Herd](https://herd.laravel.com/) (serves the app automatically)

---

## Local Setup

```bash
# 1. Clone and install dependencies
git clone <repo-url> mahir
cd mahir
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Create databases
# Create two MySQL databases: mahir_landlord and one tenant DB (e.g. mahir_tenant_demo)

# 4. Run landlord migrations
php artisan migrate --database=landlord

# 5. Seed (optional)
php artisan db:seed

# 6. Build frontend assets
npm run build
```

The app is served by Laravel Herd at `https://mahir.test`. No need to run `php artisan serve`.

---

## Key URLs

| URL | Purpose |
|-----|---------|
| `mahir.test` | Landing page |
| `admin.mahir.test` | Filament admin panel |
| `{slug}.mahir.test/api/v1/*` | Tenant API |

---

## Daily Commands

```bash
# Run all tests
php artisan test --compact

# Run specific test
php artisan test --compact --filter=LoginTest

# Run only unit/feature tests
php artisan test --compact --testsuite=Unit
php artisan test --compact --testsuite=Feature

# Format code (run before every commit)
vendor/bin/pint --dirty --format agent

# Build frontend
npm run build

# Watch frontend (dev)
npm run dev

# List all artisan commands
php artisan list

# List all routes
php artisan route:list
```

---

## Project Structure (Quick Reference)

```
app/Modules/          # All domain logic (Auth, Subscription, Tenancy)
app/Shared/           # Cross-module contracts, traits, exceptions
app/Providers/        # App-level service providers
bootstrap/            # App bootstrap, middleware, providers list
config/               # Configuration files
database/migrations/
  landlord/           # Landlord DB migrations
  tenant/             # Tenant DB migrations
routes/api.php        # API route definitions
tests/Feature/        # HTTP/integration tests
tests/Unit/           # Unit tests
```

---

## Debugging Tips

- **Last backend error**: Use Laravel Boost's `last-error` tool or check `storage/logs/laravel.log`
- **Browser errors**: Use Laravel Boost's `browser-logs` tool
- **Database queries**: Use `DB::listen()` in tinker or Laravel Telescope/Debugbar
- **Vite manifest error**: Run `npm run build` or `npm run dev`
- **Test failures**: Tests use SQLite in-memory. All connections (landlord, tenant) share a single SQLite DB via `TestCase::setUp()`
