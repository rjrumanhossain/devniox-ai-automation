<?php

namespace Tests\Feature;

use Tests\TestCase;

class SaasApiTest extends TestCase
{
    public function test_overview_endpoint_returns_saas_dashboard_payload(): void
    {
        $this->getJson('/api/v1/overview')
            ->assertOk()
            ->assertJsonStructure([
                'super_admin' => [
                    'metrics',
                    'plans',
                    'clients',
                    'channels',
                    'providers',
                    'automation',
                ],
                'customer' => [
                    'business',
                    'metrics',
                    'channels',
                    'providers',
                    'automation',
                    'reply_flow',
                ],
            ])
            ->assertJsonPath('super_admin.channels.0.name', 'Messenger')
            ->assertJsonPath('customer.providers.0.name', 'OpenAI');
    }

    public function test_role_specific_endpoints_return_role_payloads(): void
    {
        $this->getJson('/api/v1/super-admin/overview')
            ->assertOk()
            ->assertJsonPath('role', 'super_admin')
            ->assertJsonPath('clients.0.name', 'BoneekBD');

        $this->getJson('/api/v1/customer/overview')
            ->assertOk()
            ->assertJsonPath('role', 'customer')
            ->assertJsonPath('business.name', 'BoneekBD')
            ->assertJsonPath('channels.3.name', 'Website Chat');
    }

    public function test_documentation_endpoint_returns_setup_sections(): void
    {
        $this->getJson('/api/v1/documentation')
            ->assertOk()
            ->assertJsonPath('sections.0.title', 'Role Setup')
            ->assertJsonCount(5, 'sections');
    }
}
