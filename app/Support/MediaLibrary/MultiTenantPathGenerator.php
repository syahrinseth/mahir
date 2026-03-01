<?php

namespace App\Support\MediaLibrary;

use App\Modules\Tenancy\Models\Tenant;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Prefixes all media paths with the current tenant ID to ensure
 * file-level isolation on shared storage disks.
 *
 * Produces paths like: tenants/{tenant_id}/{media_id}/
 */
class MultiTenantPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    protected function getBasePath(Media $media): string
    {
        $tenant = Tenant::current();
        $tenantId = $tenant?->getKey() ?? 'shared';

        return "tenants/{$tenantId}/{$media->getKey()}";
    }
}
