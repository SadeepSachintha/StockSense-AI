<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PosTransaction;
use App\Models\RegisterShift;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class POSIngestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $transactions = PosTransaction::with('product')
            ->orderBy('transaction_date', 'desc')
            ->get();

        $grouped = $transactions->groupBy('receipt_id')->map(function ($items, $receiptId) {
            $first = $items->first();
            
            $subtotal = $items->sum(function ($item) {
                return (float)$item->price_sold * $item->quantity;
            });

            $discount = $first ? (float)$first->discount_amount : 0.00;
            $tax = $first ? (float)$first->tax_amount : 0.00;

            return [
                'id' => $receiptId ?? 'TX-LEGACY',
                'time' => $first && $first->transaction_date ? $first->transaction_date->toIso8601String() : now()->toIso8601String(),
                'tenant_id' => $first ? $first->tenant_id : 'tenant_001',
                'items' => $items->map(function ($item) {
                    return [
                        'name' => $item->product->name ?? 'Unknown Product',
                        'sku' => $item->product->sku ?? 'UNKNOWN',
                        'quantity' => $item->quantity,
                        'price' => (float)$item->price_sold,
                        'subtotal' => (float)$item->price_sold * $item->quantity
                    ];
                })->values(),
                'subtotal' => (float)$subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => (float)max(0, $subtotal - $discount + $tax),
                'payment_method' => $first ? $first->payment_method : 'cash',
                'shift_id' => $first ? $first->shift_id : null,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $grouped
        ]);
    }

    /**
     * Ingest a batch of POS transactions.
     * Decreases current stock and reports safety stock warnings.
     */
    public function ingest(Request $request): JsonResponse
    {
        $request->validate([
            'receipt_id' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shift_id' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'transactions' => 'required|array',
            'transactions.*.sku' => 'required|string',
            'transactions.*.quantity' => 'required|integer|min:1',
            'transactions.*.price_sold' => 'required|numeric|min:0',
            'transactions.*.transaction_date' => 'required|date',
        ]);

        $receiptId = $request->input('receipt_id') ?? ('TX-' . rand(100000, 999999));
        $discountAmount = $request->input('discount_amount') ?? 0.00;
        $taxAmount = $request->input('tax_amount') ?? 0.00;
        $paymentMethod = $request->input('payment_method') ?? 'cash';
        
        $shiftId = $request->input('shift_id');
        if (!$shiftId) {
            $activeShift = RegisterShift::where('status', 'open')->first();
            $shiftId = $activeShift ? $activeShift->id : null;
        }

        $ingestedCount = 0;
        $warnings = [];

        foreach ($request->input('transactions') as $tx) {
            // Find product (scoped dynamically by active tenant!)
            $product = Product::where('sku', $tx['sku'])->first();
            
            if (!$product) {
                continue;
            }

            // Record transaction
            PosTransaction::create([
                'id' => (string) Str::uuid(),
                'product_id' => $product->id,
                'quantity' => (int)$tx['quantity'],
                'price_sold' => (float)$tx['price_sold'],
                'transaction_date' => $tx['transaction_date'],
                'receipt_id' => $receiptId,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shift_id' => $shiftId,
                'payment_method' => $paymentMethod,
            ]);

            // Deduct current stock
            $newStock = max(0, $product->current_stock - (int)$tx['quantity']);
            $product->update(['current_stock' => $newStock]);

            // Evaluate stock warning thresholds
            if ($newStock <= $product->safety_stock) {
                $warnings[] = [
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'current_stock' => $newStock,
                    'safety_stock' => $product->safety_stock,
                    'message' => 'Safety limit violated. Restocking recommended.'
                ];
            }

            $ingestedCount++;
        }

        return response()->json([
            'status' => 'success',
            'ingested_count' => $ingestedCount,
            'warnings' => $warnings,
            'message' => 'POS Ingestion complete.'
        ]);
    }

    /**
     * Void a transaction receipt, refunding the inventory.
     */
    public function destroy(string $receiptId): JsonResponse
    {
        $transactions = PosTransaction::where('receipt_id', $receiptId)->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found.'
            ], 404);
        }

        \DB::beginTransaction();
        try {
            foreach ($transactions as $tx) {
                if ($tx->product) {
                    $tx->product->increment('current_stock', $tx->quantity);
                }
                $tx->delete();
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to void transaction: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction voided and stock refunded successfully.'
        ]);
    }
}
