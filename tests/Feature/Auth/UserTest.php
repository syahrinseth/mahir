<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;

beforeEach(function () {
    $this->withoutMiddleware(IdentifyTenant::class);
});

test('authenticated user can retrieve their profile', function () {
    $user = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/user');

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', 'Jane Doe')
        ->assertJsonPath('data.email', 'jane@example.com');
});

test('user profile does not expose sensitive fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/user');

    $response->assertSuccessful();

    $data = $response->json('data');

    expect($data)->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});

test('unauthenticated user cannot retrieve profile', function () {
    $response = $this->getJson('/api/v1/auth/user');

    $response->assertUnauthorized();
});
