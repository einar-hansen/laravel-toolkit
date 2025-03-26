<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Utilities\ValueObjects;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use EinarHansen\Toolkit\ValueObjects\DateRangeValue;

class TestDateRangeValue extends DateRangeValue
{
    protected function getMaxDate(): ?CarbonInterface
    {
        return Carbon::parse('2025-12-31');
    }

    protected function getMinDate(): ?CarbonInterface
    {
        return Carbon::parse('2020-01-01');
    }

    protected function getDateFormat(): string
    {
        return 'Y-m-d';
    }
}
