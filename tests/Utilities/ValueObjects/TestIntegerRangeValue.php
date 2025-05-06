<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Utilities\ValueObjects;

use EinarHansen\Toolkit\ValueObjects\IntegerRangeValue;
use Override;

final class TestIntegerRangeValue extends IntegerRangeValue
{
    #[Override]
    protected function getMaxValue(): ?int
    {
        return 100;
    }

    #[Override]
    protected function getMinValue(): ?int
    {
        return 1;
    }
}
