<?php

namespace App\Modules\Portfolio\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Portfolio\Models\Testimonial;

/**
 * Policy for authorizing actions on testimonials.
 *
 * Tenant users can view and create testimonials freely.
 * Only the testimonial author can update, delete, or publish.
 */
class TestimonialPolicy
{
    /**
     * Determine whether the user can view a list of testimonials.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific testimonial.
     */
    public function view(User $user, Testimonial $testimonial): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create testimonials.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the given testimonial.
     */
    public function update(User $user, Testimonial $testimonial): bool
    {
        return $user->id === $testimonial->user_id;
    }

    /**
     * Determine whether the user can delete the given testimonial.
     */
    public function delete(User $user, Testimonial $testimonial): bool
    {
        return $user->id === $testimonial->user_id;
    }

    /**
     * Determine whether the user can publish the given testimonial.
     */
    public function publish(User $user, Testimonial $testimonial): bool
    {
        return $user->id === $testimonial->user_id;
    }
}
