<?php

namespace Database\Seeders\Tenant;

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Models\Testimonial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestimonialSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a tenant database with testimonials.
     *
     * Must be run within a tenant context via:
     *   php artisan tenants:artisan "db:seed --class=Database\\Seeders\\Tenant\\TestimonialSeeder"
     */
    public function run(): void
    {
        $originalConnection = DB::getDefaultConnection();
        DB::setDefaultConnection('tenant');

        try {
            $this->seedTestimonials();
        } finally {
            DB::setDefaultConnection($originalConnection);
        }
    }

    /**
     * Seed testimonials with a mix of published, draft, and featured items.
     */
    private function seedTestimonials(): void
    {
        $users = User::query()->take(4)->get();

        if ($users->isEmpty()) {
            return;
        }

        $portfolios = Portfolio::query()->take(3)->get();

        // Published testimonials with full client details
        Testimonial::factory()
            ->published()
            ->featured()
            ->withRating()
            ->withClientDetails()
            ->create([
                'user_id' => $users->first()->id,
                'portfolio_id' => $portfolios->first()?->id,
                'client_name' => 'Sarah Johnson',
                'client_position' => 'CEO',
                'client_company' => 'TechStart Inc.',
                'content' => 'Outstanding work on our e-commerce platform. The team delivered a beautiful, performant application that exceeded our expectations.',
                'sort_order' => 0,
            ]);

        Testimonial::factory()
            ->published()
            ->featured()
            ->withRating()
            ->withClientDetails()
            ->create([
                'user_id' => $users->first()->id,
                'portfolio_id' => $portfolios->count() > 1 ? $portfolios[1]->id : null,
                'client_name' => 'Michael Chen',
                'client_position' => 'CTO',
                'client_company' => 'DataFlow Systems',
                'content' => 'Exceptional technical expertise and great communication throughout the project. Would highly recommend for any complex web application.',
                'sort_order' => 1,
            ]);

        Testimonial::factory()
            ->published()
            ->withRating()
            ->create([
                'user_id' => $users->count() > 1 ? $users[1]->id : $users->first()->id,
                'client_name' => 'Emily Rodriguez',
                'client_position' => 'Marketing Director',
                'client_company' => 'Creative Labs',
                'content' => 'The redesign of our website significantly improved our conversion rates. Professional, timely, and creative solutions.',
                'sort_order' => 2,
            ]);

        // Standalone testimonials (no portfolio)
        Testimonial::factory()
            ->published()
            ->withClientDetails()
            ->create([
                'user_id' => $users->first()->id,
                'client_name' => 'James Park',
                'content' => 'Great experience working together on our mobile app. The attention to detail was impressive.',
                'sort_order' => 3,
            ]);

        // Draft testimonials
        Testimonial::factory(2)->draft()->withClientDetails()->create([
            'user_id' => $users->count() > 1 ? $users[1]->id : $users->first()->id,
        ]);

        // Add featured media (headshots) to published featured testimonials
        $featuredTestimonials = Testimonial::query()
            ->where('is_featured', true)
            ->whereNotNull('published_at')
            ->get();

        foreach ($featuredTestimonials as $testimonial) {
            $testimonial
                ->addMediaFromUrl('https://picsum.photos/300/300')
                ->toMediaCollection('featured');
        }
    }
}
