<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Tests;

use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase
{
    public function test_migrations_are_present_and_create_expected_tables(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $expected = [
            'devniox_conversations',
            'devniox_messages',
            'devniox_customers',
            'devniox_leads',
            'devniox_knowledge_items',
            'devniox_faqs',
            'devniox_products',
            'devniox_services',
            'devniox_channel_connections',
            'devniox_automation_rules',
            'devniox_conversation_notes',
            'devniox_webhook_events',
            'devniox_audit_logs',
        ];

        foreach ($expected as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }
    }
}
