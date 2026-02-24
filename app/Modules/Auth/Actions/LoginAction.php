<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\AuthResponseDTO;
use App\Modules\Auth\DTOs\LoginCredentialsDTO;
use App\Modules\Auth\Services\AuthService;
use App\Shared\Contracts\ActionContract;

class LoginAction implements ActionContract
{
    public function __construct(
        private AuthService $authService,
    ) {}

    /**
     * Attempt login and return an AuthResponseDTO or null.
     *
     * @param  array{email: string, password: string, device_name?: string}  $data
     */
    public function execute(array $data = []): ?AuthResponseDTO
    {
        $credentials = LoginCredentialsDTO::fromArray($data);

        return $this->authService->attemptLogin($credentials);
    }
}
