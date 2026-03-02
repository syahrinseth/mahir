<?php

namespace App\Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Portfolio\DTOs\CreatePortfolioDTO;
use App\Modules\Portfolio\DTOs\UpdatePortfolioDTO;
use App\Modules\Portfolio\Http\Requests\CreatePortfolioRequest;
use App\Modules\Portfolio\Http\Requests\UpdatePortfolioRequest;
use App\Modules\Portfolio\Repositories\PortfolioRepository;
use App\Modules\Portfolio\Services\PortfolioService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

#[Group('Portfolios', weight: 7)]
class PortfolioController extends Controller
{
    public function __construct(
        private PortfolioService $portfolioService,
        private PortfolioRepository $repository,
    ) {}

    /**
     * List all portfolio items.
     *
     * Unauthenticated requests only see published portfolio items.
     */
    public function index(Request $request): JsonResponse
    {
        $portfolios = $request->user()
            ? $this->repository->all()
            : $this->repository->findPublished();

        return response()->json(['data' => $portfolios]);
    }

    /**
     * Create a new portfolio item.
     */
    public function store(CreatePortfolioRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

        $dto = CreatePortfolioDTO::fromArray($data);
        $portfolio = $this->portfolioService->createPortfolio($dto);

        return response()->json([
            'message' => 'Portfolio created successfully.',
            'data' => $portfolio->load('author'),
        ], 201);
    }

    /**
     * Show a specific portfolio item.
     *
     * Unauthenticated requests can only access published portfolio items.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $portfolio = $request->user()
            ? $this->repository->findById($id)
            : $this->repository->findPublishedById($id);

        if (! $portfolio) {
            abort(404, 'Portfolio not found.');
        }

        return response()->json(['data' => $portfolio->load(['author', 'category', 'media'])]);
    }

    /**
     * Update an existing portfolio item.
     */
    public function update(UpdatePortfolioRequest $request, int $id): JsonResponse
    {
        $dto = UpdatePortfolioDTO::fromArray($request->validated());
        $portfolio = $this->portfolioService->updatePortfolio($id, $dto);

        if (! $portfolio) {
            abort(404, 'Portfolio not found.');
        }

        return response()->json([
            'message' => 'Portfolio updated successfully.',
            'data' => $portfolio->load(['author', 'category']),
        ]);
    }

    /**
     * Delete a portfolio item.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->repository->delete($id);

        if (! $deleted) {
            abort(404, 'Portfolio not found.');
        }

        return response()->json(['message' => 'Portfolio deleted successfully.']);
    }

    /**
     * Publish a portfolio item.
     */
    public function publish(int $id): JsonResponse
    {
        $portfolio = $this->portfolioService->publishPortfolio($id);

        if (! $portfolio) {
            abort(404, 'Portfolio not found.');
        }

        return response()->json([
            'message' => 'Portfolio published successfully.',
            'data' => $portfolio,
        ]);
    }

    /**
     * Archive a portfolio item.
     */
    public function archive(int $id): JsonResponse
    {
        $portfolio = $this->portfolioService->archivePortfolio($id);

        if (! $portfolio) {
            abort(404, 'Portfolio not found.');
        }

        return response()->json([
            'message' => 'Portfolio archived successfully.',
            'data' => $portfolio,
        ]);
    }
}
