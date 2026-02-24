<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\DTOs\AuthResponseDTO;
use App\Modules\Auth\DTOs\LoginCredentialsDTO;
use App\Modules\Auth\DTOs\RegisterUserDTO;
use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Attempt to authenticate a tenant user and return an API token.
     */
    public function attemptLogin(LoginCredentialsDTO $credentials): ?AuthResponseDTO
    {
        $user = User::query()
            ->where('email', $credentials->email)
            ->first();

        if (! $user || ! Hash::check($credentials->password, $user->password)) {
            return null;
        }

        if (! $user->is_active) {
            return null;
        }

        $token = $user->createToken($credentials->deviceName)->plainTextToken;

        return new AuthResponseDTO(user: $user, token: $token);
    }

    /**
     * Register a new tenant user.
     */
    public function registerUser(RegisterUserDTO $dto): User
    {
        return User::query()->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'is_active' => true,
        ]);
    }

    /**
     * Revoke the current access token for the user.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Revoke all tokens for the user.
     */
    public function logoutAllDevices(User $user): void
    {
        $user->tokens()->delete();
    }
}
