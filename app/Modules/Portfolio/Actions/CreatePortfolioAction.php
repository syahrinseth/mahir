<?php

namespace App\Modules\Portfolio\Actions;

use App\Modules\Portfolio\DTOs\CreatePortfolioDTO;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Services\PortfolioService;
use App\Shared\Contracts\ActionContract;

/**
 * Create a new portfolio item.
 */
class CreatePortfolioAction implements ActionContract
{
    public function __construct(
        private PortfolioService $portfolioService,
    ) {}

    /**
     * @param  array{user_id: int, title: string, slug: string, description: string, category_id?: int|null, client_name?: string|null, project_url?: string|null, featured_image?: string|null, technologies?: list<string>|null, status?: string, sort_order?: int, started_at?: string|null, ended_at?: string|null, published_at?: string|null}  $data
     */
    public function execute(array $data): Portfolio
    {
        $dto = CreatePortfolioDTO::fromArray($data);

        return $this->portfolioService->createPortfolio($dto);
    }
}
