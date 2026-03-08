<?php

namespace Database\Seeders\Tenant;

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Models\PortfolioCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortfolioSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a tenant database with portfolio categories, items, and media.
     *
     * Must be run within a tenant context via:
     *   php artisan tenants:artisan "db:seed --class=Database\\Seeders\\Tenant\\PortfolioSeeder"
     */
    public function run(): void
    {
        $originalConnection = DB::getDefaultConnection();
        DB::setDefaultConnection('tenant');

        try {
            $this->seedPortfolios();
        } finally {
            DB::setDefaultConnection($originalConnection);
        }
    }

    /**
     * Seed portfolio categories, items, and media.
     */
    private function seedPortfolios(): void
    {
        $users = User::query()->take(4)->get();

        if ($users->isEmpty()) {
            return;
        }

        $webCategory = PortfolioCategory::factory()->create([
            'user_id' => $users->first()->id,
            'name' => 'Web Development',
            'slug' => 'web-development',
            'description' => 'Full-stack web application projects.',
            'sort_order' => 0,
        ]);

        $mobileCategory = PortfolioCategory::factory()->create([
            'user_id' => $users->first()->id,
            'name' => 'Mobile Apps',
            'slug' => 'mobile-apps',
            'description' => 'Native and cross-platform mobile applications.',
            'sort_order' => 1,
        ]);

        $designCategory = PortfolioCategory::factory()->create([
            'user_id' => $users->first()->id,
            'name' => 'Design',
            'slug' => 'design',
            'description' => 'UI/UX design and branding projects.',
            'sort_order' => 2,
        ]);

        // Published portfolios with client info and technologies
        $publishedPortfolios = collect();

        $publishedPortfolios->push(Portfolio::factory()
            ->published()
            ->withClient()
            ->withTechnologies(['Laravel', 'React', 'Tailwind CSS'])
            ->withProjectUrl()
            ->withDateRange()
            ->inCategory($webCategory)
            ->create([
                'user_id' => $users->first()->id,
                'title' => 'E-Commerce Platform',
                'slug' => 'e-commerce-platform',
                'sort_order' => 0,
            ]));

        $publishedPortfolios->push(Portfolio::factory()
            ->published()
            ->withClient()
            ->withTechnologies(['React Native', 'TypeScript', 'PostgreSQL'])
            ->withProjectUrl()
            ->withDateRange()
            ->inCategory($mobileCategory)
            ->create([
                'user_id' => $users->first()->id,
                'title' => 'Fitness Tracker App',
                'slug' => 'fitness-tracker-app',
                'sort_order' => 1,
            ]));

        $publishedPortfolios->push(Portfolio::factory()
            ->published()
            ->withTechnologies(['Vue', 'Inertia', 'Laravel'])
            ->withDateRange()
            ->inCategory($webCategory)
            ->create([
                'user_id' => $users->count() > 1 ? $users[1]->id : $users->first()->id,
                'title' => 'Project Management Tool',
                'slug' => 'project-management-tool',
                'sort_order' => 2,
            ]));

        // Draft portfolios
        Portfolio::factory(2)->draft()->withTechnologies()->create([
            'user_id' => $users->count() > 1 ? $users[1]->id : $users->first()->id,
        ]);

        // Archived portfolio
        Portfolio::factory()->archived()->withClient()->withTechnologies()->create([
            'user_id' => $users->first()->id,
        ]);

        // Add media to published portfolios using Spatie Media Library
        $captions = [
            'Homepage design overview',
            'Dashboard analytics view',
            'Mobile responsive layout',
            'User settings panel',
            'Authentication flow',
        ];

        foreach ($publishedPortfolios as $portfolio) {
            $imageCount = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $imageCount; $i++) {
                $portfolio
                    ->addMediaFromUrl('https://picsum.photos/800/600')
                    ->withCustomProperties([
                        'sort_order' => $i,
                        'caption' => fake()->boolean(40) ? fake()->randomElement($captions) : null,
                    ])
                    ->toMediaCollection('gallery');
            }

            // Add a featured image
            $portfolio
                ->addMediaFromUrl('https://picsum.photos/1200/600')
                ->toMediaCollection('featured');
        }
    }
}
