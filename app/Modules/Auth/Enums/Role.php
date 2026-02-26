<?php

namespace App\Modules\Auth\Enums;

/**
 * Available tenant user roles.
 */
enum Role: string
{
    /** Full access to all tenant resources. */
    case Admin = 'admin';

    /** Standard user with limited permissions. */
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::User => 'User',
        };
    }
}
