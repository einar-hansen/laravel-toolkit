<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Configurables;

use EinarHansen\Toolkit\Contracts\Configurable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

final readonly class PreventStrayRequests implements Configurable
{
    /**
     * Whether the configurable is enabled or not.
     */
    public function enabled(): bool
    {
        $enabled = Config::boolean('toolkit.tests.prevent_stray_requests', true);
        $testing = app()->runningUnitTests();

        return $enabled && $testing;
    }

    /**
     * Run the configurable.
     */
    public function configure(): void
    {
        Http::preventStrayRequests();
    }
}
