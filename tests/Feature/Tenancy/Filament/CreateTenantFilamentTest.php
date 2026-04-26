<?php

use App\Modules\Auth\Models\AdminUser;
use App\Modules\Tenancy\Filament\Resources\Tenants\Pages\CreateTenant;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantDatabaseService;
use App\Shared\Exceptions\TenantDatabaseException;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $adminUser = AdminUser::factory()->create();
    $this->actingAs($adminUser, 'admin');

    Filament::setCurrentPanel(
        Filament::getPanel('landlord'),
    );
});

/*
|--------------------------------------------------------------------------
| Page Load
|--------------------------------------------------------------------------
*/

test('can load the create tenant page', function () {
    Livewire::test(CreateTenant::class)
        ->assertOk();
});

/*
|--------------------------------------------------------------------------
| Successful Creation
|--------------------------------------------------------------------------
*/

test('creating a tenant provisions the database, runs migrations, and seeds', function () {
    $mockDbService = Mockery::mock(TenantDatabaseService::class);
    $mockDbService->shouldReceive('generateDatabaseName')
        ->with('acme-corp')
        ->andReturn('mahir_tenant_acme_corp');
    $mockDbService->shouldReceive('databaseExists')
        ->with('mahir_tenant_acme_corp')
        ->andReturn(false);
    $mockDbService->shouldReceive('createDatabase')
        ->once()
        ->with('mahir_tenant_acme_corp');
    $this->app->instance(TenantDatabaseService::class, $mockDbService);

    Artisan::shouldReceive('call')
        ->once()
        ->withArgs(fn (string $command, array $params): bool => $command === 'tenants:artisan'
            && str_contains($params['artisanCommand'], 'migrate'));

    Artisan::shouldReceive('output')
        ->andReturn('Migration output');

    Artisan::shouldReceive('call')
        ->once()
        ->withArgs(fn (string $command, array $params): bool => $command === 'tenants:artisan'
            && str_contains($params['artisanCommand'], 'db:seed'));

    Artisan::shouldReceive('output')
        ->andReturn('Seeder output');

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Acme Corp',
            'slug' => 'acme-corp',
            'domain' => 'acme-corp.mahir.test',
            'database' => 'mahir_tenant_acme_corp',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(Tenant::class, [
        'name' => 'Acme Corp',
        'slug' => 'acme-corp',
        'domain' => 'acme-corp.mahir.test',
        'database' => 'mahir_tenant_acme_corp',
    ]);
});

/*
|--------------------------------------------------------------------------
| Database Error Handling
|--------------------------------------------------------------------------
*/

test('shows notification when database already exists', function () {
    $mockDbService = Mockery::mock(TenantDatabaseService::class);
    $mockDbService->shouldReceive('generateDatabaseName')
        ->with('existing-tenant')
        ->andReturn('mahir_tenant_existing_tenant');
    $mockDbService->shouldReceive('databaseExists')
        ->with('mahir_tenant_existing_tenant')
        ->andReturn(true);
    $this->app->instance(TenantDatabaseService::class, $mockDbService);

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Existing Tenant',
            'slug' => 'existing-tenant',
            'domain' => 'existing-tenant.mahir.test',
            'database' => 'mahir_tenant_existing_tenant',
        ])
        ->call('create')
        ->assertNotified('Database provisioning failed')
        ->assertNoRedirect();
});

test('shows notification when database creation fails', function () {
    $mockDbService = Mockery::mock(TenantDatabaseService::class);
    $mockDbService->shouldReceive('generateDatabaseName')
        ->with('fail-tenant')
        ->andReturn('mahir_tenant_fail_tenant');
    $mockDbService->shouldReceive('databaseExists')
        ->with('mahir_tenant_fail_tenant')
        ->andReturn(false);
    $mockDbService->shouldReceive('createDatabase')
        ->with('mahir_tenant_fail_tenant')
        ->andThrow(TenantDatabaseException::failedToCreate('mahir_tenant_fail_tenant', 'Connection refused'));
    $this->app->instance(TenantDatabaseService::class, $mockDbService);

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Fail Tenant',
            'slug' => 'fail-tenant',
            'domain' => 'fail-tenant.mahir.test',
            'database' => 'mahir_tenant_fail_tenant',
        ])
        ->call('create')
        ->assertNotified('Database provisioning failed')
        ->assertNoRedirect();

    $this->assertDatabaseMissing('tenants', ['slug' => 'fail-tenant']);
});

