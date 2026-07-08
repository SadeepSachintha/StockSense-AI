<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use App\Services\TenantManager;

trait BelongsToTenant
{
    /**
     * Boot the belongs to tenant trait for a model.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            $tenantManager = app(TenantManager::class);
            if ($tenantManager->hasTenant() && !isset($model->tenant_id)) {
                $model->tenant_id = $tenantManager->getTenantId();
            }
        });
    }
}
