<?php

namespace App\Shared\Exceptions;

use RuntimeException;

class TenantNotFoundException extends RuntimeException
{
    public static function forDomain(string $domain): self
    {
        return new self("No tenant found for domain [{$domain}].");
    }

    public static function forId(int $id): self
    {
        return new self("No tenant found with ID [{$id}].");
    }

    public static function forSlug(string $slug): self
    {
        return new self("No tenant found with slug [{$slug}].");
    }
}
