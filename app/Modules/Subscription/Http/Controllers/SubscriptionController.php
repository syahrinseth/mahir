<?php

namespace App\Modules\Subscription\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscription\DTOs\CreateSubscriptionDTO;
use App\Modules\Subscription\DTOs\UpdateSubscriptionDTO;
use App\Modules\Subscription\Http\Requests\CreateSubscriptionRequest;
use App\Modules\Subscription\Http\Requests\UpdateSubscriptionRequest;
use App\Modules\Subscription\Repositories\SubscriptionRepository;
use App\Modules\Subscription\Services\SubscriptionService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

#[Group('Subscriptions', weight: 2)]
class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private SubscriptionRepository $repository,
    ) {}

    /**
     * List all subscriptions.
     *
     * Retrieves a list of all subscriptions across all tenants.
     * This is a landlord-scoped endpoint that requires authentication.
     */
    public function index(): JsonResponse
    {
        $subscriptions = $this->repository->all();

        /**
         * List of all subscriptions.
         *
         * @body array{data: array<int, array{id: int, tenant_id: int, plan: string, status: string, trial_ends_at: ?string, starts_at: ?string, ends_at: ?string, created_at: string, updated_at: string}>}
         */
        return response()->json([
            'data' => $subscriptions,
        ]);
    }

    /**
     * Create a new subscription.
     *
     * Creates a new subscription for a given tenant. Each tenant can have
     * one active subscription at a time.
     */
    #[Response(422, description: 'Validation error.', type: 'array{message: string, errors: array<string, string[]>}')]
    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        $dto = CreateSubscriptionDTO::fromArray($request->validated());
        $subscription = $this->subscriptionService->createSubscription($dto);

        /**
         * Subscription created successfully.
         *
         * @status 201
         *
         * @body array{message: string, data: array{id: int, tenant_id: int, plan: string, status: string, trial_ends_at: ?string, starts_at: ?string, ends_at: ?string, created_at: string, updated_at: string, tenant: array{id: int, name: string, slug: string, domain: string, database: string, is_active: bool, created_at: string, updated_at: string}}}
         */
        return response()->json([
            'message' => 'Subscription created successfully.',
            'data' => $subscription->load('tenant'),
        ], 201);
    }

    /**
     * Get a subscription.
     *
     * Retrieves a single subscription by its ID.
     *
     * @param  int  $id  The subscription ID.
     */
    #[PathParameter('subscription', description: 'The subscription ID.', type: 'int', example: 1)]
    public function show(int $id): JsonResponse
    {
        $subscription = $this->repository->findById($id);

        if (! $subscription) {
            abort(404, 'Subscription not found.');
        }

        /**
         * Subscription details.
         *
         * @body array{data: array{id: int, tenant_id: int, plan: string, status: string, trial_ends_at: ?string, starts_at: ?string, ends_at: ?string, created_at: string, updated_at: string}}
         */
        return response()->json([
            'data' => $subscription,
        ]);
    }

    /**
     * Update a subscription.
     *
     * Updates an existing subscription. Only the provided fields will be updated;
     * omitted fields remain unchanged. Commonly used to change plans or update status.
     *
     * @param  int  $id  The subscription ID.
     */
    #[PathParameter('subscription', description: 'The subscription ID.', type: 'int', example: 1)]
    #[Response(422, description: 'Validation error.', type: 'array{message: string, errors: array<string, string[]>}')]
    public function update(UpdateSubscriptionRequest $request, int $id): JsonResponse
    {
        $dto = UpdateSubscriptionDTO::fromArray($request->validated());
        $subscription = $this->subscriptionService->updateSubscription($id, $dto);

        if (! $subscription) {
            abort(404, 'Subscription not found.');
        }

        /**
         * Subscription updated successfully.
         *
         * @body array{message: string, data: array{id: int, tenant_id: int, plan: string, status: string, trial_ends_at: ?string, starts_at: ?string, ends_at: ?string, created_at: string, updated_at: string, tenant: array{id: int, name: string, slug: string, domain: string, database: string, is_active: bool, created_at: string, updated_at: string}}}
         */
        return response()->json([
            'message' => 'Subscription updated successfully.',
            'data' => $subscription->load('tenant'),
        ]);
    }

    /**
     * Delete a subscription.
     *
     * Permanently deletes a subscription. This action is irreversible.
     *
     * @param  int  $id  The subscription ID.
     */
    #[PathParameter('subscription', description: 'The subscription ID.', type: 'int', example: 1)]
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->repository->delete($id);

        if (! $deleted) {
            abort(404, 'Subscription not found.');
        }

        /**
         * Subscription deleted successfully.
         *
         * @body array{message: string}
         */
        return response()->json([
            'message' => 'Subscription deleted successfully.',
        ]);
    }
}
