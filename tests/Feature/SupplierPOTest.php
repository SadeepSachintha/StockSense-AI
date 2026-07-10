<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierPOTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the full lifecycle of a purchase order: dispatching, listing, and receiving.
     *
     * @test
     */
    public function it_can_manage_purchase_orders_and_replenish_stock()
    {
        // 1. Arrange: Create Tenant, Supplier, and Product
        $tenant = Tenant::create([
            'id' => 'tenant_777',
            'name' => 'Supplier Test Tenant',
            'subscription_tier' => 'premium',
            'status' => 'active'
        ]);

        $supplier = Supplier::create([
            'tenant_id' => 'tenant_777',
            'name' => 'Harvest Distributors',
            'email' => 'harvest@example.com',
            'phone' => '0771234567',
            'alert_channel' => 'WhatsApp'
        ]);

        $product = Product::create([
            'tenant_id' => 'tenant_777',
            'sku' => 'SKU-POTEST-01',
            'name' => 'Wheat Seedlings',
            'category' => 'Seeds',
            'price' => 500.00,
            'current_stock' => 10,
            'safety_stock' => 20,
            'max_stock' => 100,
            'reorder_qty' => 50,
            'lead_time_days' => 4,
            'supplier_id' => $supplier->id
        ]);

        // 2. Act & Assert: Dispatch PO
        $dispatchResponse = $this->withHeaders([
            'X-Tenant-ID' => 'tenant_777'
        ])->postJson('/api/purchase-orders', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'quantity' => 50,
            'message_body' => 'Please deliver 50 units of Wheat Seedlings urgently.'
        ]);

        $dispatchResponse->assertStatus(201);
        $dispatchResponse->assertJsonFragment(['status' => 'success']);
        
        $poId = $dispatchResponse->json('data.id');
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $poId,
            'tenant_id' => 'tenant_777',
            'product_id' => $product->id,
            'status' => 'dispatched'
        ]);

        // 3. Act & Assert: List POs
        $listResponse = $this->withHeaders([
            'X-Tenant-ID' => 'tenant_777'
        ])->getJson('/api/purchase-orders');

        $listResponse->assertStatus(200);
        $listResponse->assertJsonFragment(['id' => $poId]);

        // 4. Act & Assert: Receive PO and replenish stock
        $receiveResponse = $this->withHeaders([
            'X-Tenant-ID' => 'tenant_777'
        ])->postJson("/api/purchase-orders/{$poId}/receive");

        $receiveResponse->assertStatus(200);
        $receiveResponse->assertJsonFragment(['status' => 'success']);

        $product->refresh();
        $this->assertEquals(60, $product->current_stock); // 10 + 50
        
        $po = PurchaseOrder::find($poId);
        $this->assertEquals('received', $po->status);
    }
}
