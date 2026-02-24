<?php

namespace App\Modules\Subscription\Enums;

/**
 * Available subscription plan types.
 */
enum PlanType: string
{
    /** Basic tier with essential features. */
    case Basic = 'basic';

    /** Professional tier with advanced features. */
    case Pro = 'pro';

    /** Enterprise tier with full access and priority support. */
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::Basic => 'Basic',
            self::Pro => 'Pro',
            self::Enterprise => 'Enterprise',
        };
    }
}
