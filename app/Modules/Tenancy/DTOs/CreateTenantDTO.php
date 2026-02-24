<?php

namespace App\Modules\Tenancy\DTOs;

class CreateTenantDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $domain,
        public readonly bool $isActive = true,
    ) {}

    /**
     * @param  array{name: string, slug: string, domain: string, is_active?: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            domain: $data['domain'],
            isActive: $data['is_active'] ?? true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'domain' => $this->domain,
            'is_active' => $this->isActive,
        ];
    }
}
