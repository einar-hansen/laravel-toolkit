<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Sleep;

use function sleep as pause;
use function usleep as microPause;

final class NativeSleeps
{
    public function run(): void
    {
        sleep(1);
        \sleep(seconds: 1);
        SLEEP(1);
        pause(1);
        usleep(1000);
        \usleep(microseconds: 1000);
        microPause(1000);
        $callback = \sleep(...);
    }
}
