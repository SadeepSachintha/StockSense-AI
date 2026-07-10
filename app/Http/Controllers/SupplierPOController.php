<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SupplierPOController extends Controller
{
    /**
     * List all purchase orders scoped by active tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = PurchaseOrder::with(['product', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    /**
     * Log and dispatch a Purchase Order to a supplier.
     */
    public function dispatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|integer|min:1',
            'message_body' => 'required|string'
        ]);

        $tenantId = session('tenant_id') ?? $request->header('X-Tenant-ID') ?? 'tenant_001';

        $po = PurchaseOrder::create([
            'tenant_id' => $tenantId,
            'product_id' => $validated['product_id'],
            'supplier_id' => $validated['supplier_id'],
            'quantity' => $validated['quantity'],
            'status' => 'dispatched',
            'message_body' => $validated['message_body']
        ]);

        $product = Product::find($validated['product_id']);
        $supplier = $po->supplier;

        // Log Simulated Alert transmission based on supplier communication channels
        $channel = $supplier ? $supplier->alert_channel : 'Email';
        Log::info("STOCKSENSE DISPATCH: Sent restock PO #{$po->id} to supplier '{$supplier->name}' ({$supplier->email}) via {$channel}. Qty: {$validated['quantity']} units.");

        return response()->json([
            'status' => 'success',
            'message' => "Purchase Order #{$po->id} successfully dispatched to {$supplier->name}.",
            'data' => $po->load(['product', 'supplier'])
        ], 201);
    }

    /**
     * Mark a PO as received and increment the inventory levels.
     */
    public function receive(Request $request, string $id): JsonResponse
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status === 'received') {
            return response()->json([
                'status' => 'error',
                'message' => 'This purchase order has already been marked as received.'
            ], 400);
        }

        \DB::beginTransaction();
        try {
            // Update PO status
            $po->update(['status' => 'received']);

            // Increment product stock
            if ($po->product) {
                $po->product->increment('current_stock', $po->quantity);
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to receive stock: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Purchase Order #{$po->id} marked as received. Stock incremented by {$po->quantity} units.",
            'data' => $po->load(['product', 'supplier'])
        ]);
    }
}
