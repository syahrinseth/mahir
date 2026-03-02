<?php

namespace App\Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Portfolio\Http\Requests\StoreTestimonialMediaRequest;
use App\Modules\Portfolio\Repositories\TestimonialRepository;
use App\Modules\Portfolio\Services\TestimonialService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Testimonial Media', weight: 11)]
class TestimonialMediaController extends Controller
{
    public function __construct(
        private TestimonialService $testimonialService,
        private TestimonialRepository $testimonialRepository,
    ) {}

    /**
     * List media for a testimonial.
     *
     * Unauthenticated requests can only access media for published testimonials.
     * Pass `?collection=headshot` to filter by collection.
     */
    public function index(Request $request, int $testimonialId): JsonResponse
    {
        $testimonial = $request->user()
            ? $this->testimonialRepository->findById($testimonialId)
            : $this->testimonialRepository->findPublishedById($testimonialId);

        if (! $testimonial) {
            abort(404, 'Testimonial not found.');
        }

        /** @var string|null $collection Filter by media collection name. */
        $collection = $request->query('collection');

        $media = $collection
            ? $this->testimonialService->getMediaForTestimonial($testimonial, $collection)
            : $testimonial->getMedia('*');

        return response()->json(['data' => $media]);
    }

    /**
     * Upload a client headshot or logo for a testimonial.
     */
    public function store(StoreTestimonialMediaRequest $request, int $testimonialId): JsonResponse
    {
        $testimonial = $this->testimonialRepository->findById($testimonialId);

        if (! $testimonial) {
            abort(404, 'Testimonial not found.');
        }

        $media = $this->testimonialService->addMedia(
            testimonial: $testimonial,
            file: $request->file('file'),
        );

        return response()->json([
            'message' => 'Media uploaded successfully.',
            'data' => $media,
        ], 201);
    }

    /**
     * Delete a media item from a testimonial.
     */
    public function destroy(int $testimonialId, int $mediaId): JsonResponse
    {
        $deleted = $this->testimonialService->deleteMedia($mediaId);

        if (! $deleted) {
            abort(404, 'Media not found.');
        }

        return response()->json(['message' => 'Media deleted successfully.']);
    }
}
