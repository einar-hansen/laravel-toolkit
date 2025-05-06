<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit;

use EinarHansen\Toolkit\Commands\PublishPintConfigCommand;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

final class ToolkitServiceProvider extends BaseServiceProvider
{
    public $configurables;

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishPintConfigCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../stubs' => $this->app->basePath('stubs'),
            ], 'toolkit-stubs');
        }
    }
}
