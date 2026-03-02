<?php

namespace App\Modules\Portfolio\Actions;

use App\Modules\Portfolio\DTOs\CreateTestimonialDTO;
use App\Modules\Portfolio\Models\Testimonial;
use App\Modules\Portfolio\Services\TestimonialService;
use App\Shared\Contracts\ActionContract;

/**
 * Create a new testimonial.
 */
class CreateTestimonialAction implements ActionContract
{
    public function __construct(
        private TestimonialService $testimonialService,
    ) {}

    /**
     * @param  array{user_id: int, client_name: string, content: string, portfolio_id?: int|null, client_position?: string|null, client_company?: string|null, rating?: int|null, is_featured?: bool, sort_order?: int, published_at?: string|null}  $data
     */
    public function execute(array $data): Testimonial
    {
        $dto = CreateTestimonialDTO::fromArray($data);

        return $this->testimonialService->createTestimonial($dto);
    }
}
