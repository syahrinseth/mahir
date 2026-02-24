<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;

beforeEach(function () {
    $this->withoutMiddleware(IdentifyTenant::class);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/auth/logout');

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Logged out successfully.');
});

test('logout revokes the current access token', function () {
    $user = User::factory()->create();
    $user->createToken('iPhone');
    $user->createToken('Browser');

    expect($user->tokens()->count())->toBe(2);

    // Login to get a specific token, then logout
    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Tablet',
    ]);

    $token = $loginResponse->json('data.token');

    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('/api/v1/auth/logout');

    // Only the Tablet token should be revoked, iPhone and Browser remain
    expect($user->tokens()->count())->toBe(2);
});

test('unauthenticated user cannot logout', function () {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertUnauthorized();
});
