<?php

use App\Modules\Auth\Enums\Permission;
use App\Modules\Auth\Enums\Role;
use App\Modules\Auth\Models\User;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    foreach (Permission::cases() as $permission) {
        PermissionModel::findOrCreate($permission->value, 'web');
    }

    $adminRole = RoleModel::findOrCreate(Role::Admin->value, 'web');
    $adminRole->syncPermissions(array_column(Permission::cases(), 'value'));

    $userRole = RoleModel::findOrCreate(Role::User->value, 'web');
    $userRole->syncPermissions([
        Permission::UserViewAny->value,
        Permission::UserView->value,
    ]);
});

// --- Admin role (before() grants all) ---

test('admin can view any users', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin->value);

    expect($admin->can('viewAny', User::class))->toBeTrue();
});

test('admin can view a specific user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin->value);

    $other = User::factory()->create();

    expect($admin->can('view', $other))->toBeTrue();
});

test('admin can create users', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin->value);

    expect($admin->can('create', User::class))->toBeTrue();
});

test('admin can update any user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin->value);

    $other = User::factory()->create();

    expect($admin->can('update', $other))->toBeTrue();
});

test('admin can delete another user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin->value);

    $other = User::factory()->create();

    expect($admin->can('delete', $other))->toBeTrue();
});

// --- User role (limited permissions) ---

test('user with user role can view any users', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::User->value);

    expect($user->can('viewAny', User::class))->toBeTrue();
});

test('user with user role can view a specific user', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::User->value);

    $other = User::factory()->create();

    expect($user->can('view', $other))->toBeTrue();
});

test('user with user role cannot create users', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::User->value);

    expect($user->can('create', User::class))->toBeFalse();
});

test('user with user role cannot update another user', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::User->value);

    $other = User::factory()->create();

    expect($user->can('update', $other))->toBeFalse();
});

test('user with user role cannot delete another user', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::User->value);

    $other = User::factory()->create();

    expect($user->can('delete', $other))->toBeFalse();
});

// --- Self-access rules ---

test('user can always view their own profile', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::User->value);

    expect($user->can('view', $user))->toBeTrue();
});

test('user can always update their own profile', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::User->value);

    expect($user->can('update', $user))->toBeTrue();
});

test('user cannot delete themselves', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin->value);

    expect($admin->can('delete', $admin))->toBeFalse();
});

// --- No role assigned ---

test('user without any role cannot view users', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', User::class))->toBeFalse();
});

test('user without any role cannot create users', function () {
    $user = User::factory()->create();

    expect($user->can('create', User::class))->toBeFalse();
});

test('user without any role can still view own profile', function () {
    $user = User::factory()->create();

    expect($user->can('view', $user))->toBeTrue();
});

test('user without any role can still update own profile', function () {
    $user = User::factory()->create();

    expect($user->can('update', $user))->toBeTrue();
});
