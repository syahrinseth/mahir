<?php

use App\Modules\Auth\DTOs\LoginCredentialsDTO;
use App\Modules\Auth\DTOs\RegisterUserDTO;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Support\Facades\Hash;

test('registerUser creates a new user with hashed password', function () {
    $service = app(AuthService::class);

    $dto = new RegisterUserDTO(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'SecurePass123',
    );

    $user = $service->registerUser($dto);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->name->toBe('John Doe')
        ->email->toBe('john@example.com')
        ->is_active->toBeTrue();

    expect(Hash::check('SecurePass123', $user->password))->toBeTrue();
});

test('registerUser sets user as active by default', function () {
    $service = app(AuthService::class);

    $dto = new RegisterUserDTO(
        name: 'Active User',
        email: 'active@example.com',
        password: 'SecurePass123',
    );

    $user = $service->registerUser($dto);

    expect($user->is_active)->toBeTrue();
});

test('registerUser persists user to database', function () {
    $service = app(AuthService::class);

    $dto = new RegisterUserDTO(
        name: 'Persisted User',
        email: 'persisted@example.com',
        password: 'SecurePass123',
    );

    $service->registerUser($dto);

    $this->assertDatabaseHas('users', [
        'name' => 'Persisted User',
        'email' => 'persisted@example.com',
    ]);
});

test('attemptLogin returns AuthResponseDTO with valid credentials', function () {
    $service = app(AuthService::class);

    User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $credentials = new LoginCredentialsDTO(
        email: 'john@example.com',
        password: 'password',
        deviceName: 'iPhone',
    );

    $result = $service->attemptLogin($credentials);

    expect($result)->not->toBeNull()
        ->and($result->user)->toBeInstanceOf(User::class)
        ->and($result->user->email)->toBe('john@example.com')
        ->and($result->token)->toBeString()->not->toBeEmpty();
});

test('attemptLogin returns null with wrong password', function () {
    $service = app(AuthService::class);

    User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $credentials = new LoginCredentialsDTO(
        email: 'john@example.com',
        password: 'wrong-password',
    );

    $result = $service->attemptLogin($credentials);

    expect($result)->toBeNull();
});

test('attemptLogin returns null for non-existent user', function () {
    $service = app(AuthService::class);

    $credentials = new LoginCredentialsDTO(
        email: 'nobody@example.com',
        password: 'password',
    );

    $result = $service->attemptLogin($credentials);

    expect($result)->toBeNull();
});

test('attemptLogin returns null for inactive user', function () {
    $service = app(AuthService::class);

    User::factory()->inactive()->create([
        'email' => 'inactive@example.com',
    ]);

    $credentials = new LoginCredentialsDTO(
        email: 'inactive@example.com',
        password: 'password',
    );

    $result = $service->attemptLogin($credentials);

    expect($result)->toBeNull();
});

test('attemptLogin uses default device name when not provided', function () {
    $service = app(AuthService::class);

    $user = User::factory()->create();

    $credentials = new LoginCredentialsDTO(
        email: $user->email,
        password: 'password',
    );

    $result = $service->attemptLogin($credentials);

    expect($result)->not->toBeNull();
    expect($user->tokens()->first()->name)->toBe('api');
});

test('logout revokes the current access token', function () {
    $service = app(AuthService::class);

    $user = User::factory()->create();
    $token = $user->createToken('iPhone');

    $user->withAccessToken($token->accessToken);

    $service->logout($user);

    expect($user->tokens()->count())->toBe(0);
});

test('logoutAllDevices revokes all tokens', function () {
    $service = app(AuthService::class);

    $user = User::factory()->create();
    $user->createToken('iPhone');
    $user->createToken('Browser');
    $user->createToken('Tablet');

    expect($user->tokens()->count())->toBe(3);

    $service->logoutAllDevices($user);

    expect($user->tokens()->count())->toBe(0);
});
