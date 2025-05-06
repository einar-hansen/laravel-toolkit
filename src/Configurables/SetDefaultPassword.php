<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Configurables;

use EinarHansen\Toolkit\Contracts\Configurable;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rules\Password;

final class SetDefaultPassword implements Configurable
{
    /**
     * {@inheritDoc}
     */
    public function enabled(): bool
    {
        return Config::boolean('toolkit.app.use_default_password', true);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(): void
    {
        Password::defaults(fn (): ?Password => app()->isProduction() ? Password::min(12)->max(255)->uncompromised() : null);
    }
}