test('shows notification when migration fails', function () {
    $mockDbService = Mockery::mock(TenantDatabaseService::class);
    $mockDbService->shouldReceive('generateDatabaseName')
        ->with('migrate-fail')
        ->andReturn('mahir_tenant_migrate_fail');
    $mockDbService->shouldReceive('databaseExists')
        ->with('mahir_tenant_migrate_fail')
        ->andReturn(false);
    $mockDbService->shouldReceive('createDatabase')
        ->once()
        ->with('mahir_tenant_migrate_fail');
    $this->app->instance(TenantDatabaseService::class, $mockDbService);

    Artisan::shouldReceive('call')
        ->once()
        ->withArgs(fn (string $command, array $params): bool => $command === 'tenants:artisan'
            && str_contains($params['artisanCommand'], 'migrate'))
        ->andThrow(new RuntimeException('Migration table not found'));

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Migrate Fail',
            'slug' => 'migrate-fail',
            'domain' => 'migrate-fail.mahir.test',
            'database' => 'mahir_tenant_migrate_fail',
        ])
        ->call('create')
        ->assertNotified('Database provisioning failed')
        ->assertNoRedirect();
});

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

test('validates required form fields', function (array $data, array $errors) {
    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Valid Name',
            'slug' => 'valid-slug',
            'domain' => 'valid-slug.mahir.test',
            'database' => 'mahir_tenant_valid_slug',
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNoRedirect();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`slug` is required' => [['slug' => null], ['slug' => 'required']],
    '`domain` is required' => [['domain' => null], ['domain' => 'required']],
    '`database` is required' => [['database' => null], ['database' => 'required']],
]);

test('validates slug uniqueness', function () {
    Tenant::factory()->create(['slug' => 'taken-slug']);

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Test',
            'slug' => 'taken-slug',
            'domain' => 'taken-slug.mahir.test',
            'database' => 'mahir_tenant_taken_slug',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique'])
        ->assertNoRedirect();
});

/*
|--------------------------------------------------------------------------
| Logging
|--------------------------------------------------------------------------
*/

test('logs successful tenant creation', function () {
    Log::spy();

    $mockDbService = Mockery::mock(TenantDatabaseService::class);
    $mockDbService->shouldReceive('generateDatabaseName')
        ->with('log-test')
        ->andReturn('mahir_tenant_log_test');
    $mockDbService->shouldReceive('databaseExists')
        ->with('mahir_tenant_log_test')
        ->andReturn(false);
    $mockDbService->shouldReceive('createDatabase')
        ->once()
        ->with('mahir_tenant_log_test');
    $this->app->instance(TenantDatabaseService::class, $mockDbService);

    Artisan::shouldReceive('call')
        ->twice()
        ->andReturn(0);
    Artisan::shouldReceive('output')
        ->andReturn('');

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Log Test',
            'slug' => 'log-test',
            'domain' => 'log-test.mahir.test',
            'database' => 'mahir_tenant_log_test',
        ])
        ->call('create')
        ->assertRedirect();

    Log::shouldHaveReceived('info')
        ->withArgs(fn (string $message): bool => str_contains($message, 'Tenant created via Filament'));
});

test('logs database creation failure', function () {
    Log::spy();

    $mockDbService = Mockery::mock(TenantDatabaseService::class);
    $mockDbService->shouldReceive('generateDatabaseName')
        ->with('log-fail')
        ->andReturn('mahir_tenant_log_fail');
    $mockDbService->shouldReceive('databaseExists')
        ->with('mahir_tenant_log_fail')
        ->andReturn(false);
    $mockDbService->shouldReceive('createDatabase')
        ->with('mahir_tenant_log_fail')
        ->andThrow(TenantDatabaseException::failedToCreate('mahir_tenant_log_fail', 'Access denied'));
    $this->app->instance(TenantDatabaseService::class, $mockDbService);

    Livewire::test(CreateTenant::class)
        ->fillForm([
            'name' => 'Log Fail',
            'slug' => 'log-fail',
            'domain' => 'log-fail.mahir.test',
            'database' => 'mahir_tenant_log_fail',
        ])
        ->call('create')
        ->assertNoRedirect();

    Log::shouldHaveReceived('error')
        ->withArgs(fn (string $message): bool => str_contains($message, 'Tenant database provisioning failed'));
});
