<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('billing_cycle')->default('monthly');
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('message_limit')->default(0);
            $table->unsignedInteger('channel_limit')->default(1);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('businesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('trial');
            $table->string('timezone')->default('Asia/Dhaka');
            $table->timestamps();
        });

        Schema::create('business_api_keys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('key_prefix', 32)->index();
            $table->string('key_hash');
            $table->json('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('channel_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('external_id')->nullable()->index();
            $table->string('display_name')->nullable();
            $table->text('credential_payload')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_provider_credentials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('mode')->default('platform_key');
            $table->text('credential_payload')->nullable();
            $table->unsignedInteger('monthly_token_limit')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('usage_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_connection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('cost', 12, 4)->default(0);
            $table->timestamp('period_started_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_ledgers');
        Schema::dropIfExists('ai_provider_credentials');
        Schema::dropIfExists('channel_connections');
        Schema::dropIfExists('business_api_keys');
        Schema::dropIfExists('businesses');
        Schema::dropIfExists('plans');
    }
};
