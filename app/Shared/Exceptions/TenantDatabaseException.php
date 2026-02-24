<?php

namespace App\Shared\Exceptions;

use RuntimeException;

class TenantDatabaseException extends RuntimeException
{
    public static function failedToCreate(string $database, string $reason = ''): self
    {
        return new self("Failed to create tenant database [{$database}]. {$reason}");
    }

    public static function failedToDelete(string $database, string $reason = ''): self
    {
        return new self("Failed to delete tenant database [{$database}]. {$reason}");
    }
}
