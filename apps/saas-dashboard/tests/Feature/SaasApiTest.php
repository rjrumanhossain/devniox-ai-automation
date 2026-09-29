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
                'metrics',
                'plans',
                'clients',
                'channels',
                'providers',
                'automation',
            ])
            ->assertJsonPath('channels.0.name', 'Messenger')
            ->assertJsonPath('providers.0.name', 'OpenAI');
    }

    public function test_documentation_endpoint_returns_setup_sections(): void
    {
        $this->getJson('/api/v1/documentation')
            ->assertOk()
            ->assertJsonPath('sections.0.title', 'Client Setup')
            ->assertJsonCount(4, 'sections');
    }
}
