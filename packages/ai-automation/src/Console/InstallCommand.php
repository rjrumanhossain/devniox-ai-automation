<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'devniox-ai:install
        {--migrate : Run the package migrations after publishing}
        {--force : Overwrite existing published config and migration files}';

    protected $description = 'Install Devniox AI Automation into the host Laravel application.';

    public function handle(): int
    {
        $this->components->info('Installing Devniox AI Automation...');

        $publishOptions = [
            '--tag' => ['devniox-ai-config', 'devniox-ai-migrations'],
        ];

        if ($this->option('force')) {
            $publishOptions['--force'] = true;
        }

        $this->call('vendor:publish', $publishOptions);

        if ($this->option('migrate')) {
            $this->call('migrate', [
                '--force' => true,
            ]);
        }

        $this->newLine();
        $this->components->info('Devniox AI Automation is installed.');
        $this->line('Admin panel: /devniox-ai/admin');
        $this->line('Chat endpoint: /devniox-ai/chat');

        return self::SUCCESS;
    }
}
