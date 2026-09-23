<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Tests;

use Devniox\AiAutomation\AI\Providers\OpenAiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiProviderTest extends TestCase
{
    public function test_ai_provider_generates_response(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => 'Hello there'],
                ]],
                'usage' => ['total_tokens' => 12],
            ], 200),
        ]);

        config()->set('devniox-ai.ai.api_key', 'test-key');

        $provider = new OpenAiProvider('test-key', 'gpt-4o-mini');
        $result = $provider->generateResponse(['message' => 'Hi']);

        $this->assertSame('Hello there', $result['content']);
        $this->assertSame('openai', $result['provider']);
    }

    public function test_ai_provider_requires_api_key(): void
    {
        $this->expectException(RuntimeException::class);

        $provider = new OpenAiProvider('', 'gpt-4o-mini');
        $provider->generateResponse(['message' => 'Hi']);
    }
}
