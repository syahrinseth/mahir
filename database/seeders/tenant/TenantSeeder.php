<?php

namespace Database\Seeders\Tenant;

use App\Modules\Auth\Enums\Permission;
use App\Modules\Auth\Enums\Role;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;

class TenantSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a tenant database with roles, permissions, and users.
     *
     * Must be run within a tenant context via:
     *   php artisan tenants:artisan "db:seed --class=Database\\Seeders\\Tenant\\TenantSeeder"
     */
    public function run(): void
    {
        $originalConnection = DB::getDefaultConnection();
        DB::setDefaultConnection('tenant');

        try {
            $this->seedRolesAndPermissions();
            $this->seedUsers();
        } finally {
            DB::setDefaultConnection($originalConnection);
        }
    }

    /**
     * Seed the default roles and permissions for the tenant.
     */
    private function seedRolesAndPermissions(): void
    {
        $registrar = app()[\Spatie\Permission\PermissionRegistrar::class];
        $registrar->forgetCachedPermissions();

        foreach (Permission::cases() as $permission) {
            PermissionModel::findOrCreate($permission->value, 'web');
        }

        $registrar->forgetCachedPermissions();

        $adminRole = RoleModel::findOrCreate(Role::Admin->value, 'web');
        $adminRole->syncPermissions(array_column(Permission::cases(), 'value'));

        $userRole = RoleModel::findOrCreate(Role::User->value, 'web');
        $userRole->syncPermissions([
            Permission::UserViewAny->value,
            Permission::UserView->value,
        ]);
    }

    /**
     * Seed users with a mix of active/inactive and verified/unverified states.
     */
    private function seedUsers(): void
    {
        $admin = User::factory()->active()->verified()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole(Role::Admin->value);

        $users = User::factory(3)->active()->verified()->create();
        $users->each(fn (User $user) => $user->assignRole(Role::User->value));

        User::factory(2)->active()->unverified()->create()
            ->each(fn (User $user) => $user->assignRole(Role::User->value));

        User::factory()->inactive()->verified()->create()
            ->assignRole(Role::User->value);

        User::factory()->inactive()->unverified()->create()
            ->assignRole(Role::User->value);
    }
}
