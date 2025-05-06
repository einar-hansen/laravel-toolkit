<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit;

use EinarHansen\Toolkit\Commands\PublishPintConfigCommand;
use EinarHansen\Toolkit\Configurables\AggressivePrefetching;
use EinarHansen\Toolkit\Configurables\AutomaticallyEagerLoadRelationships;
use EinarHansen\Toolkit\Configurables\FakeSleep;
use EinarHansen\Toolkit\Configurables\ForceScheme;
use EinarHansen\Toolkit\Configurables\ImmutableDates;
use EinarHansen\Toolkit\Configurables\PreventStrayRequests;
use EinarHansen\Toolkit\Configurables\ProhibitDestructiveCommands;
use EinarHansen\Toolkit\Configurables\SetDefaultPassword;
use EinarHansen\Toolkit\Configurables\ShouldBeStrict;
use EinarHansen\Toolkit\Configurables\Unguard;
use EinarHansen\Toolkit\Contracts\Configurable;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

final class ToolkitServiceProvider extends BaseServiceProvider
{
    /**
     * The list of configurables.
     *
     * @var list<class-string<Configurable>>
     */
    private array $configurables = [
        AggressivePrefetching::class,
        AutomaticallyEagerLoadRelationships::class,
        FakeSleep::class,
        ForceScheme::class,
        ImmutableDates::class,
        PreventStrayRequests::class,
        ProhibitDestructiveCommands::class,
        SetDefaultPassword::class,
        ShouldBeStrict::class,
        Unguard::class,
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        Collection::make($this->configurables)
            ->map(fn (string $configurable) => $this->app->make($configurable))
            ->filter(fn (Configurable $configurable): bool => $configurable->enabled())
            ->each(fn (Configurable $configurable) => $configurable->configure());

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
