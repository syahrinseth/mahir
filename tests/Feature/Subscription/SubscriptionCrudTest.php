<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;
use App\Modules\Subscription\Enums\PlanType;
use App\Modules\Subscription\Enums\SubscriptionStatus;
use App\Modules\Subscription\Models\Subscription;
use App\Modules\Tenancy\Models\Tenant;

beforeEach(function () {
    $this->withoutMiddleware(IdentifyTenant::class);
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
});

/*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
*/

test('can list all subscriptions', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->count(3)->for($tenant)->create();

    $response = $this->getJson('/api/v1/subscriptions');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('listing subscriptions returns empty array when none exist', function () {
    $response = $this->getJson('/api/v1/subscriptions');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

test('can create a subscription', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->postJson('/api/v1/subscriptions', [
        'tenant_id' => $tenant->id,
        'plan' => PlanType::Pro->value,
        'status' => SubscriptionStatus::Active->value,
        'starts_at' => now()->toDateTimeString(),
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Subscription created successfully.')
        ->assertJsonPath('data.tenant_id', $tenant->id)
        ->assertJsonPath('data.plan', PlanType::Pro->value)
        ->assertJsonPath('data.status', SubscriptionStatus::Active->value);
});

test('can create a trial subscription', function () {
    $tenant = Tenant::factory()->create();
    $trialEndsAt = now()->addDays(14)->toDateTimeString();

    $response = $this->postJson('/api/v1/subscriptions', [
        'tenant_id' => $tenant->id,
        'plan' => PlanType::Basic->value,
        'status' => SubscriptionStatus::Trial->value,
        'trial_ends_at' => $trialEndsAt,
        'starts_at' => now()->toDateTimeString(),
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', SubscriptionStatus::Trial->value);
});

test('creating subscription fails without tenant_id', function () {
    $response = $this->postJson('/api/v1/subscriptions', [
        'plan' => PlanType::Pro->value,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['tenant_id']);
});

test('creating subscription fails without plan', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->postJson('/api/v1/subscriptions', [
        'tenant_id' => $tenant->id,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['plan']);
});

test('creating subscription fails with invalid plan', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->postJson('/api/v1/subscriptions', [
        'tenant_id' => $tenant->id,
        'plan' => 'nonexistent-plan',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['plan']);
});

test('creating subscription fails with invalid status', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->postJson('/api/v1/subscriptions', [
        'tenant_id' => $tenant->id,
        'plan' => PlanType::Pro->value,
        'status' => 'invalid-status',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('creating subscription fails with non-existent tenant', function () {
    $response = $this->postJson('/api/v1/subscriptions', [
        'tenant_id' => 99999,
        'plan' => PlanType::Pro->value,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['tenant_id']);
});

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

test('can show a subscription', function () {
    $subscription = Subscription::factory()->create();

    $response = $this->getJson("/api/v1/subscriptions/{$subscription->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $subscription->id);
});

test('showing a non-existent subscription returns 404', function () {
    $response = $this->getJson('/api/v1/subscriptions/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

test('can update a subscription plan', function () {
    $subscription = Subscription::factory()->withPlan(PlanType::Basic)->create();

    $response = $this->putJson("/api/v1/subscriptions/{$subscription->id}", [
        'plan' => PlanType::Enterprise->value,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Subscription updated successfully.')
        ->assertJsonPath('data.plan', PlanType::Enterprise->value);
});

test('can update a subscription status', function () {
    $subscription = Subscription::factory()->create();

    $response = $this->putJson("/api/v1/subscriptions/{$subscription->id}", [
        'status' => SubscriptionStatus::Cancelled->value,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.status', SubscriptionStatus::Cancelled->value);
});

test('updating a non-existent subscription returns 404', function () {
    $response = $this->putJson('/api/v1/subscriptions/99999', [
        'plan' => PlanType::Pro->value,
    ]);

    $response->assertNotFound();
});

test('updating subscription fails with invalid plan', function () {
    $subscription = Subscription::factory()->create();

    $response = $this->putJson("/api/v1/subscriptions/{$subscription->id}", [
        'plan' => 'invalid-plan',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['plan']);
});

/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

test('can delete a subscription', function () {
    $subscription = Subscription::factory()->create();

    $response = $this->deleteJson("/api/v1/subscriptions/{$subscription->id}");

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Subscription deleted successfully.');

    $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
});

test('deleting a non-existent subscription returns 404', function () {
    $response = $this->deleteJson('/api/v1/subscriptions/99999');

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot access subscriptions', function () {
    $this->app['auth']->forgetGuards();

    $response = $this->getJson('/api/v1/subscriptions');

    $response->assertUnauthorized();
});
