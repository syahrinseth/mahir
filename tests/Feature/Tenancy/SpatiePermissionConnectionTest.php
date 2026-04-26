<?php

use App\Modules\Auth\Enums\Role;
use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Role as RoleModel;
use App\Modules\Auth\Models\User;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Tasks\ResetPermissionsTask;

test('Permission model uses the tenant connection', function () {
    expect((new Permission)->getConnectionName())->toBe('tenant');
});

test('Role model uses the tenant connection', function () {
    expect((new RoleModel)->getConnectionName())->toBe('tenant');
});

test('config registers the custom tenant-scoped Permission model', function () {
    expect(config('permission.models.permission'))->toBe(Permission::class);
});

test('config registers the custom tenant-scoped Role model', function () {
    expect(config('permission.models.role'))->toBe(RoleModel::class);
});

test('switch tenant tasks include ResetPermissionsTask', function () {
    $tasks = config('multitenancy.switch_tenant_tasks');

    expect($tasks)->toContain(ResetPermissionsTask::class);
});

test('ResetPermissionsTask makeCurrent calls forgetCachedPermissions without error', function () {
    $tenant = Tenant::factory()->create(['slug' => 'cache-test']);
    $task = app(ResetPermissionsTask::class);

    // Should not throw; verifies the task integrates cleanly with the registrar
    expect(fn () => $task->makeCurrent($tenant))->not->toThrow(Throwable::class);
});

test('ResetPermissionsTask forgetCurrent calls forgetCachedPermissions without error', function () {
    $task = app(ResetPermissionsTask::class);

    expect(fn () => $task->forgetCurrent())->not->toThrow(Throwable::class);
});

test('tenant request does not throw permissions table not found error', function () {
    Tenant::factory()->create(['slug' => 'perm-test']);

    $response = $this->get('http://perm-test.mahir.test/');

    $response->assertSuccessful();
});

test('user can be assigned a role on the tenant connection', function () {
    RoleModel::create(['name' => Role::Admin->value, 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole(Role::Admin->value);

    expect($user->hasRole(Role::Admin->value))->toBeTrue();
});
