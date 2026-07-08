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
}
