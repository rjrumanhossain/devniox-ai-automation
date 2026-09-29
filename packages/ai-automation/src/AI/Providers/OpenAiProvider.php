<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\AI\Providers;

use Devniox\AiAutomation\AI\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiProvider implements AiProviderInterface
{
    protected string $apiKey;

    protected string $model;

    protected string $systemInstructions = 'You are a helpful customer support assistant.';

    protected array $businessContext = [];

    protected array $conversationHistory = [];

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? (string) config('devniox-ai.ai.api_key');
        $this->model = $model ?? (string) config('devniox-ai.ai.model', 'gpt-4o-mini');
    }

    public function generateResponse(array $payload): array
    {
        if ($this->apiKey === '' || $this->apiKey === '0') {
            throw new RuntimeException('AI provider API key is not configured.');
        }

        $messages = [
            [
                'role' => 'system',
                'content' => $this->systemInstructions,
            ],
        ];

        if (! empty($this->businessContext)) {
            $messages[] = [
                'role' => 'system',
                'content' => 'Business context: '.json_encode($this->businessContext, JSON_THROW_ON_ERROR),
            ];
        }

        foreach ($this->conversationHistory as $item) {
            $messages[] = [
                'role' => $item['role'] ?? 'user',
                'content' => $item['content'] ?? '',
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $payload['message'] ?? '',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => (float) ($payload['temperature'] ?? config('devniox-ai.ai.response.temperature', 0.7)),
            'max_tokens' => (int) ($payload['max_tokens'] ?? config('devniox-ai.ai.response.max_tokens', 300)),
        ]);

        if ($response->failed()) {
            throw new RuntimeException('AI provider request failed: '.$response->body());
        }

        $content = $response->json('choices.0.message.content', '');

        return [
            'content' => $content,
            'provider' => 'openai',
            'model' => $this->model,
            'usage' => $response->json('usage', []),
        ];
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function withSystemInstructions(string $instructions): self
    {
        $this->systemInstructions = $instructions;

        return $this;
    }

    public function withBusinessContext(array $context): self
    {
        $this->businessContext = $context;

        return $this;
    }

    public function withConversationHistory(array $history): self
    {
        $this->conversationHistory = $history;

        return $this;
    }
}
