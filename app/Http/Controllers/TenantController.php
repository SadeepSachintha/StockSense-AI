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
            'tax_rate' => 'nullable|numeric|min:0|max:100',
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
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        // Clean nulls to avoid clearing active attributes if partial payload, but keep 0 values!
        $data = array_filter($validated, function ($val) {
            return !is_null($val);
        });
        $tenant->update($data);

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

    /**
     * Get users for a specific tenant.
     */
    public function getUsers(string $tenantId): JsonResponse
    {
        $users = \App\Models\TenantUser::where('tenant_id', $tenantId)->get();
        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * Add a user to a specific tenant.
     */
    public function addUser(Request $request, string $tenantId): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string|min:4',
            'role' => 'required|string|in:cashier,owner',
            'name' => 'required|string|max:255',
        ]);

        // Check if username unique inside this tenant scope
        $exists = \App\Models\TenantUser::where('tenant_id', $tenantId)
            ->where('username', $validated['username'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => "The username '{$validated['username']}' is already in use for this store."
            ], 400);
        }

        $user = \App\Models\TenantUser::create([
            'tenant_id' => $tenantId,
            'username' => $validated['username'],
            'password' => \Hash::make($validated['password']),
            'role' => $validated['role'],
            'name' => $validated['name'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Store operator account provisioned successfully.',
            'data' => $user
        ], 201);
    }

    /**
     * Delete a tenant user account.
     */
    public function deleteUser(string $id): JsonResponse
    {
        $user = \App\Models\TenantUser::findOrFail($id);
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Operator account deleted successfully.'
        ]);
    }
}
