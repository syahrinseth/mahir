<?php

use App\Modules\Auth\Models\AdminUser;
use App\Modules\Subscription\Filament\Resources\Subscriptions\Pages\CreateSubscription;
use App\Modules\Subscription\Filament\Resources\Subscriptions\Pages\EditSubscription;
use App\Modules\Subscription\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Modules\Subscription\Models\Subscription;
use App\Modules\Tenancy\Models\Tenant;
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

test('can load the list subscriptions page', function () {
    Livewire::test(ListSubscriptions::class)
        ->assertOk();
});

test('lists subscriptions', function () {
    $tenant = Tenant::factory()->create();
    $subscription = Subscription::factory()->create(['tenant_id' => $tenant->id]);

    Livewire::test(ListSubscriptions::class)
        ->assertCanSeeTableRecords([$subscription]);
});

/*
|--------------------------------------------------------------------------
| Create Page
|--------------------------------------------------------------------------
*/

test('can load the create subscription page', function () {
    Livewire::test(CreateSubscription::class)
        ->assertOk();
});

test('validates required fields on create', function (array $data, array $errors) {
    $tenant = Tenant::factory()->create();

    Livewire::test(CreateSubscription::class)
        ->fillForm(['tenant_id' => $tenant->id, ...$data])
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`plan` is required' => [['plan' => null], ['plan' => 'required']],
    '`status` is required' => [['status' => null], ['status' => 'required']],
]);

/*
|--------------------------------------------------------------------------
| Edit Page
|--------------------------------------------------------------------------
*/

test('can load the edit subscription page', function () {
    $tenant = Tenant::factory()->create();
    $subscription = Subscription::factory()->create(['tenant_id' => $tenant->id]);

    Livewire::test(EditSubscription::class, ['record' => $subscription->getRouteKey()])
        ->assertOk();
});

test('can update a subscription', function () {
    $tenant = Tenant::factory()->create();
    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan' => 'basic',
    ]);

    Livewire::test(EditSubscription::class, ['record' => $subscription->getRouteKey()])
        ->fillForm(['plan' => 'pro'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Subscription::class, [
        'id' => $subscription->id,
        'plan' => 'pro',
    ]);
});
