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
use EinarHansen\Toolkit\Contracts\Configurable;
use EinarHansen\Toolkit\Mixins\ArrMixin;
use EinarHansen\Toolkit\Mixins\CollectionMixin;
use EinarHansen\Toolkit\Mixins\ResponseMixin;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
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
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->registerConfigurables();
        $this->registerMixins();

        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishPintConfigCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/toolkit.php' => $this->app->basePath('config/toolkit.php'),
            ], 'toolkit-config');
            $this->publishes([
                __DIR__.'/../stubs' => $this->app->basePath('stubs'),
            ], 'toolkit-stubs');
        }
    }

    private function registerConfigurables(): void
    {
        Collection::make($this->configurables)
            ->map(fn (string $configurable) => $this->app->make($configurable))
            ->filter(fn (Configurable $configurable): bool => $configurable->enabled())
            ->each(fn (Configurable $configurable) => $configurable->configure());
    }

    private function registerMixins(): void
    {
        $config = $this->app->make(Repository::class);
        if ($config->get('toolkit.mixins.arr')) {
            Arr::mixin(new ArrMixin);
        }

        if ($config->get('toolkit.mixins.collection')) {
            Collection::mixin(new CollectionMixin);
        }

        if ($config->get('toolkit.mixins.response')) {
            Response::mixin(new ResponseMixin);
        }
    }
}
