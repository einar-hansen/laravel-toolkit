<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Configurables;

use EinarHansen\Toolkit\Contracts\Configurable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

final readonly class ForceScheme implements Configurable
{
    /**
     * Whether the configurable is enabled or not.
     */
    public function enabled(): bool
    {
        return Config::boolean('toolkit.app.enforce_https_scheme', true);
    }

    /**
     * Run the configurable.
     */
    public function configure(): void
    {
        URL::forceHttps();
    }
}
