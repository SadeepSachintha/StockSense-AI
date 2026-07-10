<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\RegisterShift;
use App\Models\PosTransaction;

class POSShiftTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_manage_shifts_and_reconcile_sales()
    {
        // 1. Arrange: Setup Tenant and Product
        $tenant = Tenant::create([
            'id' => 'tenant_123',
            'name' => 'Retail Master Store',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);

        $product = Product::create([
            'tenant_id' => 'tenant_123',
            'sku' => 'SKU-SH-001',
            'name' => 'Premium Shampoo',
            'category' => 'Cosmetics',
            'price' => 1500.00,
            'current_stock' => 50,
            'safety_stock' => 5,
            'max_stock' => 100,
            'reorder_qty' => 20,
            'lead_time_days' => 2
        ]);

        // Verify there is no active shift initial status
        $resActive = $this->withHeaders(['X-Tenant-ID' => 'tenant_123'])
            ->getJson('/api/shifts/active');
        $resActive->assertStatus(200);
        $resActive->assertJsonFragment(['status' => 'no_active_shift']);

        // 2. Act: Open shift with float 5,000
        $resOpen = $this->withHeaders(['X-Tenant-ID' => 'tenant_123'])
            ->postJson('/api/shifts/open', ['opening_float' => 5000.00]);
        $resOpen->assertStatus(200);
        $resOpen->assertJsonFragment(['status' => 'success']);

        // Assert double-open fails
        $resOpenDouble = $this->withHeaders(['X-Tenant-ID' => 'tenant_123'])
            ->postJson('/api/shifts/open', ['opening_float' => 1000.00]);
        $resOpenDouble->assertStatus(400);

        // 3. Act: Process checkouts with cash and card
        // Checkout 1: Cash sale of 2 items (LKR 3,000)
        $resCheckout1 = $this->withHeaders(['X-Tenant-ID' => 'tenant_123'])
            ->postJson('/api/products/ingest', [
                'receipt_id' => 'TX-CASH-01',
                'discount_amount' => 0.00,
                'tax_amount' => 0.00,
                'payment_method' => 'cash',
                'transactions' => [
                    [
                        'sku' => 'SKU-SH-001',
                        'quantity' => 2,
                        'price_sold' => 1500.00,
                        'transaction_date' => '2026-07-10 12:00:00'
                    ]
                ]
            ]);
        $resCheckout1->assertStatus(200);

        // Checkout 2: Card sale of 1 item (LKR 1,500)
        $resCheckout2 = $this->withHeaders(['X-Tenant-ID' => 'tenant_123'])
            ->postJson('/api/products/ingest', [
                'receipt_id' => 'TX-CARD-01',
                'discount_amount' => 0.00,
                'tax_amount' => 0.00,
                'payment_method' => 'card',
                'transactions' => [
                    [
                        'sku' => 'SKU-SH-001',
                        'quantity' => 1,
                        'price_sold' => 1500.00,
                        'transaction_date' => '2026-07-10 13:00:00'
                    ]
                ]
            ]);
        $resCheckout2->assertStatus(200);

        // Verify active shift parameters
        $resActive2 = $this->withHeaders(['X-Tenant-ID' => 'tenant_123'])
            ->getJson('/api/shifts/active');
        $resActive2->assertStatus(200);
        $resActive2->assertJsonFragment([
            'status' => 'open',
            'opening_float' => 5000.00,
            'cash_sales' => 3000.00,
            'card_sales' => 1500.00,
            'expected_cash' => 8000.00
        ]);

        // 4. Act: Close shift with counted cash 7,950 (short by 50)
        $resClose = $this->withHeaders(['X-Tenant-ID' => 'tenant_123'])
            ->postJson('/api/shifts/close', ['actual_cash' => 7950.00]);
        $resClose->assertStatus(200);
        
        $resClose->assertJsonFragment([
            'status' => 'success',
            'opening_float' => 5000.00,
            'cash_sales' => 3000.00,
            'card_sales' => 1500.00,
            'expected_cash' => 8000.00,
            'actual_cash' => 7950.00,
            'discrepancy' => -50.00
        ]);

        // Assert database state is closed
        $this->assertDatabaseHas('register_shifts', [
            'status' => 'closed',
            'actual_cash' => 7950.00
        ]);
    }
}
