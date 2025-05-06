<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Configurables;

use EinarHansen\Toolkit\Contracts\Configurable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

final readonly class ShouldBeStrict implements Configurable
{
    /**
     * Whether the configurable is enabled or not.
     */
    public function enabled(): bool
    {
        return Config::boolean('toolkit.eloquent.strict_mode', true);
    }

    /**
     * Run the configurable.
     */
    public function configure(): void
    {
        Model::shouldBeStrict();
    }
}
