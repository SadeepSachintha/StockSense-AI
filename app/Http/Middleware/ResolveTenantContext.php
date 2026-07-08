<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TenantManager;

class ResolveTenantContext
{
    protected TenantManager $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Resolve tenant ID from header, query parameters, or session
        $tenantId = $request->header('X-Tenant-ID') 
            ?? $request->query('tenant_id') 
            ?? ($request->hasSession() ? $request->session()->get('tenant_id') : null);

        // Set context in TenantManager
        if ($tenantId) {
            $this->tenantManager->setTenantId($tenantId);
        }

        return $next($request);
    }
}
