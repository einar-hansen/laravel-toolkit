<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Configurables;

use Carbon\CarbonImmutable;
use EinarHansen\Toolkit\Contracts\Configurable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

final readonly class ImmutableDates implements Configurable
{
    /**
     * Whether the configurable is enabled or not.
     */
    public function enabled(): bool
    {
        return Config::boolean('toolkit.app.enable_immutable_dates', true);
    }

    /**
     * Run the configurable.
     */
    public function configure(): void
    {
        Date::use(CarbonImmutable::class);
    }
}
