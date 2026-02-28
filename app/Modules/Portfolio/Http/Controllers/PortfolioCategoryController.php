<?php

namespace App\Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Portfolio\DTOs\CreatePortfolioCategoryDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioCategoryDTO;
use App\Modules\Portfolio\Http\Requests\CreatePortfolioCategoryRequest;
use App\Modules\Portfolio\Http\Requests\UpdatePortfolioCategoryRequest;
use App\Modules\Portfolio\Repositories\PortfolioCategoryRepository;
use App\Modules\Portfolio\Services\PortfolioService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

#[Group('Portfolio Categories', weight: 8)]
class PortfolioCategoryController extends Controller
{
    public function __construct(
        private PortfolioService $portfolioService,
        private PortfolioCategoryRepository $repository,
    ) {}

    /**
     * List all portfolio categories.
     */
    public function index(): JsonResponse
    {
        $categories = $this->repository->all();

        return response()->json(['data' => $categories]);
    }

    /**
     * Create a new portfolio category.
     */
    public function store(CreatePortfolioCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $dto = CreatePortfolioCategoryDTO::fromArray($data);
        $category = $this->portfolioService->createCategory($dto);

        return response()->json([
            'message' => 'Portfolio category created successfully.',
            'data' => $category->load('creator'),
        ], 201);
    }

    /**
     * Show a specific portfolio category.
     */
    public function show(int $id): JsonResponse
    {
        $category = $this->repository->findById($id);

        if (! $category) {
            abort(404, 'Portfolio category not found.');
        }

        return response()->json(['data' => $category->load(['creator', 'orderedPortfolios'])]);
    }

    /**
     * Update an existing portfolio category.
     */
    public function update(UpdatePortfolioCategoryRequest $request, int $id): JsonResponse
    {
        $dto = UpdatePortfolioCategoryDTO::fromArray($request->validated());
        $category = $this->portfolioService->updateCategory($id, $dto);

        if (! $category) {
            abort(404, 'Portfolio category not found.');
        }

        return response()->json([
            'message' => 'Portfolio category updated successfully.',
            'data' => $category->load('creator'),
        ]);
    }

    /**
     * Delete a portfolio category.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->repository->delete($id);

        if (! $deleted) {
            abort(404, 'Portfolio category not found.');
        }

        return response()->json(['message' => 'Portfolio category deleted successfully.']);
    }
}
