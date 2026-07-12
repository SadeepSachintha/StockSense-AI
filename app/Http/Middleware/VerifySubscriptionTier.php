<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class VerifySubscriptionTier
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $requiredTier  ('basic' or 'premium')
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $requiredTier): Response
    {
        // Resolve tenant ID
        $tenantId = session('tenant_id') 
            ?? $request->header('X-Tenant-ID')
            ?? $request->query('tenant_id');

        if (!$tenantId) {
            return $next($request);
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return $next($request);
        }

        $tier = $tenant->subscription_tier;

        if ($requiredTier === 'basic') {
            if (!in_array($tier, ['basic', 'premium'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This action requires a Basic or Premium subscription.'
                ], 403);
            }
        }

        if ($requiredTier === 'premium') {
            if ($tier !== 'premium') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This action requires a Premium subscription.'
                ], 403);
            }
        }

        return $next($request);
    }
}
