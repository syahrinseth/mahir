<?php

use App\Modules\Auth\Actions\RegisterUserAction;
use App\Modules\Auth\DTOs\AuthResponseDTO;
use App\Modules\Auth\Models\User;

test('execute creates a user and returns AuthResponseDTO', function () {
    $action = app(RegisterUserAction::class);

    $result = $action->execute([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'SecurePass123',
        'device_name' => 'iPhone',
    ]);

    expect($result)->toBeInstanceOf(AuthResponseDTO::class)
        ->and($result->user)->toBeInstanceOf(User::class)
        ->and($result->user->name)->toBe('John Doe')
        ->and($result->user->email)->toBe('john@example.com')
        ->and($result->token)->toBeString()->not->toBeEmpty();
});

test('execute persists the user to database', function () {
    $action = app(RegisterUserAction::class);

    $action->execute([
        'name' => 'Persisted User',
        'email' => 'persisted@example.com',
        'password' => 'SecurePass123',
    ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Persisted User',
        'email' => 'persisted@example.com',
    ]);
});

test('execute creates a sanctum token for the user', function () {
    $action = app(RegisterUserAction::class);

    $result = $action->execute([
        'name' => 'Token User',
        'email' => 'token@example.com',
        'password' => 'SecurePass123',
        'device_name' => 'Browser',
    ]);

    expect($result->user->tokens()->count())->toBe(1);
    expect($result->user->tokens()->first()->name)->toBe('Browser');
});

test('execute uses default device name when not provided', function () {
    $action = app(RegisterUserAction::class);

    $result = $action->execute([
        'name' => 'Default Device',
        'email' => 'default@example.com',
        'password' => 'SecurePass123',
    ]);

    expect($result->user->tokens()->first()->name)->toBe('api');
});
