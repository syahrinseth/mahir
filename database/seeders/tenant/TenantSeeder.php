<?php

namespace Database\Seeders\Tenant;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a tenant database with users.
     *
     * This seeder should be run within a tenant context
     * (after calling $tenant->makeCurrent()).
     */
    public function run(): void
    {
        $this->seedUsers();
    }

    /**
     * Seed users with a mix of active/inactive and verified/unverified states.
     */
    private function seedUsers(): void
    {
        User::factory()->active()->verified()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory(3)->active()->verified()->create();

        User::factory(2)->active()->unverified()->create();

        User::factory()->inactive()->verified()->create();

        User::factory()->inactive()->unverified()->create();
    }
}
