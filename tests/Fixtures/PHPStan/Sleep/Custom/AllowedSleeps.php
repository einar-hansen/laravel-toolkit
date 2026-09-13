<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Sleep\Custom;

use Illuminate\Support\Sleep;

function sleep(int $seconds): void {}
function usleep(int $microseconds): void {}

final class AllowedSleeps
{
    public function run(callable $callback): void
    {
        sleep(1);
        usleep(1000);
        namespace\sleep(1);
        Sleep::sleep(1);
        Sleep::usleep(1000);
        Sleep::for(1)->seconds();
        $this->sleep();
        $callback(1);
    }

    private function sleep(): void {}
}
