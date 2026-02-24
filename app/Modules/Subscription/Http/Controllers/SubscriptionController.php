<?php

namespace App\Modules\Subscription\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscription\DTOs\CreateSubscriptionDTO;
use App\Modules\Subscription\DTOs\UpdateSubscriptionDTO;
use App\Modules\Subscription\Http\Requests\CreateSubscriptionRequest;
use App\Modules\Subscription\Http\Requests\UpdateSubscriptionRequest;
use App\Modules\Subscription\Repositories\SubscriptionRepository;
use App\Modules\Subscription\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private SubscriptionRepository $repository,
    ) {}

    public function index(): JsonResponse
    {
        $subscriptions = $this->repository->all();

        return response()->json([
            'data' => $subscriptions,
        ]);
    }

    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        $dto = CreateSubscriptionDTO::fromArray($request->validated());
        $subscription = $this->subscriptionService->createSubscription($dto);

        return response()->json([
            'message' => 'Subscription created successfully.',
            'data' => $subscription->load('tenant'),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $subscription = $this->repository->findById($id);

        if (! $subscription) {
            return response()->json([
                'message' => 'Subscription not found.',
            ], 404);
        }

        return response()->json([
            'data' => $subscription,
        ]);
    }

    public function update(UpdateSubscriptionRequest $request, int $id): JsonResponse
    {
        $dto = UpdateSubscriptionDTO::fromArray($request->validated());
        $subscription = $this->subscriptionService->updateSubscription($id, $dto);

        if (! $subscription) {
            return response()->json([
                'message' => 'Subscription not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Subscription updated successfully.',
            'data' => $subscription->load('tenant'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->repository->delete($id);

        if (! $deleted) {
            return response()->json([
                'message' => 'Subscription not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Subscription deleted successfully.',
        ]);
    }
}
