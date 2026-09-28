<?php

declare(strict_types=1);

namespace Devniox\AiAutomation;

use Devniox\AiAutomation\AI\Contracts\AiProviderInterface;
use Devniox\AiAutomation\AI\Providers\OpenAiProvider;
use Devniox\AiAutomation\Channels\Contracts\ChannelInterface;
use Devniox\AiAutomation\Channels\WebsiteChannel;
use Devniox\AiAutomation\Console\InstallCommand;
use Illuminate\Support\ServiceProvider;

class AiAutomationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/devniox-ai.php', 'devniox-ai');

        $this->app->bind(AiProviderInterface::class, function () {
            return new OpenAiProvider(
                config('devniox-ai.ai.api_key'),
                config('devniox-ai.ai.model')
            );
        });

        $this->app->bind(ChannelInterface::class, function () {
            return new WebsiteChannel;
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

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/devniox-ai.php');

        $viewsPath = __DIR__.'/../resources/views';

        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'devniox-ai');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
