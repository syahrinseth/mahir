<?php

namespace App\Modules\Auth\DTOs;

use App\Modules\Auth\Models\User;

class AuthResponseDTO
{
    public function __construct(
        public readonly User $user,
        public readonly string $token,
    ) {}

    /**
     * @return array{user: User, token: string}
     */
    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'token' => $this->token,
        ];
    }
}
