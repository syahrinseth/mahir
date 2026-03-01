<?php

namespace App\Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Portfolio\Http\Requests\StorePortfolioMediaRequest;
use App\Modules\Portfolio\Repositories\PortfolioRepository;
use App\Modules\Portfolio\Services\PortfolioService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Portfolio Media', weight: 9)]
class PortfolioMediaController extends Controller
{
    public function __construct(
        private PortfolioService $portfolioService,
        private PortfolioRepository $portfolioRepository,
    ) {}

    /**
     * List all media for a portfolio item.
     */
    public function index(int $portfolioId): JsonResponse
    {
        $portfolio = $this->portfolioRepository->findById($portfolioId);

        if (! $portfolio) {
            abort(404, 'Portfolio not found.');
        }

        $media = $this->portfolioService->getMediaForPortfolio($portfolio);

        return response()->json(['data' => $media]);
    }

    /**
     * Upload a media file for a portfolio item.
     */
    public function store(StorePortfolioMediaRequest $request, int $portfolioId): JsonResponse
    {
        $portfolio = $this->portfolioRepository->findById($portfolioId);

        if (! $portfolio) {
            abort(404, 'Portfolio not found.');
        }

        $media = $this->portfolioService->addMedia(
            portfolio: $portfolio,
            file: $request->file('file'),
            collection: $request->validated('collection', 'gallery'),
            properties: [
                'caption' => $request->validated('caption'),
                'sort_order' => $request->validated('sort_order', 0),
            ],
        );

        return response()->json([
            'message' => 'Media uploaded successfully.',
            'data' => $media,
        ], 201);
    }

    /**
     * Delete a media item from a portfolio.
     */
    public function destroy(int $portfolioId, int $mediaId): JsonResponse
    {
        $deleted = $this->portfolioService->deleteMedia($mediaId);

        if (! $deleted) {
            abort(404, 'Media not found.');
        }

        return response()->json(['message' => 'Media deleted successfully.']);
    }

    /**
     * Reorder media items for a portfolio.
     */
    public function reorder(Request $request, int $portfolioId): JsonResponse
    {
        $portfolio = $this->portfolioRepository->findById($portfolioId);

        if (! $portfolio) {
            abort(404, 'Portfolio not found.');
        }

        $validated = $request->validate([
            /** Ordered list of media IDs. */
            'media_ids' => ['required', 'array'],
            /** Each media ID. */
            'media_ids.*' => ['integer'],
        ]);

        $this->portfolioService->reorderMedia($portfolio, $validated['media_ids']);

        return response()->json(['message' => 'Media reordered successfully.']);
    }
}
