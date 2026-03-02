<?php

namespace App\Modules\Portfolio\Services;

use App\Modules\Portfolio\DTOs\CreateTestimonialDTO;
use App\Modules\Portfolio\DTOs\UpdateTestimonialDTO;
use App\Modules\Portfolio\Models\Testimonial;
use App\Modules\Portfolio\Repositories\TestimonialRepository;
use App\Shared\Contracts\ServiceContract;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Business logic for managing testimonials and their media.
 */
class TestimonialService implements ServiceContract
{
    public function __construct(
        private TestimonialRepository $testimonialRepository,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Testimonials
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new testimonial.
     */
    public function createTestimonial(CreateTestimonialDTO $dto): Testimonial
    {
        return $this->testimonialRepository->create($dto->toArray());
    }

    /**
     * Update an existing testimonial.
     */
    public function updateTestimonial(int $id, UpdateTestimonialDTO $dto): ?Testimonial
    {
        $updated = $this->testimonialRepository->update($id, $dto->toArray());

        return $updated instanceof Testimonial ? $updated : null;
    }

    /**
     * Publish a testimonial by setting its published_at timestamp.
     */
    public function publishTestimonial(int $id): ?Testimonial
    {
        $testimonial = $this->testimonialRepository->findById($id);

        if (! $testimonial) {
            return null;
        }

        $dto = new UpdateTestimonialDTO(
            publishedAt: now()->toDateTimeString(),
        );

        $updated = $this->testimonialRepository->update($id, $dto->toArray());

        return $updated instanceof Testimonial ? $updated : null;
    }

    /**
     * Unpublish a testimonial by clearing its published_at timestamp.
     */
    public function unpublishTestimonial(int $id): ?Testimonial
    {
        $testimonial = $this->testimonialRepository->findById($id);

        if (! $testimonial) {
            return null;
        }

        $testimonial->update(['published_at' => null]);

        return $testimonial->fresh(['author', 'portfolio', 'media']);
    }

    /**
     * Delete a testimonial.
     */
    public function deleteTestimonial(int $id): bool
    {
        return $this->testimonialRepository->delete($id);
    }

    /*
    |--------------------------------------------------------------------------
    | Media (Spatie Media Library)
    |--------------------------------------------------------------------------
    */

    /**
     * Add a media file (client headshot/logo) to the testimonial's featured collection.
     */
    public function addMedia(Testimonial $testimonial, UploadedFile $file): Media
    {
        return $testimonial
            ->addMedia($file)
            ->toMediaCollection('featured');
    }

    /**
     * Delete a media item.
     */
    public function deleteMedia(int $mediaId): bool
    {
        $mediaClass = config('media-library.media_model');
        $media = $mediaClass::query()->find($mediaId);

        if (! $media) {
            return false;
        }

        $media->delete();

        return true;
    }

    /**
     * Get the featured media for a testimonial.
     *
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media>
     */
    public function getMediaForTestimonial(Testimonial $testimonial): \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection
    {
        return $testimonial->getMedia('featured');
    }
}
