<?php

namespace App\Modules\Article\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Article\DTOs\CreateSeriesDTO;
use App\Modules\Article\DTOs\UpdateSeriesDTO;
use App\Modules\Article\Http\Requests\CreateSeriesRequest;
use App\Modules\Article\Http\Requests\UpdateSeriesRequest;
use App\Modules\Article\Repositories\ArticleSeriesRepository;
use App\Modules\Article\Services\ArticleService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

#[Group('Article Series', weight: 4)]
class ArticleSeriesController extends Controller
{
    public function __construct(
        private ArticleService $articleService,
        private ArticleSeriesRepository $repository,
    ) {}

    /**
     * List all article series.
     *
     * Retrieves a list of all article series for the current tenant.
     */
    public function index(): JsonResponse
    {
        $series = $this->repository->all();

        /**
         * List of all article series.
         *
         * @body array{data: array<int, array{id: int, user_id: int, title: string, slug: string, description: ?string, created_at: string, updated_at: string}>}
         */
        return response()->json([
            'data' => $series,
        ]);
    }

    /**
     * Create a new article series.
     *
     * Creates a new article series for the authenticated user.
     */
    #[Response(422, description: 'Validation error.', type: 'array{message: string, errors: array<string, string[]>}')]
    public function store(CreateSeriesRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $dto = CreateSeriesDTO::fromArray($data);
        $series = $this->articleService->createSeries($dto);

        /**
         * Series created successfully.
         *
         * @status 201
         *
         * @body array{message: string, data: array{id: int, user_id: int, title: string, slug: string, description: ?string, created_at: string, updated_at: string}}
         */
        return response()->json([
            'message' => 'Series created successfully.',
            'data' => $series->load('author'),
        ], 201);
    }

    /**
     * Get an article series.
     *
     * Retrieves a single article series by its ID, including its ordered articles.
     *
     * @param  int  $id  The series ID.
     */
    #[PathParameter('series', description: 'The series ID.', type: 'int', example: 1)]
    public function show(int $id): JsonResponse
    {
        $series = $this->repository->findById($id);

        if (! $series) {
            abort(404, 'Series not found.');
        }

        /**
         * Series details with ordered articles.
         *
         * @body array{data: array{id: int, user_id: int, title: string, slug: string, description: ?string, created_at: string, updated_at: string, ordered_articles: array<int, array{id: int, title: string, slug: string, series_order: ?int}>}}
         */
        return response()->json([
            'data' => $series,
        ]);
    }

    /**
     * Update an article series.
     *
     * Updates an existing article series. Only the provided fields will be updated.
     *
     * @param  int  $id  The series ID.
     */
    #[PathParameter('series', description: 'The series ID.', type: 'int', example: 1)]
    #[Response(422, description: 'Validation error.', type: 'array{message: string, errors: array<string, string[]>}')]
    public function update(UpdateSeriesRequest $request, int $id): JsonResponse
    {
        $dto = UpdateSeriesDTO::fromArray($request->validated());
        $series = $this->articleService->updateSeries($id, $dto);

        if (! $series) {
            abort(404, 'Series not found.');
        }

        /**
         * Series updated successfully.
         *
         * @body array{message: string, data: array{id: int, user_id: int, title: string, slug: string, description: ?string, created_at: string, updated_at: string}}
         */
        return response()->json([
            'message' => 'Series updated successfully.',
            'data' => $series,
        ]);
    }

    /**
     * Delete an article series.
     *
     * Permanently deletes an article series. Articles in the series are not deleted.
     *
     * @param  int  $id  The series ID.
     */
    #[PathParameter('series', description: 'The series ID.', type: 'int', example: 1)]
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->repository->delete($id);

        if (! $deleted) {
            abort(404, 'Series not found.');
        }

        /**
         * Series deleted successfully.
         *
         * @body array{message: string}
         */
        return response()->json([
            'message' => 'Series deleted successfully.',
        ]);
    }
}
