<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;

beforeEach(function () {
    $this->withoutMiddleware(IdentifyTenant::class);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'iPhone',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ])
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', $user->email);
});

test('user receives a sanctum token after login', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $token = $response->json('data.token');

    expect($token)->toBeString()->not->toBeEmpty();
});

test('login creates a personal access token in the database', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Browser',
    ]);

    expect($user->tokens()->count())->toBe(1);
    expect($user->tokens()->first()->name)->toBe('Browser');
});

test('login uses default device name when not provided', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect($user->tokens()->first()->name)->toBe('api');
});

test('login fails with wrong password', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('message', 'Invalid credentials or account is inactive.');
});

test('login fails with non-existent email', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'nobody@example.com',
        'password' => 'password',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('message', 'Invalid credentials or account is inactive.');
});

test('inactive user cannot login', function () {
    $user = User::factory()->inactive()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('message', 'Invalid credentials or account is inactive.');
});

test('login fails without email', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'password' => 'password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('login fails without password', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('login fails with invalid email format', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'not-an-email',
        'password' => 'password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('login fails with empty request body', function () {
    $response = $this->postJson('/api/v1/auth/login', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

test('login does not expose password in response', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSuccessful();

    $userData = $response->json('data.user');

    expect($userData)->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});

test('multiple logins create separate tokens', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'iPhone',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Browser',
    ]);

    expect($user->tokens()->count())->toBe(2);
});
