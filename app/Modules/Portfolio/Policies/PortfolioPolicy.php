<?php

namespace App\Modules\Portfolio\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\Portfolio;

/**
 * Policy for authorizing actions on portfolios.
 *
 * Tenant users can view and create portfolios freely.
 * Only the portfolio author can update, delete, publish, or archive.
 */
class PortfolioPolicy
{
    /**
     * Determine whether the user can view a list of portfolios.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific portfolio.
     */
    public function view(User $user, Portfolio $portfolio): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create portfolios.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the given portfolio.
     */
    public function update(User $user, Portfolio $portfolio): bool
    {
        return $user->id === $portfolio->user_id;
    }

    /**
     * Determine whether the user can delete the given portfolio.
     */
    public function delete(User $user, Portfolio $portfolio): bool
    {
        return $user->id === $portfolio->user_id;
    }

    /**
     * Determine whether the user can publish the given portfolio.
     */
    public function publish(User $user, Portfolio $portfolio): bool
    {
        return $user->id === $portfolio->user_id;
    }

    /**
     * Determine whether the user can archive the given portfolio.
     */
    public function archive(User $user, Portfolio $portfolio): bool
    {
        return $user->id === $portfolio->user_id;
    }
}
