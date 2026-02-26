<?php

namespace App\Modules\Auth\Policies;

use App\Modules\Auth\Enums\Permission;
use App\Modules\Auth\Models\User;

/**
 * Policy for authorizing actions on tenant users.
 *
 * Admin users bypass all checks via the before() method.
 * Non-admin users are checked against their assigned permissions.
 */
class UserPolicy
{
    /**
     * Grant all abilities to admin users.
     *
     * Returns null for 'delete' so the delete() method
     * can enforce the self-deletion guard.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin') && $ability !== 'delete') {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view a list of users.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::UserViewAny->value);
    }

    /**
     * Determine whether the user can view a specific user.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasPermissionTo(Permission::UserView->value);
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::UserCreate->value);
    }

    /**
     * Determine whether the user can update the given user.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasPermissionTo(Permission::UserUpdate->value);
    }

    /**
     * Determine whether the user can delete the given user.
     *
     * No user may delete themselves, regardless of role.
     * This check is not bypassed by before() because it
     * returns null for self-deletion.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo(Permission::UserDelete->value);
    }
}
