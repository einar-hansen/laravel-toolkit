<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Configurables;

use EinarHansen\Toolkit\Contracts\Configurable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Sleep;

final readonly class FakeSleep implements Configurable
{
    /**
     * Whether the configurable is enabled or not.
     */
    public function enabled(): bool
    {
        return Config::boolean('toolkit.tests.enable_fake_sleep', true);
    }

    /**
     * Run the configurable.
     */
    public function configure(): void
    {
        Sleep::fake();
    }
}
