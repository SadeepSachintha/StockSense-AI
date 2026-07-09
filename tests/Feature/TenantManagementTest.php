<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_tenants(): void
    {
        Tenant::create([
            'id' => 'tenant_test_1',
            'name' => 'John Doe',
            'company_name' => 'Doe Enterprises',
            'subscription_tier' => 'premium',
            'status' => 'active'
        ]);

        $response = $this->getJson('/api/tenants');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'name', 'company_name', 'subscription_tier', 'status']
                ]
            ]);
    }

    /** @test */
    public function it_can_register_a_new_tenant(): void
    {
        $payload = [
            'id' => 'new_tenant_123',
            'name' => 'Alice Cooper',
            'company_name' => 'Cooper Agribusiness',
            'subscription_tier' => 'basic',
            'status' => 'active',
            'tax_rate' => 15.00
        ];

        $response = $this->postJson('/api/tenants', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Tenant registered successfully.'
            ]);

        $this->assertDatabaseHas('tenants', [
            'id' => 'new_tenant_123',
            'name' => 'Alice Cooper',
            'tax_rate' => 15.00
        ]);
    }

    /** @test */
    public function it_can_show_a_tenant_details(): void
    {
        $tenant = Tenant::create([
            'id' => 'tenant_test_show',
            'name' => 'Bob Marley',
            'company_name' => 'Marley Agri',
            'subscription_tier' => 'free',
            'status' => 'active'
        ]);

        $response = $this->getJson("/api/tenants/{$tenant->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => 'tenant_test_show',
                    'name' => 'Bob Marley'
                ]
            ]);
    }

    /** @test */
    public function it_can_update_a_tenant(): void
    {
        $tenant = Tenant::create([
            'id' => 'tenant_test_update',
            'name' => 'Bob Marley',
            'company_name' => 'Marley Agri',
            'subscription_tier' => 'free',
            'status' => 'active'
        ]);

        $payload = [
            'name' => 'Robert Nesta Marley',
            'subscription_tier' => 'premium',
            'tax_rate' => 0.00
        ];

        $response = $this->putJson("/api/tenants/{$tenant->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Tenant updated successfully.'
            ]);

        $this->assertDatabaseHas('tenants', [
            'id' => 'tenant_test_update',
            'name' => 'Robert Nesta Marley',
            'subscription_tier' => 'premium',
            'tax_rate' => 0.00
        ]);
    }

    /** @test */
    public function it_can_delete_a_tenant(): void
    {
        $tenant = Tenant::create([
            'id' => 'tenant_test_delete',
            'name' => 'Delete Me',
            'company_name' => 'Temp Co',
            'subscription_tier' => 'free',
            'status' => 'active'
        ]);

        $response = $this->deleteJson("/api/tenants/{$tenant->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Tenant and associated records deleted.'
            ]);

        $this->assertDatabaseMissing('tenants', [
            'id' => 'tenant_test_delete'
        ]);
    }
}
