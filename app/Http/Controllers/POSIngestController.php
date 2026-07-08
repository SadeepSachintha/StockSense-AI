<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PosTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class POSIngestController extends Controller
{
    /**
     * Ingest a batch of POS transactions.
     * Decreases current stock and reports safety stock warnings.
     */
    public function ingest(Request $request): JsonResponse
    {
        $request->validate([
            'transactions' => 'required|array',
            'transactions.*.sku' => 'required|string',
            'transactions.*.quantity' => 'required|integer|min:1',
            'transactions.*.price_sold' => 'required|numeric|min:0',
            'transactions.*.transaction_date' => 'required|date',
        ]);

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
}
