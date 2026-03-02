<?php

namespace App\Modules\Portfolio\Actions;

use App\Modules\Portfolio\DTOs\UpdateTestimonialDTO;
use App\Modules\Portfolio\Models\Testimonial;
use App\Modules\Portfolio\Services\TestimonialService;
use App\Shared\Contracts\ActionContract;

/**
 * Update an existing testimonial.
 */
class UpdateTestimonialAction implements ActionContract
{
    public function __construct(
        private TestimonialService $testimonialService,
    ) {}

    /**
     * @param  array{client_name?: string, content?: string, portfolio_id?: int|null, client_position?: string|null, client_company?: string|null, rating?: int|null, is_featured?: bool, sort_order?: int, published_at?: string|null}  $data
     */
    public function execute(int $id, array $data): ?Testimonial
    {
        $dto = UpdateTestimonialDTO::fromArray($data);

        return $this->testimonialService->updateTestimonial($id, $dto);
    }
}
