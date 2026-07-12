<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubscriptionTierTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $freeTenant;
    private Tenant $basicTenant;
    private Tenant $premiumTenant;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenants
        $this->freeTenant = Tenant::create([
            'id' => 'tenant_free',
            'name' => 'Free Store',
            'subscription_tier' => 'free',
            'status' => 'active'
        ]);

        $this->basicTenant = Tenant::create([
            'id' => 'tenant_basic',
            'name' => 'Basic Store',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);

        $this->premiumTenant = Tenant::create([
            'id' => 'tenant_premium',
            'name' => 'Premium Store',
            'subscription_tier' => 'premium',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_blocks_free_tenants_from_accessing_basic_and_premium_api_routes()
    {
        // 1. Arrange: Create product under free tenant scope
        app(\App\Services\TenantManager::class)->setTenantId('tenant_free');
        $supplier = Supplier::create([
            'name' => 'Supplier Bulk'
        ]);
        $product = Product::create([
            'sku' => 'SKU-FREE-001',
            'name' => 'Basmati Rice',
            'category' => 'Grains',
            'price' => 10.00,
            'elasticity' => -1.2,
            'current_stock' => 100,
            'safety_stock' => 20,
            'max_stock' => 500,
            'reorder_qty' => 50,
            'lead_time_days' => 5,
            'supplier_id' => $supplier->id
        ]);

        // 2. Act & Assert: Basic route blocks Free tenant
        $response1 = $this->withSession(['tenant_id' => 'tenant_free'])
            ->putJson("/api/products/{$product->id}/price", ['price' => 12.00]);
        $response1->assertStatus(403);

        // 3. Act & Assert: Premium route blocks Free tenant
        $response2 = $this->withSession(['tenant_id' => 'tenant_free'])
            ->getJson("/api/products/{$product->id}/forecast");
        $response2->assertStatus(403);
    }

    /** @test */
    public function it_allows_basic_tenants_to_access_basic_routes_but_blocks_premium_routes()
    {
        // 1. Arrange: Create product under basic tenant scope
        app(\App\Services\TenantManager::class)->setTenantId('tenant_basic');
        $supplier = Supplier::create([
            'name' => 'Supplier Bulk'
        ]);
        $product = Product::create([
            'sku' => 'SKU-BASIC-001',
            'name' => 'Basmati Rice',
            'category' => 'Grains',
            'price' => 10.00,
            'elasticity' => -1.2,
            'current_stock' => 100,
            'safety_stock' => 20,
            'max_stock' => 500,
            'reorder_qty' => 50,
            'lead_time_days' => 5,
            'supplier_id' => $supplier->id
        ]);

        // 2. Act & Assert: Basic route succeeds for Basic tenant
        $response1 = $this->withSession(['tenant_id' => 'tenant_basic'])
            ->putJson("/api/products/{$product->id}/price", ['price' => 12.00]);
        $response1->assertStatus(200);

        // 3. Act & Assert: Premium route blocks Basic tenant
        $response2 = $this->withSession(['tenant_id' => 'tenant_basic'])
            ->getJson("/api/products/{$product->id}/forecast");
        $response2->assertStatus(403);
    }

    /** @test */
    public function it_allows_premium_tenants_to_access_all_api_routes()
    {
        // Mock FastAPI response at start of test
        Http::fake([
            '*/predict' => Http::response([
                'status' => 'success',
                'safety_stock' => 45,
                'reorder_point' => 180,
                'wmape' => 3.8,
                'forecast' => []
            ], 200)
        ]);

        // 1. Arrange: Create product under premium tenant scope
        app(\App\Services\TenantManager::class)->setTenantId('tenant_premium');
        $supplier = Supplier::create([
            'name' => 'Supplier Bulk'
        ]);
        $product = Product::create([
            'sku' => 'SKU-PREM-001',
            'name' => 'Basmati Rice',
            'category' => 'Grains',
            'price' => 10.00,
            'elasticity' => -1.2,
            'current_stock' => 100,
            'safety_stock' => 20,
            'max_stock' => 500,
            'reorder_qty' => 50,
            'lead_time_days' => 5,
            'supplier_id' => $supplier->id
        ]);

        // 2. Act & Assert: Basic route succeeds for Premium tenant
        $response1 = $this->withSession(['tenant_id' => 'tenant_premium'])
            ->putJson("/api/products/{$product->id}/price", ['price' => 12.00]);
        $response1->assertStatus(200);

        // 3. Act & Assert: Premium route succeeds for Premium tenant
        // (FastAPI returns fake forecast data in test environment)
        $response2 = $this->withSession(['tenant_id' => 'tenant_premium'])
            ->getJson("/api/products/{$product->id}/forecast");
        $response2->assertStatus(200);
    }
}
