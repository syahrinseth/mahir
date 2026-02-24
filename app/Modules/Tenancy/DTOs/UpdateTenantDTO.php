<?php

namespace App\Modules\Tenancy\DTOs;

class UpdateTenantDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $slug = null,
        public readonly ?string $domain = null,
        public readonly ?bool $isActive = null,
    ) {}

    /**
     * @param  array{name?: string, slug?: string, domain?: string, is_active?: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            domain: $data['domain'] ?? null,
            isActive: $data['is_active'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'slug' => $this->slug,
            'domain' => $this->domain,
            'is_active' => $this->isActive,
        ], fn (mixed $value): bool => $value !== null);
    }
}
