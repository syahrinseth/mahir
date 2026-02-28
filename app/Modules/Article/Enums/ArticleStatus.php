<?php

namespace App\Modules\Article\Enums;

/**
 * Possible states of an article.
 */
enum ArticleStatus: string
{
    /** The article is a work in progress and not visible to others. */
    case Draft = 'draft';

    /** The article is live and visible to readers. */
    case Published = 'published';

    /** The article has been taken down and is no longer visible. */
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
            self::Draft => 'warning',
            self::Published => 'success',
            self::Archived => 'gray',
        };
    }
}
