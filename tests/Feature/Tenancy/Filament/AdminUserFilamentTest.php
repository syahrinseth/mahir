<?php

use App\Modules\Auth\Filament\Resources\AdminUsers\Pages\CreateAdminUser;
use App\Modules\Auth\Filament\Resources\AdminUsers\Pages\EditAdminUser;
use App\Modules\Auth\Filament\Resources\AdminUsers\Pages\ListAdminUsers;
use App\Modules\Auth\Models\AdminUser;
use Filament\Facades\Filament;
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
| List Page
|--------------------------------------------------------------------------
*/

test('can load the list admin users page', function () {
    Livewire::test(ListAdminUsers::class)
        ->assertOk();
});

test('lists admin users', function () {
    $users = AdminUser::factory()->count(3)->create();

    Livewire::test(ListAdminUsers::class)
        ->assertCanSeeTableRecords($users);
});

/*
|--------------------------------------------------------------------------
| Create Page
|--------------------------------------------------------------------------
*/

test('can load the create admin user page', function () {
    Livewire::test(CreateAdminUser::class)
        ->assertOk();
});

test('validates required fields on create', function (array $data, array $errors) {
    Livewire::test(CreateAdminUser::class)
        ->fillForm(['name' => 'Test User', 'email' => 'test@example.com', 'password' => 'password', ...$data])
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`email` is required' => [['email' => null], ['email' => 'required']],
    '`password` is required on create' => [['password' => null], ['password' => 'required']],
]);

test('can create an admin user', function () {
    Livewire::test(CreateAdminUser::class)
        ->fillForm([
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(AdminUser::class, [
        'name' => 'New Admin',
        'email' => 'newadmin@example.com',
    ]);
});

/*
|--------------------------------------------------------------------------
| Edit Page
|--------------------------------------------------------------------------
*/

test('can load the edit admin user page', function () {
    $user = AdminUser::factory()->create();

    Livewire::test(EditAdminUser::class, ['record' => $user->getRouteKey()])
        ->assertOk();
});

test('can update an admin user name', function () {
    $user = AdminUser::factory()->create(['name' => 'Old Name']);

    Livewire::test(EditAdminUser::class, ['record' => $user->getRouteKey()])
        ->fillForm(['name' => 'New Name'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(AdminUser::class, [
        'id' => $user->id,
        'name' => 'New Name',
    ]);
});
