<?php

use App\Http\Middleware\IdentifyTenant;
use App\Modules\Auth\Models\User;

beforeEach(function () {
    $this->withoutMiddleware(IdentifyTenant::class);
});

test('user can register with valid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'device_name' => 'iPhone',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ])
        ->assertJsonPath('data.user.name', 'John Doe')
        ->assertJsonPath('data.user.email', 'john@example.com');

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'is_active' => true,
    ]);
});

test('user receives a sanctum token after registration', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'device_name' => 'Browser',
    ]);

    $response->assertCreated();

    $token = $response->json('data.token');

    expect($token)->toBeString()->not->toBeEmpty();
});

test('user is active by default after registration', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Active User',
        'email' => 'active@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $user = User::query()->where('email', 'active@example.com')->first();

    expect($user->is_active)->toBeTrue();
});

test('user password is hashed after registration', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Hashed User',
        'email' => 'hashed@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $user = User::query()->where('email', 'hashed@example.com')->first();

    expect($user->password)->not->toBe('Password123!');
});

test('registration uses default device name when not provided', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'No Device',
        'email' => 'nodevice@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertCreated();

    $token = $response->json('data.token');

    expect($token)->toBeString()->not->toBeEmpty();
});

test('registration fails without name', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email' => 'john@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('registration fails without email', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('registration fails with invalid email', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'not-an-email',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('registration fails without password', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('registration fails without password confirmation', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('registration fails when password confirmation does not match', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'DifferentPassword!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('registration fails with duplicate email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'New User',
        'email' => 'existing@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('registration fails with empty request body', function () {
    $response = $this->postJson('/api/v1/auth/register', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('registration does not expose password in response', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Secret User',
        'email' => 'secret@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertCreated();

    $userData = $response->json('data.user');

    expect($userData)->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});
