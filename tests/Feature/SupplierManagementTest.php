<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Supplier;
use App\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant1;
    private Tenant $tenant2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant1 = Tenant::create([
            'id' => 'tenant_test_1',
            'name' => 'Store 1',
            'company_name' => 'Store 1 LLC',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);

        $this->tenant2 = Tenant::create([
            'id' => 'tenant_test_2',
            'name' => 'Store 2',
            'company_name' => 'Store 2 LLC',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_scopes_supplier_retrieval_to_the_active_tenant(): void
    {
        // Bind tenant 1 context and create a supplier
        app(TenantManager::class)->setTenantId('tenant_test_1');
        Supplier::create([
            'name' => 'Supplier for Tenant 1',
            'email' => 'sup1@example.com'
        ]);

        // Bind tenant 2 context and create a supplier
        app(TenantManager::class)->setTenantId('tenant_test_2');
        Supplier::create([
            'name' => 'Supplier for Tenant 2',
            'email' => 'sup2@example.com'
        ]);

        // Query list as Tenant 1
        $response = $this->getJson('/api/suppliers', [
            'X-Tenant-ID' => 'tenant_test_1'
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Supplier for Tenant 1');

        // Query list as Tenant 2
        $response = $this->getJson('/api/suppliers', [
            'X-Tenant-ID' => 'tenant_test_2'
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Supplier for Tenant 2');
    }

    /** @test */
    public function it_can_register_new_supplier_under_tenant_scope(): void
    {
        $payload = [
            'name' => 'Global Organic Farm Co',
            'email' => 'orders@globalorganic.com',
            'phone' => '+18880199',
            'alert_channel' => 'WhatsApp'
        ];

        $response = $this->postJson('/api/suppliers', $payload, [
            'X-Tenant-ID' => 'tenant_test_1'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Supplier registered successfully.'
            ]);

        $this->assertDatabaseHas('suppliers', [
            'tenant_id' => 'tenant_test_1',
            'name' => 'Global Organic Farm Co',
            'alert_channel' => 'WhatsApp'
        ]);
    }
}
