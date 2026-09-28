# Devniox AI Automation

A reusable Laravel package for low-token AI customer support automation across website chat, WhatsApp Business API, and Facebook Messenger.

## What It Does

- Answers basic customer questions from local FAQ and knowledge base first
- Falls back to an OpenAI-compatible AI provider only when local answers are not enough
- Stores conversations, messages, leads, webhook events, and channel settings
- Supports multi-admin or multi-business apps through `business_key`, `owner_id`, and `tenant_id`
- Provides headless admin APIs that can be used from Filament, Nova, a custom dashboard, or any existing admin panel
- Supports official WhatsApp/Messenger APIs when credentials are configured
- Supports non-API handoff through WhatsApp `wa.me`, Messenger URL, email, and phone

## Requirements

- PHP 8.2+
- Laravel 11 or Laravel 12 components
- Composer
- OpenAI-compatible API key for AI fallback

## Installation

After the package is available on Packagist:

```bash
composer require devniox/ai-automation
php artisan devniox-ai:install --migrate
```

For private GitHub usage before Packagist approval, add this to the host application's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/rjrumanhossain/devniox-ai-automation"
    }
  ]
}
```

Then install it:

```bash
composer require devniox/ai-automation:dev-main
php artisan devniox-ai:install --migrate
```

Manual install is also supported:

```bash
php artisan vendor:publish --tag=devniox-ai-config
php artisan vendor:publish --tag=devniox-ai-migrations
php artisan migrate
```

## Core Flow

1. Customer sends a message from the website widget, WhatsApp, or Messenger.
2. The package resolves the business/admin scope from `business_key`, request host, or a custom tenant resolver.
3. FAQ and knowledge base are searched first to reduce AI token usage.
4. If no local answer is found and AI fallback is enabled, the AI provider generates a concise response.
5. Conversation, messages, and leads are stored.
6. If official channel credentials exist, the reply can be sent back through WhatsApp/Messenger.
7. If official APIs are not connected, handoff links can send the customer to the admin's WhatsApp number, Messenger URL, email, or phone.

## Website Chat

```html
<script>
  window.DevnioxAiConfig = {
    baseUrl: '/devniox-ai',
    businessKey: 'shop-123',
    customerId: 'customer-123'
  };
</script>
<script src="/devniox-ai/widget.js"></script>
```

POST endpoint:

```http
POST /devniox-ai/chat
```

Payload:

```json
{
  "business_key": "shop-123",
  "message": "What is your delivery time?",
  "customer_name": "Jane",
  "email": "jane@example.com",
  "phone": "+15551234567"
}
```

## Admin APIs

The package is headless. Use these endpoints from any admin panel:

- `GET /devniox-ai/admin/settings`
- `POST /devniox-ai/admin/settings`
- `POST /devniox-ai/admin/channels`
- `GET /devniox-ai/admin/faqs`
- `POST /devniox-ai/admin/faqs`
- `PUT /devniox-ai/admin/faqs/{faq}`
- `DELETE /devniox-ai/admin/faqs/{faq}`
- `GET /devniox-ai/admin/knowledge`
- `POST /devniox-ai/admin/knowledge`
- `GET /devniox-ai/admin/conversations`
- `GET /devniox-ai/admin/conversations/{conversation}`
- `POST /devniox-ai/admin/conversations/{conversation}/close`

## Admin UI Setup

Open `/devniox-ai/admin/settings` in the host application's authenticated admin session. Enter the AI provider API key, choose a model, and enable AI fallback. The API key is encrypted in the database and is never returned by the settings listing API. Leaving the key field empty preserves the saved key; use the remove checkbox to delete it.

Add active FAQs and knowledge items from their admin pages. The chatbot checks local answers first when enabled, then uses the saved AI configuration when no local answer matches. Settings are scoped by the resolved business/tenant.

Environment configuration remains optional as a fallback when no database API key is saved, for example `AI_AUTOMATION_AI_API_KEY` and `AI_AUTOMATION_AI_MODEL`.

Protect admin APIs with host-app middleware:

```env
AI_AUTOMATION_ADMIN_MIDDLEWARE=web,auth
```

## FAQ Example

```http
POST /devniox-ai/admin/faqs
```

```json
{
  "business_key": "shop-123",
  "question": "delivery",
  "answer": "Delivery usually takes 2 business days.",
  "keywords": ["shipping", "courier"],
  "is_active": true
}
```

## Channel Credentials

Credentials submitted through the admin API are encrypted before storage.

```http
POST /devniox-ai/admin/channels
```

WhatsApp:

```json
{
  "business_key": "shop-123",
  "channel": "whatsapp",
  "connection_name": "Main WhatsApp",
  "credentials": {
    "token": "EA...",
    "phone_id": "123456789",
    "webhook_secret": "your-app-secret",
    "api_url": "https://graph.facebook.com/v18.0"
  }
}
```

Messenger:

```json
{
  "business_key": "shop-123",
  "channel": "messenger",
  "credentials": {
    "page_access_token": "EA...",
    "app_secret": "your-app-secret",
    "verify_token": "custom-verify-token",
    "api_url": "https://graph.facebook.com/v18.0"
  }
}
```

## Webhooks

- `POST /devniox-ai/webhooks/whatsapp`
- `GET|POST /devniox-ai/webhooks/messenger`
- `POST /devniox-ai/webhooks/website`

WhatsApp and Messenger signatures are validated with `X-Hub-Signature-256` when a secret is configured.

## Handoff Without Official APIs

Official WhatsApp/Messenger APIs are optional. For simple customer support handoff, configure:

```env
AI_AUTOMATION_HANDOFF_ENABLED=true
AI_AUTOMATION_HANDOFF_WHATSAPP_NUMBER=15551234567
AI_AUTOMATION_HANDOFF_MESSENGER_URL=https://m.me/your-page
AI_AUTOMATION_HANDOFF_EMAIL=support@example.com
AI_AUTOMATION_HANDOFF_PHONE="+1 555 123 4567"
```

The response includes handoff links when AI/local answers are not enough.

## Tenant Integration

For custom tenant/admin detection:

```php
config(['devniox-ai.tenant.resolver' => function ($request, array $context) {
    return [
        'business_key' => $request?->input('business_key'),
        'owner_type' => App\Models\User::class,
        'owner_id' => auth()->id(),
        'tenant_id' => auth()->user()?->business_id,
    ];
}]);
```

## Configuration

Important environment values:

```env
AI_AUTOMATION_AI_API_KEY=
AI_AUTOMATION_USE_AI_FALLBACK=true
AI_AUTOMATION_LOCAL_FIRST=true
AI_AUTOMATION_MAX_HISTORY_MESSAGES=6
AI_AUTOMATION_ADMIN_MIDDLEWARE=web,auth
```

## Testing

```bash
composer test
composer check-format
```

## Security Notes

- Set `AI_AUTOMATION_ADMIN_MIDDLEWARE` before exposing admin routes in production.
- Store official API credentials through the admin channel endpoint or environment variables.
- Keep API keys and channel secrets out of frontend code.
- Use HTTPS for webhook endpoints.
