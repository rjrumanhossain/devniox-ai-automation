# Devniox AI Automation

A reusable Laravel package that adds AI-powered customer automation for conversations, leads, knowledge management, and multi-channel messaging.

## What it is

This package is designed to be installed into any Laravel application as a Composer library. It does not assume a specific authentication system, user model, dashboard, or business logic. Instead, it exposes configuration points and contracts so the host application can integrate the package without changing its existing architecture.

## Requirements

- PHP 8.2+
- Laravel 10 or Laravel 11
- Composer
- Queue support (optional but recommended)
- OpenAI-compatible API or other provider adapters

## Installation

```bash
composer require devniox/ai-automation
php artisan vendor:publish --tag=devniox-ai-config
php artisan vendor:publish --tag=devniox-ai-migrations
php artisan migrate
```

## Configuration

Publish the package config and review the values in `config/devniox-ai.php`.

```php
return [
    'enabled' => env('AI_AUTOMATION_ENABLED', true),
    'ai' => [
        'provider' => env('AI_AUTOMATION_AI_PROVIDER', 'openai'),
        'api_key' => env('AI_AUTOMATION_AI_API_KEY'),
        'model' => env('AI_AUTOMATION_AI_MODEL', 'gpt-4o-mini'),
        'default_language' => env('AI_AUTOMATION_DEFAULT_LANGUAGE', 'en'),
    ],
    'queue' => [
        'connection' => env('AI_AUTOMATION_QUEUE_CONNECTION', 'sync'),
        'name' => env('AI_AUTOMATION_QUEUE_NAME', 'devniox-ai'),
    ],
    'tenant' => [
        'enabled' => env('AI_AUTOMATION_TENANT_MODE', false),
        'resolver' => null,
    ],
];
```

## AI setup

The package is structured around the `AiProviderInterface` and can be swapped with a custom provider implementation. The bundled implementation is an OpenAI-compatible provider.

```php
use Devniox\AiAutomation\AI\Providers\OpenAiProvider;

app()->bind(\Devniox\AiAutomation\AI\Contracts\AiProviderInterface::class, function () {
    return new OpenAiProvider(config('devniox-ai.ai.api_key'), config('devniox-ai.ai.model'));
});
```

## WhatsApp setup

Configure the WhatsApp Business API credentials and define a webhook route.

```env
AI_AUTOMATION_WHATSAPP_TOKEN=...
AI_AUTOMATION_WHATSAPP_PHONE_ID=...
AI_AUTOMATION_WHATSAPP_WEBHOOK_SECRET=...
```

## Messenger setup

```env
AI_AUTOMATION_MESSENGER_PAGE_ACCESS_TOKEN=...
AI_AUTOMATION_MESSENGER_APP_SECRET=...
AI_AUTOMATION_MESSENGER_VERIFY_TOKEN=...
```

## Website chat setup

A bare JavaScript widget is provided and can be published with the asset tag.

```html
<script src="/vendor/devniox-ai/devniox-ai-widget.js"></script>
<script>
  window.DevnioxAiWidget.init({
      baseUrl: '/devniox-ai',
      customerId: 'customer-123'
  });
</script>
```

## Queue setup

Set the queue connection in the config file, then run the queue worker:

```bash
php artisan queue:work --queue=devniox-ai
```

## Webhook setup

Routes are exposed under the package prefix. Example:

- `/devniox-ai/webhooks/whatsapp`
- `/devniox-ai/webhooks/messenger`
- `/devniox-ai/webhooks/website`

## Tenant integration

The package supports a tenant resolver and filters data when tenant mode is on.

```php
config(['devniox-ai.tenant.enabled' => true]);
config(['devniox-ai.tenant.resolver' => fn () => 42]);
```

## Authentication integration

The host application owns the actual authentication and staff permissions. The package exposes an `owner_model` configuration and policy hooks.

## Events

The package dispatches events such as:

- `MessageReceived`
- `MessageProcessingStarted`
- `AiResponseGenerated`
- `MessageSent`
- `MessageFailed`
- `HumanHandoffRequested`
- `LeadCreated`
- `WebhookReceived`

## Extending providers

Implement the interfaces to swap AI providers or channel providers:

```php
use Devniox\AiAutomation\AI\Contracts\AiProviderInterface;
use Devniox\AiAutomation\Channels\Contracts\ChannelInterface;
```

## Testing

```bash
composer test
```

## Troubleshooting

- Confirm the package config is published.
- Ensure queue workers are running.
- Check provider signature verification tokens.
- Verify environment variables are present.

## Security

- Keep API keys in environment variables.
- Encrypt sensitive credentials before persisting.
- Do not expose secrets in frontend code.
- Validate all incoming webhook payloads.
- Use tenant-aware policies and guards.

## Example integration into a fresh Laravel project

```php
// config/services.php or AppServiceProvider
use Devniox\AiAutomation\AI\Contracts\AiProviderInterface;
use Devniox\AiAutomation\AI\Providers\OpenAiProvider;

$this->app->bind(AiProviderInterface::class, function () {
    return new OpenAiProvider(
        config('devniox-ai.ai.api_key'),
        config('devniox-ai.ai.model')
    );
});
```

```bash
php artisan vendor:publish --tag=devniox-ai-config
php artisan vendor:publish --tag=devniox-ai-migrations
php artisan migrate
```
