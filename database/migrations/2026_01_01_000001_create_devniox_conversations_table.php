<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devniox_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->index();
            $table->string('external_id')->nullable()->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('customer_id')->nullable()->index();
            $table->string('status')->default('open');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('devniox_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('devniox_conversations')->cascadeOnDelete();
            $table->string('channel')->index();
            $table->string('direction')->default('incoming');
            $table->string('sender_id')->nullable()->index();
            $table->string('provider_message_id')->nullable()->index();
            $table->text('content');
            $table->string('status')->default('queued');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('devniox_customers', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->index();
            $table->string('channel')->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('devniox_leads', function (Blueprint $table) {
            $table->id();
            $table->string('source')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('status')->default('new');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('devniox_knowledge_items', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable();
            $table->string('title');
            $table->longText('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('devniox_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->longText('answer');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('devniox_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('devniox_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('devniox_channel_connections', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->index();
            $table->string('connection_name')->nullable();
            $table->text('credentials_encrypted')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('devniox_automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('event');
            $table->json('conditions')->nullable();
            $table->json('actions')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('devniox_conversation_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('devniox_conversations')->cascadeOnDelete();
            $table->string('author_type')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->text('note');
            $table->timestamps();
        });

        Schema::create('devniox_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->index();
            $table->string('provider_event_id')->nullable()->unique();
            $table->json('payload');
            $table->string('status')->default('received');
            $table->timestamps();
        });

        Schema::create('devniox_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event');
            $table->string('channel')->nullable();
            $table->string('actor_type')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devniox_audit_logs');
        Schema::dropIfExists('devniox_webhook_events');
        Schema::dropIfExists('devniox_conversation_notes');
        Schema::dropIfExists('devniox_automation_rules');
        Schema::dropIfExists('devniox_channel_connections');
        Schema::dropIfExists('devniox_services');
        Schema::dropIfExists('devniox_products');
        Schema::dropIfExists('devniox_faqs');
        Schema::dropIfExists('devniox_knowledge_items');
        Schema::dropIfExists('devniox_leads');
        Schema::dropIfExists('devniox_customers');
        Schema::dropIfExists('devniox_messages');
        Schema::dropIfExists('devniox_conversations');
    }
};
