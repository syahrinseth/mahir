<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\AuthResponseDTO;
use App\Modules\Auth\DTOs\RegisterUserDTO;
use App\Modules\Auth\Services\AuthService;
use App\Shared\Contracts\ActionContract;

class RegisterUserAction implements ActionContract
{
    public function __construct(
        private AuthService $authService,
    ) {}

    /**
     * Register a new tenant user and return an AuthResponseDTO.
     *
     * @param  array{name: string, email: string, password: string, device_name?: string}  $data
     */
    public function execute(array $data = []): AuthResponseDTO
    {
        $dto = RegisterUserDTO::fromArray($data);

        $user = $this->authService->registerUser($dto);

        $token = $user->createToken($dto->deviceName)->plainTextToken;

        return new AuthResponseDTO(user: $user, token: $token);
    }
}
