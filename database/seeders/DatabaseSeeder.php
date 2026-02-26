<?php

namespace Database\Seeders;

use Database\Seeders\Landlord\LandlordSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the landlord database.
     *
     * Usage:
     *   Landlord: php artisan db:seed
     *   Tenant:   php artisan tenant:artisan "db:seed --class=Database\\Seeders\\Tenant\\TenantSeeder"
     */
    public function run(): void
    {
        $this->call(LandlordSeeder::class);
    }
}
