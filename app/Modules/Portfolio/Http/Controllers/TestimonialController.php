<?php

namespace App\Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Portfolio\DTOs\CreateTestimonialDTO;
use App\Modules\Portfolio\DTOs\UpdateTestimonialDTO;
use App\Modules\Portfolio\Http\Requests\CreateTestimonialRequest;
use App\Modules\Portfolio\Http\Requests\UpdateTestimonialRequest;
use App\Modules\Portfolio\Repositories\TestimonialRepository;
use App\Modules\Portfolio\Services\TestimonialService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Testimonials', weight: 10)]
class TestimonialController extends Controller
{
    public function __construct(
        private TestimonialService $testimonialService,
        private TestimonialRepository $repository,
    ) {}

    /**
     * List all testimonials.
     *
     * Unauthenticated requests only see published testimonials.
     */
    public function index(Request $request): JsonResponse
    {
        $testimonials = $request->user()
            ? $this->repository->all()
            : $this->repository->findPublished();

        return response()->json(['data' => $testimonials]);
    }

    /**
     * Create a new testimonial.
     */
    public function store(CreateTestimonialRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $dto = CreateTestimonialDTO::fromArray($data);
        $testimonial = $this->testimonialService->createTestimonial($dto);

        return response()->json([
            'message' => 'Testimonial created successfully.',
            'data' => $testimonial->load('author'),
        ], 201);
    }

    /**
     * Show a specific testimonial.
     *
     * Unauthenticated requests can only access published testimonials.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $testimonial = $request->user()
            ? $this->repository->findById($id)
            : $this->repository->findPublishedById($id);

        if (! $testimonial) {
            abort(404, 'Testimonial not found.');
        }

        return response()->json(['data' => $testimonial->load(['author', 'portfolio', 'media'])]);
    }

    /**
     * Update an existing testimonial.
     */
    public function update(UpdateTestimonialRequest $request, int $id): JsonResponse
    {
        $dto = UpdateTestimonialDTO::fromArray($request->validated());
        $testimonial = $this->testimonialService->updateTestimonial($id, $dto);

        if (! $testimonial) {
            abort(404, 'Testimonial not found.');
        }

        return response()->json([
            'message' => 'Testimonial updated successfully.',
            'data' => $testimonial->load(['author', 'portfolio']),
        ]);
    }

    /**
     * Delete a testimonial.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->repository->delete($id);

        if (! $deleted) {
            abort(404, 'Testimonial not found.');
        }

        return response()->json(['message' => 'Testimonial deleted successfully.']);
    }

    /**
     * Publish a testimonial.
     */
    public function publish(int $id): JsonResponse
    {
        $testimonial = $this->testimonialService->publishTestimonial($id);

        if (! $testimonial) {
            abort(404, 'Testimonial not found.');
        }

        return response()->json([
            'message' => 'Testimonial published successfully.',
            'data' => $testimonial,
        ]);
    }
}
