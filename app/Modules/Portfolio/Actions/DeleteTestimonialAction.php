<?php

namespace App\Modules\Portfolio\Actions;

use App\Modules\Portfolio\Services\TestimonialService;
use App\Shared\Contracts\ActionContract;

/**
 * Delete a testimonial permanently.
 */
class DeleteTestimonialAction implements ActionContract
{
    public function __construct(
        private TestimonialService $testimonialService,
    ) {}

    public function execute(int $id): bool
    {
        return $this->testimonialService->deleteTestimonial($id);
    }
}
