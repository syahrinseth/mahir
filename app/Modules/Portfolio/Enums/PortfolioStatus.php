<?php

namespace App\Modules\Portfolio\Enums;

/**
 * Possible states of a portfolio item.
 */
enum PortfolioStatus: string
{
    /** The portfolio item is a work in progress and not visible to others. */
    case Draft = 'draft';

    /** The portfolio item is live and visible to viewers. */
    case Published = 'published';

    /** The portfolio item has been taken down and is no longer visible. */
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
            self::Archived => 'warning',
        };
    }
}
