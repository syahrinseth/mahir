<?php

namespace App\Modules\Auth\Enums;

/**
 * Available tenant user permissions.
 *
 * Permissions follow the pattern: {resource}.{action}
 */
enum Permission: string
{
    /** View a list of users. */
    case UserViewAny = 'user.view-any';

    /** View a single user's details. */
    case UserView = 'user.view';

    /** Create a new user. */
    case UserCreate = 'user.create';

    /** Update an existing user. */
    case UserUpdate = 'user.update';

    /** Delete a user. */
    case UserDelete = 'user.delete';

    public function label(): string
    {
        return match ($this) {
            self::UserViewAny => 'View Users',
            self::UserView => 'View User',
            self::UserCreate => 'Create User',
            self::UserUpdate => 'Update User',
            self::UserDelete => 'Delete User',
        };
    }
}
