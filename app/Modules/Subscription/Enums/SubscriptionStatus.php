<?php

namespace App\Modules\Subscription\Enums;

/**
 * Possible states of a subscription.
 */
enum SubscriptionStatus: string
{
    /** The subscription is active and in good standing. */
    case Active = 'active';

    /** The subscription is in a trial period. */
    case Trial = 'trial';

    /** The subscription has been cancelled by the user. */
    case Cancelled = 'cancelled';

    /** The subscription has expired and is no longer valid. */
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Trial => 'Trial',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Trial => 'info',
            self::Cancelled => 'warning',
            self::Expired => 'danger',
        };
    }
}
