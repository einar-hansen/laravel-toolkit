<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Commands;

use Illuminate\Console\Command;

final class PublishPhpstanConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'toolkit:publish:phpstan
        {--force : Force the operation to run without confirmation}
        {--backup : Create a backup of existing phpstan.neon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will publish an opinionated PHPStan configuration file for your project.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force') && ! $this->components->confirm('Do you wish to publish the PHPStan configuration file? This will override the existing [phpstan.neon] file.', true)) {
            return 0;
        }

        $stub_path = __DIR__.'/../../stubs/phpstan.stub';
        $destination_path = base_path('phpstan.neon');

        if (! file_exists($stub_path)) {
            $this->components->error('PHPStan configuration stub file not found.');

            return 1;
        }

        if (file_exists($destination_path) && $this->option('backup')) {
            if (! copy($destination_path, $destination_path.'.backup')) {
                $this->components->error('Failed to back up the existing PHPStan configuration file.');

                return 1;
            }

            $this->components->info('Backup created at: '.$destination_path.'.backup');
        }

        $this->components->info('Publishing PHPStan configuration file...');

        if (! copy($stub_path, $destination_path)) {
            $this->components->error('Failed to publish the PHPStan configuration file.');

            return 1;
        }

        $this->components->info('PHPStan configuration file published successfully at: '.$destination_path);

        return 0;
    }
}
