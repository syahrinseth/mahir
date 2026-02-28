<?php

namespace App\Modules\Portfolio\Actions;

use App\Modules\Portfolio\DTOs\UpdatePortfolioDTO;
use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Services\PortfolioService;
use App\Shared\Contracts\ActionContract;

/**
 * Update an existing portfolio item.
 */
class UpdatePortfolioAction implements ActionContract
{
    public function __construct(
        private PortfolioService $portfolioService,
    ) {}

    /**
     * @param  array{title?: string, slug?: string, description?: string, category_id?: int|null, client_name?: string|null, project_url?: string|null, featured_image?: string|null, technologies?: list<string>|null, status?: string, sort_order?: int, started_at?: string|null, ended_at?: string|null, published_at?: string|null}  $data
     */
    public function execute(int $id, array $data): ?Portfolio
    {
        $dto = UpdatePortfolioDTO::fromArray($data);

        return $this->portfolioService->updatePortfolio($id, $dto);
    }
}
