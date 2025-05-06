<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Configurables;

use EinarHansen\Toolkit\Contracts\Configurable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Vite;

final readonly class AggressivePrefetching implements Configurable
{
    /**
     * Whether the configurable is enabled or not.
     */
    public function enabled(): bool
    {
        return Config::boolean('toolkit.app.enable_aggressive_prefetching', true);
    }

    /**
     * Run the configurable.
     */
    public function configure(): void
    {
        Vite::useAggressivePrefetching();
    }
}
