<?php

namespace App\Modules\Portfolio\Actions;

use App\Modules\Portfolio\Models\Portfolio;
use App\Modules\Portfolio\Services\PortfolioService;
use App\Shared\Contracts\ActionContract;

/**
 * Publish a portfolio item by setting its status and publish date.
 */
class PublishPortfolioAction implements ActionContract
{
    public function __construct(
        private PortfolioService $portfolioService,
    ) {}

    public function execute(int $id): ?Portfolio
    {
        return $this->portfolioService->publishPortfolio($id);
    }
}
