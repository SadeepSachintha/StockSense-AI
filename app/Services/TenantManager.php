<?php

namespace App\Services;

class TenantManager
{
    protected ?string $tenantId = null;

    /**
     * Set the active tenant ID.
     */
    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Get the active tenant ID.
     */
    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    /**
     * Check if a tenant is currently set.
     */
    public function hasTenant(): bool
    {
        return !is_null($this->tenantId);
    }
}
