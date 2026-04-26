<?php

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Filament\Resources\Testimonials\Pages\CreateTestimonial;
use App\Modules\Portfolio\Filament\Resources\Testimonials\Pages\EditTestimonial;
use App\Modules\Portfolio\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Modules\Portfolio\Models\Testimonial;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $user = User::factory()->create(['is_active' => true]);
    $this->user = $user;
    $this->actingAs($user, 'tenant');

    Filament::setCurrentPanel(
        Filament::getPanel('tenant'),
    );
});

/*
|--------------------------------------------------------------------------
| List Page
|--------------------------------------------------------------------------
*/

test('can load the list testimonials page', function () {
    Livewire::test(ListTestimonials::class)
        ->assertOk();
});

test('lists testimonials', function () {
    $testimonials = Testimonial::factory()->count(3)->create();

    Livewire::test(ListTestimonials::class)
        ->assertCanSeeTableRecords($testimonials);
});

/*
|--------------------------------------------------------------------------
| Create Page
|--------------------------------------------------------------------------
*/

test('can load the create testimonial page', function () {
    Livewire::test(CreateTestimonial::class)
        ->assertOk();
});

test('validates required fields on create', function (array $data, array $errors) {
    Livewire::test(CreateTestimonial::class)
        ->fillForm([
            'client_name' => 'Jane Doe',
            'content' => 'Great work!',
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`client_name` is required' => [['client_name' => null], ['client_name' => 'required']],
    '`content` is required' => [['content' => null], ['content' => 'required']],
]);

test('can create a testimonial', function () {
    Livewire::test(CreateTestimonial::class)
        ->fillForm([
            'client_name' => 'Jane Doe',
            'content' => 'Excellent project delivery!',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Testimonial::class, [
        'client_name' => 'Jane Doe',
        'content' => 'Excellent project delivery!',
    ]);
});

/*
|--------------------------------------------------------------------------
| Edit Page
|--------------------------------------------------------------------------
*/

test('can load the edit testimonial page', function () {
    $testimonial = Testimonial::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])
        ->assertOk();
});

test('can update a testimonial client name', function () {
    $testimonial = Testimonial::factory()->create(['client_name' => 'Old Name', 'user_id' => $this->user->id]);

    Livewire::test(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])
        ->fillForm(['client_name' => 'New Name'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Testimonial::class, [
        'id' => $testimonial->id,
        'client_name' => 'New Name',
    ]);
});
