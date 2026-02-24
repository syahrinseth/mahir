<?php

namespace Database\Seeders;

use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\Landlord\LandlordSeeder;
use Database\Seeders\Tenant\TenantSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the landlord database, then seed each tenant's database.
     */
    public function run(): void
    {
        $this->call(LandlordSeeder::class);

        $createTenantAction = app()->make(\App\Modules\Tenancy\Actions\CreateTenantAction::class);
        $createTenantAction->execute([
            'name' => 'Test',
            'slug' => 'test',
            'domain' => 'test.' . config('multitenancy.base_domain'),
        ]);

        Tenant::query()->where('is_active', true)->each(function (Tenant $tenant): void {
            $this->command->info("Seeding tenant: {$tenant->name} ({$tenant->domain})");

            $tenant->makeCurrent();

            $this->call(TenantSeeder::class);
        });

        Tenant::forgetCurrent();
    }
}
