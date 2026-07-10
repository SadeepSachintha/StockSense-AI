<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\PosTransaction;
use Illuminate\Support\Facades\Http;

class ForecastIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_successfully_queries_fake_fastapi_and_updates_safety_stock(): void
    {
        // 1. Arrange: Setup Tenant and Product
        $tenant = Tenant::create([
            'id' => 'tenant_123',
            'name' => 'Test Tenant Retailers',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);

        $product = Product::create([
            'tenant_id' => 'tenant_123',
            'sku' => 'SKU-RICE-001',
            'name' => 'Basmati Rice',
            'category' => 'Grains',
            'price' => 14.99,
            'elasticity' => -1.4,
            'current_stock' => 150,
            'safety_stock' => 20, // Initial safety stock
            'max_stock' => 300,
            'reorder_qty' => 100,
            'lead_time_days' => 3
        ]);

        // Mock FastAPI predict response
        Http::fake([
            '*/predict' => Http::response([
                'sku_id' => 'SKU-RICE-001',
                'forecast' => [100, 105, 110, 115, 120, 125, 130, 135, 140, 145, 150, 155, 160, 165],
                'safety_stock' => 45, // Updated safety stock
                'reorder_point' => 180,
                'wmape' => 3.8,
                'message' => 'LightGBM forecast completed successfully.'
            ], 200)
        ]);

        // 2. Act: Call the controller route with X-Tenant-ID header
        $response = $this->withHeaders([
            'X-Tenant-ID' => 'tenant_123'
        ])->getJson("/api/products/{$product->id}/forecast");

        // 3. Assert: Verify response metrics
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'status' => 'success',
            'safety_stock' => 45,
            'reorder_point' => 180
        ]);

        // Verify product record in database was updated
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'safety_stock' => 45,
            'wmape' => 3.8
        ]);
    }

    /** @test */
    public function it_successfully_ingests_pos_transaction_batches_and_issues_warnings(): void
    {
        // 1. Arrange: Setup Tenant and Product
        $tenant = Tenant::create([
            'id' => 'tenant_123',
            'name' => 'Test Tenant Retailers',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);

        $product = Product::create([
            'tenant_id' => 'tenant_123',
            'sku' => 'SKU-FERT-002',
            'name' => 'NPK Fertilizer',
            'category' => 'Agri',
            'price' => 29.99,
            'elasticity' => -0.8,
            'current_stock' => 60,
            'safety_stock' => 50,
            'max_stock' => 150,
            'reorder_qty' => 50,
            'lead_time_days' => 5
        ]);

        // 2. Act: Send daily transactions batch
        $response = $this->withHeaders([
            'X-Tenant-ID' => 'tenant_123'
        ])->postJson('/api/products/ingest', [
            'transactions' => [
                [
                    'sku' => 'SKU-FERT-002',
                    'quantity' => 20, // Drops stock to 40 (below safety_stock of 50)
                    'price_sold' => 29.99,
                    'transaction_date' => '2026-07-08 18:00:00'
                ]
            ]
        ]);

        // 3. Assert: Verify warning triggered
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'warnings');
        $response->assertJsonFragment([
            'sku' => 'SKU-FERT-002',
            'current_stock' => 40,
            'message' => 'Safety limit violated. Restocking recommended.'
        ]);

        // Verify transaction is recorded and product stock updated in DB
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 40
        ]);

        $this->assertDatabaseCount('pos_transactions', 1);
    }

    /** @test */
    public function it_can_fetch_and_void_transactions(): void
    {
        $tenant = Tenant::create([
            'id' => 'tenant_123',
            'name' => 'Test Tenant Retailers',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);

        $product = Product::create([
            'tenant_id' => 'tenant_123',
            'sku' => 'SKU-TEST-VOID',
            'name' => 'Test Product',
            'category' => 'Test',
            'price' => 10.00,
            'current_stock' => 100,
            'safety_stock' => 10,
            'max_stock' => 200,
            'reorder_qty' => 50,
            'lead_time_days' => 2
        ]);

        // Ingest transaction
        $response = $this->withHeaders(['X-Tenant-ID' => 'tenant_123'])
            ->postJson('/api/products/ingest', [
                'receipt_id' => 'TX-VOID-ME',
                'discount_amount' => 5.00,
                'tax_amount' => 1.50,
                'transactions' => [
                    [
                        'sku' => 'SKU-TEST-VOID',
                        'quantity' => 10, // Stock goes to 90
                        'price_sold' => 10.00,
                        'transaction_date' => '2026-07-08 18:00:00'
                    ]
                ]
            ]);

        $response->assertStatus(200);

        // Fetch transaction history
        $historyResponse = $this->withHeaders(['X-Tenant-ID' => 'tenant_123'])
            ->getJson('/api/transactions');

        $historyResponse->assertStatus(200);
        $historyResponse->assertJsonFragment(['id' => 'TX-VOID-ME']);

        // Void the transaction
        $voidResponse = $this->withHeaders(['X-Tenant-ID' => 'tenant_123'])
            ->deleteJson('/api/transactions/TX-VOID-ME');

        $voidResponse->assertStatus(200);
        $voidResponse->assertJsonFragment(['status' => 'success']);

        // Confirm stock is refunded
        $product->refresh();
        $this->assertEquals(100, $product->current_stock);
        $this->assertDatabaseCount('pos_transactions', 0);
    }
}
