<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
    /**
     * List all tenants in the system.
     */
    public function index(): JsonResponse
    {
        $tenants = Tenant::all();
        return response()->json([
            'status' => 'success',
            'data' => $tenants
        ]);
    }

    /**
     * Create a new tenant registration.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|string|unique:tenants,id|alpha_dash|max:50',
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'subscription_tier' => 'required|string|in:free,basic,premium',
            'status' => 'nullable|string|in:active,suspended',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        $tenant = Tenant::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Tenant registered successfully.',
            'data' => $tenant
        ], 201);
    }

    /**
     * View a specific tenant details.
     */
    public function show(string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $tenant
        ]);
    }

    /**
     * Update an existing tenant.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'subscription_tier' => 'nullable|string|in:free,basic,premium',
            'status' => 'nullable|string|in:active,suspended',
        ]);

        // Clean nulls to avoid clearing active attributes if partial payload
        $tenant->update(array_filter($validated));

        return response()->json([
            'status' => 'success',
            'message' => 'Tenant updated successfully.',
            'data' => $tenant
        ]);
    }

    /**
     * Delete a tenant.
     */
    public function destroy(string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        
        // Deleting tenant automatically cascades to products and transactions via DB foreign key triggers
        $tenant->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Tenant and associated records deleted.'
        ]);
    }
}
