<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Utilities\ValueObjects;

use EinarHansen\Toolkit\ValueObjects\FloatRangeValue;

class TestFloatRangeValue extends FloatRangeValue
{
    public function __construct(float $value)
    {
        parent::__construct($value);
    }

    protected function getMaxValue(): ?float
    {
        return 100.0;
    }

    protected function getMinValue(): ?float
    {
        return 0.0;
    }

    protected function getPrecision(): int
    {
        return 2;
    }
}
