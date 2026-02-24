<?php

namespace App\Modules\Tenancy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenancy\Actions\CreateTenantAction;
use App\Modules\Tenancy\DTOs\UpdateTenantDTO;
use App\Modules\Tenancy\Http\Requests\CreateTenantRequest;
use App\Modules\Tenancy\Http\Requests\UpdateTenantRequest;
use App\Modules\Tenancy\Services\TenantService;
use App\Shared\Exceptions\TenantNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        private TenantService $tenantService,
    ) {}

    public function index(): JsonResponse
    {
        $tenants = $this->tenantService->listAllTenants();

        return response()->json([
            'data' => $tenants,
        ]);
    }

    public function store(CreateTenantRequest $request, CreateTenantAction $action): JsonResponse
    {
        $tenant = $action->execute($request->validated());

        return response()->json([
            'message' => 'Tenant created successfully.',
            'data' => $tenant,
        ], 201);
    }

    public function show(int $tenant): JsonResponse
    {
        try {
            $tenantModel = $this->tenantService->getTenantById($tenant);
        } catch (TenantNotFoundException) {
            return response()->json([
                'message' => 'Tenant not found.',
            ], 404);
        }

        return response()->json([
            'data' => $tenantModel,
        ]);
    }

    public function update(UpdateTenantRequest $request, int $tenant): JsonResponse
    {
        try {
            $dto = UpdateTenantDTO::fromArray($request->validated());
            $tenantModel = $this->tenantService->updateTenant($tenant, $dto);
        } catch (TenantNotFoundException) {
            return response()->json([
                'message' => 'Tenant not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Tenant updated successfully.',
            'data' => $tenantModel,
        ]);
    }

    public function destroy(int $tenant, Request $request): JsonResponse
    {
        $dropDatabase = $request->boolean('drop_database', false);

        try {
            $this->tenantService->deleteTenant($tenant, $dropDatabase);
        } catch (TenantNotFoundException) {
            return response()->json([
                'message' => 'Tenant not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Tenant deleted successfully.',
        ]);
    }
}
