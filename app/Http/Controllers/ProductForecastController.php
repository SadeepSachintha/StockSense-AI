<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ProductForecastController extends Controller
{
    /**
     * List all products scoped by the active tenant.
     */
    public function index(): JsonResponse
    {
        $products = Product::with('supplier')->get();
        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Retrieve forecast data for a product.
     * Integrates with Python FastAPI forecasting microservice.
     */
    public function showForecast(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        // Aggregate daily transactions for the last 30 days
        $history = $product->posTransactions()
            ->select(DB::raw("DATE(transaction_date) as date"), DB::raw("SUM(quantity) as total_qty"))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->pluck('total_qty')
            ->reverse()
            ->values()
            ->toArray();

        // Fallback baseline history if data is sparse (e.g., new product)
        if (count($history) < 14) {
            $history = array_merge(array_fill(0, 30 - count($history), 20), $history);
        }

        // Target API URL from config
        $apiUrl = config('services.predictor.url', 'http://127.0.0.1:8000') . '/predict';

        try {
            // Call FastAPI prediction engine (increased timeout to 35 seconds to accommodate free tier cold-starts)
            $response = Http::timeout(35)->post($apiUrl, [
                'sku_id' => $product->sku,
                'sales_history' => array_map('floatval', $history),
                'price' => (float)$product->price,
                'elasticity' => (float)$product->elasticity,
                'price_change_percent' => (float)$request->query('price_change_percent', 0.0),
                'lead_time_days' => (int)$product->lead_time_days,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Update product stats with latest ML calculations
                $product->update([
                    'safety_stock' => (int)$data['safety_stock'],
                    'wmape' => (float)$data['wmape'],
                ]);

                return response()->json([
                    'status' => 'success',
                    'product' => $product,
                    'forecast' => $data['forecast'],
                    'safety_stock' => $data['safety_stock'],
                    'reorder_point' => $data['reorder_point'],
                    'wmape' => $data['wmape'],
                    'message' => 'Forecast retrieved from LightGBM engine.'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to resolve forecast from prediction engine. Status code: ' . $response->status()
            ], 502);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Connection to prediction service failed: ' . $e->getMessage()
            ], 503);
        }
    }
}
