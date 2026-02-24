<?php

namespace App\Modules\Subscription\Enums;

enum PlanType: string
{
    case Basic = 'basic';
    case Pro = 'pro';
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
