<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateConversations();
        $this->updateScopedTables();
        $this->updateFaqs();
        $this->updateWebhookEvents();
        $this->createSettingsTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('devniox_ai_settings');
    }

    protected function updateConversations(): void
    {
        if (! Schema::hasTable('devniox_conversations')) {
            return;
        }

        Schema::table('devniox_conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('devniox_conversations', 'uuid')) {
                $table->string('uuid')->nullable()->unique()->after('id');
            }

            $this->scopeColumns($table, 'devniox_conversations', 'external_id');
        });
    }

    protected function updateScopedTables(): void
    {
        $tables = [
            'devniox_customers' => 'id',
            'devniox_leads' => 'id',
            'devniox_knowledge_items' => 'id',
            'devniox_products' => 'id',
            'devniox_services' => 'id',
            'devniox_channel_connections' => 'id',
            'devniox_automation_rules' => 'id',
            'devniox_audit_logs' => 'id',
        ];

        foreach ($tables as $tableName => $after) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($after, $tableName) {
                $this->scopeColumns($table, $tableName, $after);
            });
        }
    }

    protected function updateFaqs(): void
    {
        if (! Schema::hasTable('devniox_faqs')) {
            return;
        }

        Schema::table('devniox_faqs', function (Blueprint $table) {
            $this->scopeColumns($table, 'devniox_faqs', 'id');

            if (! Schema::hasColumn('devniox_faqs', 'keywords')) {
                $table->json('keywords')->nullable()->after('answer');
            }
        });
    }

    protected function updateWebhookEvents(): void
    {
        if (! Schema::hasTable('devniox_webhook_events')) {
            return;
        }

        Schema::table('devniox_webhook_events', function (Blueprint $table) {
            $this->scopeColumns($table, 'devniox_webhook_events', 'id');

            if (! Schema::hasColumn('devniox_webhook_events', 'signature')) {
                $table->string('signature')->nullable();
            }
        });
    }

    protected function createSettingsTable(): void
    {
        if (Schema::hasTable('devniox_ai_settings')) {
            return;
        }

        Schema::create('devniox_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_key', 120)->nullable()->index();
            $table->string('owner_type', 120)->nullable()->index();
            $table->unsignedBigInteger('owner_id')->nullable()->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('key', 120)->index();
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['business_key', 'owner_type', 'owner_id', 'tenant_id', 'key'], 'devniox_settings_scope_key_unique');
        });
    }

    protected function scopeColumns(Blueprint $table, string $tableName, string $after): void
    {
        if (! Schema::hasColumn($tableName, 'business_key')) {
            $table->string('business_key')->nullable()->index();
        }

        if (! Schema::hasColumn($tableName, 'owner_type')) {
            $table->string('owner_type')->nullable()->index();
        }

        if (! Schema::hasColumn($tableName, 'owner_id')) {
            $table->unsignedBigInteger('owner_id')->nullable()->index();
        }

        if (! Schema::hasColumn($tableName, 'tenant_id')) {
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
        }
    }
};
