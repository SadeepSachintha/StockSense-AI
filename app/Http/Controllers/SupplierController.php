<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    /**
     * List all suppliers scoped under the active tenant context.
     */
    public function index(): JsonResponse
    {
        $suppliers = Supplier::all();
        return response()->json([
            'status' => 'success',
            'data' => $suppliers
        ]);
    }

    /**
     * Store a new supplier in the system (automatically scoped by tenant trait).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'alert_channel' => 'nullable|string|in:Email,WhatsApp,SMS',
        ]);

        $validated['alert_channel'] = $validated['alert_channel'] ?? 'Email';

        $supplier = Supplier::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier registered successfully.',
            'data' => $supplier
        ], 201);
    }
}
