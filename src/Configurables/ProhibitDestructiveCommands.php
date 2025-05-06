<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Configurables;

use EinarHansen\Toolkit\Contracts\Configurable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

final readonly class ProhibitDestructiveCommands implements Configurable
{
    /**
     * Whether the configurable is enabled or not.
     */
    public function enabled(): bool
    {
        return Config::boolean('toolkit.app.disable_destructive_commands', true);
    }

    /**
     * Run the configurable.
     */
    public function configure(): void
    {
        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );
    }
}
