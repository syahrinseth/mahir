<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Actions\LoginAction;
use App\Modules\Auth\Actions\RegisterUserAction;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\AuthService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Auth', weight: 0)]
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
    ) {}

    /**
     * Register a new user.
     *
     * Creates a new user account within the current tenant and returns
     * an authentication token for immediate access.
     *
     * @unauthenticated
     */
    #[Response(422, description: 'Validation error.', type: 'array{message: string, errors: array<string, string[]>}')]
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        /**
         * Registration successful.
         *
         * @status 201
         *
         * @body array{message: string, data: array{user: array{id: int, name: string, email: string, is_active: bool, created_at: string, updated_at: string}, token: string}}
         */
        return response()->json([
            'message' => 'Registration successful.',
            'data' => [
                'user' => $result->user,
                'token' => $result->token,
            ],
        ], 201);
    }

    /**
     * Log in a user.
     *
     * Authenticates a user with email and password credentials and returns
     * a Sanctum Bearer token for subsequent API requests.
     *
     * @unauthenticated
     */
    #[Response(422, description: 'Validation error.', type: 'array{message: string, errors: array<string, string[]>}')]
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        if (! $result) {
            // Invalid credentials or account is inactive.
            abort(401, 'Invalid credentials or account is inactive.');
        }

        /**
         * Login successful.
         *
         * @body array{message: string, data: array{user: array{id: int, name: string, email: string, is_active: bool, created_at: string, updated_at: string}, token: string}}
         */
        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'user' => $result->user,
                'token' => $result->token,
            ],
        ]);
    }

    /**
     * Log out the current user.
     *
     * Revokes all Sanctum tokens for the authenticated user, effectively
     * logging them out of all devices.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->authService->logout($user);

        /**
         * Logged out successfully.
         *
         * @body array{message: string}
         */
        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get the authenticated user.
     *
     * Returns the currently authenticated user's profile information.
     */
    public function user(Request $request): JsonResponse
    {
        /**
         * Authenticated user details.
         *
         * @body array{data: array{id: int, name: string, email: string, is_active: bool, created_at: string, updated_at: string}}
         */
        return response()->json([
            'data' => $request->user(),
        ]);
    }
}
