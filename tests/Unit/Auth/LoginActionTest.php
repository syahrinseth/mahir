<?php

use App\Modules\Auth\Actions\LoginAction;
use App\Modules\Auth\DTOs\AuthResponseDTO;
use App\Modules\Auth\Models\User;

test('execute returns AuthResponseDTO with valid credentials', function () {
    $action = app(LoginAction::class);

    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $result = $action->execute([
        'email' => 'john@example.com',
        'password' => 'password',
        'device_name' => 'iPhone',
    ]);

    expect($result)->not->toBeNull()
        ->toBeInstanceOf(AuthResponseDTO::class)
        ->and($result->user->id)->toBe($user->id)
        ->and($result->token)->toBeString()->not->toBeEmpty();
});

test('execute returns null with invalid credentials', function () {
    $action = app(LoginAction::class);

    User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $result = $action->execute([
        'email' => 'john@example.com',
        'password' => 'wrong-password',
    ]);

    expect($result)->toBeNull();
});

test('execute returns null for inactive user', function () {
    $action = app(LoginAction::class);

    User::factory()->inactive()->create([
        'email' => 'inactive@example.com',
    ]);

    $result = $action->execute([
        'email' => 'inactive@example.com',
        'password' => 'password',
    ]);

    expect($result)->toBeNull();
});

test('execute creates a token with provided device name', function () {
    $action = app(LoginAction::class);

    $user = User::factory()->create();

    $action->execute([
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Tablet',
    ]);

    expect($user->tokens()->first()->name)->toBe('Tablet');
});

test('execute returns null for non-existent user', function () {
    $action = app(LoginAction::class);

    $result = $action->execute([
        'email' => 'ghost@example.com',
        'password' => 'password',
    ]);

    expect($result)->toBeNull();
});
