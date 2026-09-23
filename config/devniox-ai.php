<?php

declare(strict_types=1);

return [
    'enabled' => env('AI_AUTOMATION_ENABLED', true),
    'provider' => env('AI_AUTOMATION_AI_PROVIDER', 'openai'),
    'owner_model' => env('AI_AUTOMATION_OWNER_MODEL', null),
    'routes' => [
        'prefix' => env('AI_AUTOMATION_ROUTE_PREFIX', 'devniox-ai'),
        'webhook_prefix' => env('AI_AUTOMATION_WEBHOOK_PREFIX', 'webhooks'),
    ],
    'ai' => [
        'provider' => env('AI_AUTOMATION_AI_PROVIDER', 'openai'),
        'api_key' => env('AI_AUTOMATION_AI_API_KEY'),
        'model' => env('AI_AUTOMATION_AI_MODEL', 'gpt-4o-mini'),
        'default_language' => env('AI_AUTOMATION_DEFAULT_LANGUAGE', 'en'),
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
