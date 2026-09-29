<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_login_and_receive_super_admin_portal(): void
    {
        $this->seed();

        $this->postJson('/portal-api/login', [
            'email' => 'admin@admin.com',
            'password' => '12345678',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', 'super_admin');

        $this->getJson('/portal-api/overview')
            ->assertOk()
            ->assertJsonPath('role', 'super_admin')
            ->assertJsonPath('clients.0.name', 'BoneekBD');
    }

    public function test_customer_can_login_and_receive_customer_portal(): void
    {
        $this->seed();

        $this->postJson('/portal-api/login', [
            'email' => 'rumank@gmail.com',
            'password' => '12345678',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', 'customer');

        $this->getJson('/portal-api/overview')
            ->assertOk()
            ->assertJsonPath('role', 'customer')
            ->assertJsonPath('business.name', 'BoneekBD')
            ->assertJsonPath('business.api_key_prefix', 'dnx_live_boneekbd');
    }

    public function test_invalid_login_is_rejected(): void
    {
        $this->seed();

        $this->postJson('/portal-api/login', [
            'email' => 'admin@admin.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }
}
