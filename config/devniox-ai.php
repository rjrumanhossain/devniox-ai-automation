<?php

declare(strict_types=1);

return [
    'enabled' => env('AI_AUTOMATION_ENABLED', true),
    'provider' => env('AI_AUTOMATION_AI_PROVIDER', 'openai'),
    'owner_model' => env('AI_AUTOMATION_OWNER_MODEL', null),
    'routes' => [
        'prefix' => env('AI_AUTOMATION_ROUTE_PREFIX', 'devniox-ai'),
        'webhook_prefix' => env('AI_AUTOMATION_WEBHOOK_PREFIX', 'webhooks'),
        'admin_prefix' => env('AI_AUTOMATION_ADMIN_PREFIX', 'admin'),
        'admin_middleware' => array_filter(explode(',', env('AI_AUTOMATION_ADMIN_MIDDLEWARE', ''))),
    ],
    'admin' => [
        'layout' => env('AI_AUTOMATION_ADMIN_LAYOUT', 'devniox-ai::admin.layout'),
    ],
    'ai' => [
        'provider' => env('AI_AUTOMATION_AI_PROVIDER', 'openai'),
        'api_key' => env('AI_AUTOMATION_AI_API_KEY'),
        'model' => env('AI_AUTOMATION_AI_MODEL', 'gpt-4o-mini'),
        'default_language' => env('AI_AUTOMATION_DEFAULT_LANGUAGE', 'en'),
        'use_ai_fallback' => env('AI_AUTOMATION_USE_AI_FALLBACK', true),
        'response' => [
            'max_tokens' => (int) env('AI_AUTOMATION_MAX_TOKENS', 300),
            'temperature' => (float) env('AI_AUTOMATION_TEMPERATURE', 0.7),
        ],
        'system_instructions' => env('AI_AUTOMATION_SYSTEM_INSTRUCTIONS', 'You are a helpful customer support assistant.'),
    ],
    'queue' => [
        'connection' => env('AI_AUTOMATION_QUEUE_CONNECTION', 'sync'),
        'name' => env('AI_AUTOMATION_QUEUE_NAME', 'devniox-ai'),
    ],
    'webhook' => [
        'secret' => env('AI_AUTOMATION_WEBHOOK_SECRET'),
        'verify' => env('AI_AUTOMATION_WEBHOOK_VERIFY', true),
    ],
    'whatsapp' => [
        'enabled' => env('AI_AUTOMATION_WHATSAPP_ENABLED', false),
        'token' => env('AI_AUTOMATION_WHATSAPP_TOKEN'),
        'phone_id' => env('AI_AUTOMATION_WHATSAPP_PHONE_ID'),
        'webhook_secret' => env('AI_AUTOMATION_WHATSAPP_WEBHOOK_SECRET'),
        'api_url' => env('AI_AUTOMATION_WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0'),
        'retry_attempts' => (int) env('AI_AUTOMATION_WHATSAPP_RETRY_ATTEMPTS', 3),
    ],
    'messenger' => [
        'enabled' => env('AI_AUTOMATION_MESSENGER_ENABLED', false),
        'page_access_token' => env('AI_AUTOMATION_MESSENGER_PAGE_ACCESS_TOKEN'),
        'app_secret' => env('AI_AUTOMATION_MESSENGER_APP_SECRET'),
        'verify_token' => env('AI_AUTOMATION_MESSENGER_VERIFY_TOKEN'),
        'api_url' => env('AI_AUTOMATION_MESSENGER_API_URL', 'https://graph.facebook.com/v18.0'),
        'retry_attempts' => (int) env('AI_AUTOMATION_MESSENGER_RETRY_ATTEMPTS', 3),
    ],
    'website' => [
        'enabled' => env('AI_AUTOMATION_WEBSITE_ENABLED', true),
        'rate_limit' => (int) env('AI_AUTOMATION_WEBSITE_RATE_LIMIT', 60),
        'lead_capture' => env('AI_AUTOMATION_WEBSITE_LEAD_CAPTURE', true),
    ],
    'business' => [
        'name' => env('AI_AUTOMATION_BUSINESS_NAME', 'Your Business'),
        'description' => env('AI_AUTOMATION_BUSINESS_DESCRIPTION', 'Customer support automation.'),
        'support_hours' => env('AI_AUTOMATION_SUPPORT_HOURS', 'Business hours'),
        'contact_email' => env('AI_AUTOMATION_CONTACT_EMAIL'),
        'contact_phone' => env('AI_AUTOMATION_CONTACT_PHONE'),
    ],
    'handoff' => [
        'enabled' => env('AI_AUTOMATION_HANDOFF_ENABLED', true),
        'message' => env('AI_AUTOMATION_HANDOFF_MESSAGE', 'I can connect you with our support team.'),
        'whatsapp_number' => env('AI_AUTOMATION_HANDOFF_WHATSAPP_NUMBER'),
        'messenger_url' => env('AI_AUTOMATION_HANDOFF_MESSENGER_URL'),
        'email' => env('AI_AUTOMATION_HANDOFF_EMAIL'),
        'phone' => env('AI_AUTOMATION_HANDOFF_PHONE'),
    ],
    'answering' => [
        'local_first' => env('AI_AUTOMATION_LOCAL_FIRST', true),
        'max_history_messages' => (int) env('AI_AUTOMATION_MAX_HISTORY_MESSAGES', 6),
        'knowledge_excerpt_length' => (int) env('AI_AUTOMATION_KNOWLEDGE_EXCERPT_LENGTH', 900),
    ],
    'commerce' => [
        'enabled' => env('AI_AUTOMATION_COMMERCE_ENABLED', true),
        'table' => env('AI_AUTOMATION_COMMERCE_TABLE', 'products'),
        'name_column' => env('AI_AUTOMATION_COMMERCE_NAME_COLUMN', 'name'),
        'slug_column' => env('AI_AUTOMATION_COMMERCE_SLUG_COLUMN', 'slug'),
        'price_column' => env('AI_AUTOMATION_COMMERCE_PRICE_COLUMN', 'new_price'),
        'old_price_column' => env('AI_AUTOMATION_COMMERCE_OLD_PRICE_COLUMN', 'old_price'),
        'stock_column' => env('AI_AUTOMATION_COMMERCE_STOCK_COLUMN', 'stock'),
        'status_column' => env('AI_AUTOMATION_COMMERCE_STATUS_COLUMN', 'status'),
        'active_value' => env('AI_AUTOMATION_COMMERCE_ACTIVE_VALUE', 1),
        'product_url' => env('AI_AUTOMATION_COMMERCE_PRODUCT_URL', '/product/{slug}'),
    ],
    'encryption' => [
        'keys' => [
            'whatsapp' => env('AI_AUTOMATION_ENCRYPTION_WHATSAPP_KEY'),
            'messenger' => env('AI_AUTOMATION_ENCRYPTION_MESSENGER_KEY'),
        ],
    ],
    'logging' => [
        'enabled' => env('AI_AUTOMATION_LOGGING_ENABLED', true),
        'channel' => env('AI_AUTOMATION_LOG_CHANNEL', 'stack'),
    ],
    'tenant' => [
        'enabled' => env('AI_AUTOMATION_TENANT_MODE', false),
        'resolver' => null,
    ],
    'owner' => [
        'model' => env('AI_AUTOMATION_OWNER_MODEL'),
    ],
];
