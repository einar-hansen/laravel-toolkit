<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests;

use EinarHansen\Toolkit\ToolkitServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ToolkitServiceProvider::class,
        ];
    }
}
