<?php

namespace App\Modules\Portfolio\Actions;

use App\Modules\Portfolio\Services\PortfolioService;
use App\Shared\Contracts\ActionContract;

/**
 * Delete a portfolio item permanently.
 */
class DeletePortfolioAction implements ActionContract
{
    public function __construct(
        private PortfolioService $portfolioService,
    ) {}

    public function execute(int $id): bool
    {
        return $this->portfolioService->deletePortfolio($id);
    }
}
