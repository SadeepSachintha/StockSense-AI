<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Product;
use App\Services\TenantManager;

class MultiTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    protected TenantManager $tenantManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenantManager = app(TenantManager::class);
    }

    /** @test */
    public function it_automatically_scopes_and_filters_products_by_active_tenant(): void
    {
        // 1. Create two test tenants
        $tenantA = Tenant::create([
            'id' => 'tenant_a',
            'name' => 'Tenant A Retailers',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);

        $tenantB = Tenant::create([
            'id' => 'tenant_b',
            'name' => 'Tenant B Distributors',
            'subscription_tier' => 'premium',
            'status' => 'active'
        ]);

        // 2. Set active tenant to Tenant A and insert a product
        $this->tenantManager->setTenantId('tenant_a');
        
        $productA = Product::create([
            'sku' => 'SKU-AAA-001',
            'name' => 'Product A',
            'category' => 'Test Grains',
            'price' => 10.99,
            'elasticity' => -1.2,
            'current_stock' => 100,
            'safety_stock' => 50,
            'max_stock' => 500,
            'reorder_qty' => 100,
            'lead_time_days' => 3
        ]);

        // Verify that tenant_id was automatically populated
        $this->assertEquals('tenant_a', $productA->tenant_id);

        // 3. Set active tenant to Tenant B and insert a product
        $this->tenantManager->setTenantId('tenant_b');

        $productB = Product::create([
            'sku' => 'SKU-BBB-002',
            'name' => 'Product B',
            'category' => 'Test Agrochemicals',
            'price' => 24.50,
            'elasticity' => -0.8,
            'current_stock' => 200,
            'safety_stock' => 40,
            'max_stock' => 400,
            'reorder_qty' => 80,
            'lead_time_days' => 5
        ]);

        // Verify that tenant_id was automatically populated for Tenant B
        $this->assertEquals('tenant_b', $productB->tenant_id);

        // 4. Test Query Filters: Active Tenant = Tenant A
        $this->tenantManager->setTenantId('tenant_a');
        
        $activeProducts = Product::all();
        $this->assertCount(1, $activeProducts);
        $this->assertEquals('Product A', $activeProducts->first()->name);

        // 5. Test Query Filters: Active Tenant = Tenant B
        $this->tenantManager->setTenantId('tenant_b');

        $activeProducts = Product::all();
        $this->assertCount(1, $activeProducts);
        $this->assertEquals('Product B', $activeProducts->first()->name);
    }

    /** @test */
    public function it_can_batch_create_products_scoped_to_the_active_tenant(): void
    {
        $tenantA = Tenant::create([
            'id' => 'tenant_a',
            'name' => 'Tenant A Retailers',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);

        \App\Models\Supplier::create([
            'id' => 1,
            'tenant_id' => 'tenant_a',
            'name' => 'AgriBulk Corp',
            'contact_name' => 'John Agri',
            'email' => 'agribulk@test.com',
            'phone' => '123456'
        ]);
        
        \App\Models\Supplier::create([
            'id' => 2,
            'tenant_id' => 'tenant_a',
            'name' => 'GrownOrganic Farms',
            'contact_name' => 'Jane Organic',
            'email' => 'organic@test.com',
            'phone' => '654321'
        ]);

        $response = $this->withSession(['tenant_id' => 'tenant_a'])
            ->postJson('/api/products', [
                'products' => [
                    [
                        'sku' => 'SKU-BATCH-1',
                        'name' => 'Batch Product 1',
                        'category' => 'Batch',
                        'price' => 12.00,
                        'current_stock' => 50,
                        'safety_stock' => 10,
                        'max_stock' => 100,
                        'reorder_qty' => 30,
                        'lead_time_days' => 4,
                        'supplier_id' => 1,
                    ],
                    [
                        'sku' => 'SKU-BATCH-2',
                        'name' => 'Batch Product 2',
                        'category' => 'Batch',
                        'price' => 15.50,
                        'current_stock' => 60,
                        'safety_stock' => 12,
                        'max_stock' => 120,
                        'reorder_qty' => 40,
                        'lead_time_days' => 5,
                        'supplier_id' => 2,
                    ]
                ]
            ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['status' => 'success']);

        $this->tenantManager->setTenantId('tenant_a');
        $this->assertCount(2, Product::all());
    }

    /** @test */
    public function it_can_replenish_product_stock(): void
    {
        $tenant = Tenant::create([
            'id' => 'tenant_a',
            'name' => 'Tenant A Retailers',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);

        $this->tenantManager->setTenantId('tenant_a');
        $product = Product::create([
            'sku' => 'SKU-REPL-1',
            'name' => 'Replenish Test Product',
            'category' => 'Grains',
            'price' => 5.00,
            'current_stock' => 10,
            'safety_stock' => 5,
            'max_stock' => 100,
            'reorder_qty' => 30,
            'lead_time_days' => 4
        ]);

        $response = $this->withSession(['tenant_id' => 'tenant_a'])
            ->putJson("/api/products/{$product->id}/replenish", [
                'quantity' => 40
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['status' => 'success']);

        $product->refresh();
        $this->assertEquals(50, $product->current_stock);
    }

    /** @test */
    public function it_upserts_product_stock_if_sku_already_exists(): void
    {
        $tenant = Tenant::create([
            'id' => 'tenant_a',
            'name' => 'Tenant A Retailers',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);

        \App\Models\Supplier::create([
            'id' => 1,
            'tenant_id' => 'tenant_a',
            'name' => 'AgriBulk Corp',
            'contact_name' => 'John Agri',
            'email' => 'agribulk@test.com',
            'phone' => '123456'
        ]);

        $this->tenantManager->setTenantId('tenant_a');
        $product = Product::create([
            'sku' => 'SKU-UPSERT-1',
            'name' => 'Initial Product',
            'category' => 'Grains',
            'price' => 5.00,
            'current_stock' => 10,
            'safety_stock' => 5,
            'max_stock' => 100,
            'reorder_qty' => 30,
            'lead_time_days' => 4,
            'supplier_id' => 1
        ]);

        $response = $this->withSession(['tenant_id' => 'tenant_a'])
            ->postJson("/api/products", [
                'sku' => 'SKU-UPSERT-1',
                'name' => 'Updated Product Name',
                'category' => 'Grains',
                'price' => 6.50,
                'current_stock' => 20,
                'safety_stock' => 10,
                'max_stock' => 150,
                'reorder_qty' => 40,
                'lead_time_days' => 5,
                'supplier_id' => 1
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['status' => 'success']);

        $product->refresh();
        // Unchanged SKU, but stock increments by 20 -> 30, and name is updated!
        $this->assertEquals(30, $product->current_stock);
        $this->assertEquals('Updated Product Name', $product->name);
        $this->assertEquals(6.50, $product->price);
        $this->assertCount(1, Product::all());
    }
}
