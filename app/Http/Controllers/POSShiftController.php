<?php

namespace App\Http\Controllers;

use App\Models\RegisterShift;
use App\Models\PosTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class POSShiftController extends Controller
{
    /**
     * Retrieve the active shift for the tenant.
     */
    public function active(Request $request): JsonResponse
    {
        $shift = RegisterShift::where('status', 'open')->first();

        if (!$shift) {
            return response()->json([
                'status' => 'no_active_shift',
                'message' => 'No active register shift found.'
            ]);
        }

        // Calculate dynamic shift totals
        $totals = $this->calculateShiftSales($shift->id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $shift->id,
                'opened_at' => $shift->opened_at->toIso8601String(),
                'opening_float' => (float)$shift->opening_float,
                'cash_sales' => (float)$totals['cash_sales'],
                'card_sales' => (float)$totals['card_sales'],
                'qr_sales' => (float)$totals['qr_sales'],
                'total_sales' => (float)$totals['total_sales'],
                'expected_cash' => (float)($shift->opening_float + $totals['cash_sales']),
                'status' => $shift->status
            ]
        ]);
    }

    /**
     * Open a new shift.
     */
    public function open(Request $request): JsonResponse
    {
        $request->validate([
            'opening_float' => 'required|numeric|min:0'
        ]);

        // Check if there is already an open shift
        $existing = RegisterShift::where('status', 'open')->first();
        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'A register shift is already open.'
            ], 400);
        }

        $shift = RegisterShift::create([
            'id' => (string) Str::uuid(),
            'opened_at' => now(),
            'opening_float' => (float)$request->input('opening_float'),
            'status' => 'open'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Shift opened successfully.',
            'data' => $shift
        ]);
    }

    /**
     * Close the active shift.
     */
    public function close(Request $request): JsonResponse
    {
        $request->validate([
            'actual_cash' => 'required|numeric|min:0'
        ]);

        $shift = RegisterShift::where('status', 'open')->first();
        if (!$shift) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active shift found to close.'
            ], 404);
        }

        $totals = $this->calculateShiftSales($shift->id);
        $actualCash = (float)$request->input('actual_cash');
        $expectedCash = (float)($shift->opening_float + $totals['cash_sales']);
        $discrepancy = $actualCash - $expectedCash;

        $shift->update([
            'closed_at' => now(),
            'actual_cash' => $actualCash,
            'cash_sales' => $totals['cash_sales'],
            'card_sales' => $totals['card_sales'],
            'qr_sales' => $totals['qr_sales'],
            'status' => 'closed'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Shift closed successfully.',
            'data' => [
                'id' => $shift->id,
                'opened_at' => $shift->opened_at->toIso8601String(),
                'closed_at' => $shift->closed_at->toIso8601String(),
                'opening_float' => (float)$shift->opening_float,
                'cash_sales' => (float)$totals['cash_sales'],
                'card_sales' => (float)$totals['card_sales'],
                'qr_sales' => (float)$totals['qr_sales'],
                'total_sales' => (float)$totals['total_sales'],
                'expected_cash' => $expectedCash,
                'actual_cash' => $actualCash,
                'discrepancy' => $discrepancy
            ]
        ]);
    }

    /**
     * Helper to compute payment split totals for a shift.
     */
    private function calculateShiftSales(string $shiftId): array
    {
        $transactions = PosTransaction::where('shift_id', $shiftId)->get();
        $grouped = $transactions->groupBy('receipt_id');

        $cashSales = 0.00;
        $cardSales = 0.00;
        $qrSales = 0.00;

        foreach ($grouped as $receiptId => $items) {
            $first = $items->first();
            $subtotal = $items->sum(function ($item) {
                return (float)$item->price_sold * $item->quantity;
            });
            $discount = $first ? (float)$first->discount_amount : 0.00;
            $tax = $first ? (float)$first->tax_amount : 0.00;
            $total = (float)max(0, $subtotal - $discount + $tax);

            $payMethod = $first ? strtolower($first->payment_method) : 'cash';
            if ($payMethod === 'card') {
                $cardSales += $total;
            } elseif ($payMethod === 'qr') {
                $qrSales += $total;
            } else {
                $cashSales += $total;
            }
        }

        return [
            'cash_sales' => $cashSales,
            'card_sales' => $cardSales,
            'qr_sales' => $qrSales,
            'total_sales' => $cashSales + $cardSales + $qrSales
        ];
    }
}
