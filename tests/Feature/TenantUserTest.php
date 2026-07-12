<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user authentication and role mapping to sessions.
     *
     * @test
     */
    public function it_can_authenticate_users_and_assign_roles()
    {
        // 1. Arrange: Create Tenant and TenantUsers
        $tenant = Tenant::create([
            'id' => 'tenant_abc',
            'name' => 'ABC Retail',
            'subscription_tier' => 'basic',
            'status' => 'active'
        ]);

        $owner = TenantUser::create([
            'tenant_id' => 'tenant_abc',
            'username' => 'owner_user',
            'password' => \Hash::make('secret123'),
            'role' => 'owner',
            'name' => 'Owner Admin'
        ]);

        $cashier = TenantUser::create([
            'tenant_id' => 'tenant_abc',
            'username' => 'cashier_user',
            'password' => \Hash::make('secret456'),
            'role' => 'cashier',
            'name' => 'Cashier Front'
        ]);

        // 2. Act & Assert: Try logging in with wrong password
        $failResponse = $this->post('/login', [
            'tenant_id' => 'tenant_abc',
            'username' => 'owner_user',
            'password' => 'wrongpassword'
        ]);
        $failResponse->assertSessionHasErrors('password');

        // 3. Act & Assert: Log in as owner successfully
        $ownerResponse = $this->post('/login', [
            'tenant_id' => 'tenant_abc',
            'username' => 'owner_user',
            'password' => 'secret123'
        ]);
        $ownerResponse->assertRedirect('/');
        $this->assertEquals('tenant_abc', session('tenant_id'));
        $this->assertEquals('owner', session('user_role'));
        $this->assertEquals('Owner Admin', session('user_name'));

        // Clear session
        session()->flush();

        // 4. Act & Assert: Log in as cashier successfully
        $cashierResponse = $this->post('/login', [
            'tenant_id' => 'tenant_abc',
            'username' => 'cashier_user',
            'password' => 'secret456'
        ]);
        $cashierResponse->assertRedirect('/');
        $this->assertEquals('tenant_abc', session('tenant_id'));
        $this->assertEquals('cashier', session('user_role'));
        $this->assertEquals('Cashier Front', session('user_name'));
    }
}
