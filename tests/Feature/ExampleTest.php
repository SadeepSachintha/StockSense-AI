<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guests_are_redirected_to_cashier_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_cashier_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_cashier_can_access_dashboard(): void
    {
        $response = $this->withSession(['tenant_id' => 'tenant_001'])->get('/');
        $response->assertStatus(200);
    }

    public function test_guests_are_redirected_to_admin_login(): void
    {
        $response = $this->get('/saas-admin');
        $response->assertRedirect('/saas-admin/login');
    }

    public function test_admin_login_page_renders_successfully(): void
    {
        $response = $this->get('/saas-admin/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_admin_can_access_console(): void
    {
        $response = $this->withSession(['admin_logged_in' => true])->get('/saas-admin');
        $response->assertStatus(200);
    }
}
