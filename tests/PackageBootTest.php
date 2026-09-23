<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Tests;

use Devniox\AiAutomation\AI\Contracts\AiProviderInterface;
use Devniox\AiAutomation\AI\Providers\OpenAiProvider;
use Devniox\AiAutomation\Channels\Contracts\ChannelInterface;
use Devniox\AiAutomation\Channels\WebsiteChannel;

class PackageBootTest extends TestCase
{
    public function test_package_boots_and_bindings_exist(): void
    {
        $this->assertTrue(true);
        $this->assertTrue(app()->bound(AiProviderInterface::class));
        $this->assertTrue(app()->bound(ChannelInterface::class));

        $provider = app(AiProviderInterface::class);
        $channel = app(ChannelInterface::class);

        $this->assertInstanceOf(OpenAiProvider::class, $provider);
        $this->assertInstanceOf(WebsiteChannel::class, $channel);
    }

    public function test_config_is_available(): void
    {
        $this->assertTrue(config('devniox-ai.enabled'));
        $this->assertSame('openai', config('devniox-ai.ai.provider'));
    }
}
