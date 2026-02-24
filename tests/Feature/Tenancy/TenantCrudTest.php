<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Services\TenantDatabaseService;
use Illuminate\Support\Facades\Artisan;

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

test('can list all tenants', function () {
    Tenant::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/tenants');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing tenants returns empty array when none exist', function () {
    $response = $this->getJson('/api/v1/tenants');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

test('can create a tenant', function () {
    $mockDbService = Mockery::mock(TenantDatabaseService::class);
    $mockDbService->shouldReceive('generateDatabaseName')
        ->once()
        ->with('acme-corp')
        ->andReturn('mahir_tenant_acme_corp');
    $mockDbService->shouldReceive('createDatabase')
        ->once()
        ->with('mahir_tenant_acme_corp');
    $this->app->instance(TenantDatabaseService::class, $mockDbService);

    Artisan::shouldReceive('call')
        ->once()
        ->withArgs(function (string $command, array $params): bool {
            return $command === 'tenants:artisan'
                && str_contains($params['artisanCommand'], 'migrate');
        });

    $response = $this->postJson('/api/v1/tenants', [
        'name' => 'Acme Corp',
        'slug' => 'acme-corp',
        'domain' => 'acme-corp.mahir.test',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Tenant created successfully.')
        ->assertJsonPath('data.name', 'Acme Corp')
        ->assertJsonPath('data.slug', 'acme-corp')
        ->assertJsonPath('data.domain', 'acme-corp.mahir.test');
});

test('creating tenant fails without name', function () {
    $response = $this->postJson('/api/v1/tenants', [
        'slug' => 'acme-corp',
        'domain' => 'acme-corp.mahir.test',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('creating tenant fails without slug', function () {
    $response = $this->postJson('/api/v1/tenants', [
        'name' => 'Acme Corp',
        'domain' => 'acme-corp.mahir.test',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

test('creating tenant fails without domain', function () {
    $response = $this->postJson('/api/v1/tenants', [
        'name' => 'Acme Corp',
        'slug' => 'acme-corp',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['domain']);
});

test('creating tenant fails with duplicate slug', function () {
    Tenant::factory()->create(['slug' => 'acme-corp']);

    $response = $this->postJson('/api/v1/tenants', [
        'name' => 'Another Acme',
        'slug' => 'acme-corp',
        'domain' => 'another-acme.mahir.test',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

test('creating tenant fails with duplicate domain', function () {
    Tenant::factory()->create(['domain' => 'acme-corp.mahir.test']);

    $response = $this->postJson('/api/v1/tenants', [
        'name' => 'Another Acme',
        'slug' => 'another-acme',
        'domain' => 'acme-corp.mahir.test',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['domain']);
});

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

test('can show a tenant', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->getJson("/api/v1/tenants/{$tenant->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $tenant->id)
        ->assertJsonPath('data.name', $tenant->name);
});

test('showing a non-existent tenant returns 404', function () {
    $response = $this->getJson('/api/v1/tenants/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

test('can update a tenant name', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->putJson("/api/v1/tenants/{$tenant->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Tenant updated successfully.')
        ->assertJsonPath('data.name', 'Updated Name');
});

test('can update a tenant domain', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->putJson("/api/v1/tenants/{$tenant->id}", [
        'domain' => 'new-domain.mahir.test',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.domain', 'new-domain.mahir.test');
});

test('can deactivate a tenant', function () {
    $tenant = Tenant::factory()->active()->create();

    $response = $this->putJson("/api/v1/tenants/{$tenant->id}", [
        'is_active' => false,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.is_active', false);
});

test('updating a non-existent tenant returns 404', function () {
    $response = $this->putJson('/api/v1/tenants/99999', [
        'name' => 'Updated',
    ]);

    $response->assertNotFound();
});

test('updating tenant fails with duplicate domain', function () {
    Tenant::factory()->create(['domain' => 'existing.mahir.test']);
    $tenant = Tenant::factory()->create();

    $response = $this->putJson("/api/v1/tenants/{$tenant->id}", [
        'domain' => 'existing.mahir.test',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['domain']);
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete a tenant', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->deleteJson("/api/v1/tenants/{$tenant->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Tenant deleted successfully.');

    $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
});

test('deleting a non-existent tenant returns 404', function () {
    $response = $this->deleteJson('/api/v1/tenants/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot access tenants', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/tenants');

    $response->assertUnauthorized();
});
