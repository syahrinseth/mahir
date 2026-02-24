<?php

namespace Database\Seeders\Landlord;

use App\Modules\Auth\Models\AdminUser;
use App\Modules\Subscription\Enums\PlanType;
use App\Modules\Subscription\Models\Subscription;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LandlordSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the landlord (central) database with admin users, tenants, and subscriptions.
     */
    public function run(): void
    {
        $this->seedAdminUsers();
    }

    /**
     * Seed admin users for the Filament panel.
     */
    private function seedAdminUsers(): void
    {
        AdminUser::factory()->verified()->create([
            'name' => 'Super Admin',
            'email' => 'admin@mahir.test',
        ]);

        AdminUser::factory()->verified()->create([
            'name' => 'Support Admin',
            'email' => 'support@mahir.test',
        ]);

        AdminUser::factory()->unverified()->create();
    }
}
