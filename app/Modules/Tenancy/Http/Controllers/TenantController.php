<?php

namespace App\Modules\Tenancy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenancy\Actions\CreateTenantAction;
use App\Modules\Tenancy\DTOs\UpdateTenantDTO;
use App\Modules\Tenancy\Http\Requests\CreateTenantRequest;
use App\Modules\Tenancy\Http\Requests\UpdateTenantRequest;
use App\Modules\Tenancy\Services\TenantService;
use App\Shared\Exceptions\TenantNotFoundException;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Tenants', weight: 1)]
class TenantController extends Controller
{
    public function __construct(
        private TenantService $tenantService,
    ) {}

    /**
     * List all tenants.
     *
     * Retrieves a list of all registered tenants. This is a landlord-scoped
     * endpoint that requires authentication.
     */
    public function index(): JsonResponse
    {
        $tenants = $this->tenantService->listAllTenants();

        /**
         * List of all tenants.
         *
         * @body array{data: array<int, array{id: int, name: string, slug: string, domain: string, database: string, is_active: bool, created_at: string, updated_at: string}>}
         */
        return response()->json([
            'data' => $tenants,
        ]);
    }

    /**
     * Create a new tenant.
     *
     * Creates a new tenant record in the landlord database. A corresponding
     * tenant database will be provisioned automatically.
     */
    #[Response(422, description: 'Validation error.', type: 'array{message: string, errors: array<string, string[]>}')]
    public function store(CreateTenantRequest $request, CreateTenantAction $action): JsonResponse
    {
        $tenant = $action->execute($request->validated());

        /**
         * Tenant created successfully.
         *
         * @status 201
         *
         * @body array{message: string, data: array{id: int, name: string, slug: string, domain: string, database: string, is_active: bool, created_at: string, updated_at: string}}
         */
        return response()->json([
            'message' => 'Tenant created successfully.',
            'data' => $tenant,
        ], 201);
    }

    /**
     * Get a tenant.
     *
     * Retrieves a single tenant by their ID.
     *
     * @param  int  $tenant  The tenant ID.
     */
    #[PathParameter('tenant', description: 'The tenant ID.', type: 'int', example: 1)]
    public function show(int $tenant): JsonResponse
    {
        try {
            $tenantModel = $this->tenantService->getTenantById($tenant);
        } catch (TenantNotFoundException) {
            abort(404, 'Tenant not found.');
        }

        /**
         * Tenant details.
         *
         * @body array{data: array{id: int, name: string, slug: string, domain: string, database: string, is_active: bool, created_at: string, updated_at: string}}
         */
        return response()->json([
            'data' => $tenantModel,
        ]);
    }

    /**
     * Update a tenant.
     *
     * Updates an existing tenant's information. Only the provided fields
     * will be updated; omitted fields remain unchanged.
     *
     * @param  int  $tenant  The tenant ID.
     */
    #[PathParameter('tenant', description: 'The tenant ID.', type: 'int', example: 1)]
    #[Response(422, description: 'Validation error.', type: 'array{message: string, errors: array<string, string[]>}')]
    public function update(UpdateTenantRequest $request, int $tenant): JsonResponse
    {
        try {
            $dto = UpdateTenantDTO::fromArray($request->validated());
            $tenantModel = $this->tenantService->updateTenant($tenant, $dto);
        } catch (TenantNotFoundException) {
            abort(404, 'Tenant not found.');
        }

        /**
         * Tenant updated successfully.
         *
         * @body array{message: string, data: array{id: int, name: string, slug: string, domain: string, database: string, is_active: bool, created_at: string, updated_at: string}}
         */
        return response()->json([
            'message' => 'Tenant updated successfully.',
            'data' => $tenantModel,
        ]);
    }

    /**
     * Delete a tenant.
     *
     * Permanently deletes a tenant and optionally drops their database.
     * This action is irreversible.
     *
     * @param  int  $tenant  The tenant ID.
     */
    #[PathParameter('tenant', description: 'The tenant ID.', type: 'int', example: 1)]
    #[QueryParameter('drop_database', description: 'Whether to drop the tenant database.', type: 'bool', default: false, example: false)]
    public function destroy(int $tenant, Request $request): JsonResponse
    {
        $dropDatabase = $request->boolean('drop_database', false);

        try {
            $this->tenantService->deleteTenant($tenant, $dropDatabase);
        } catch (TenantNotFoundException) {
            abort(404, 'Tenant not found.');
        }

        /**
         * Tenant deleted successfully.
         *
         * @body array{message: string}
         */
        return response()->json([
            'message' => 'Tenant deleted successfully.',
        ]);
    }
}
