<?php

declare(strict_types=1);

namespace Devniox\AiAutomation;

use Illuminate\Support\ServiceProvider;

class AiAutomationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/devniox-ai.php', 'devniox-ai');

        $this->app->bind(\Devniox\AiAutomation\AI\Contracts\AiProviderInterface::class, function () {
            return new \Devniox\AiAutomation\AI\Providers\OpenAiProvider(
                config('devniox-ai.ai.api_key'),
                config('devniox-ai.ai.model')
            );
        });

        $this->app->bind(\Devniox\AiAutomation\Channels\Contracts\ChannelInterface::class, function () {
            return new \Devniox\AiAutomation\Channels\WebsiteChannel();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/devniox-ai.php' => config_path('devniox-ai.php'),
        ], 'devniox-ai-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'devniox-ai-migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/devniox-ai.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'devniox-ai');
    }
}
