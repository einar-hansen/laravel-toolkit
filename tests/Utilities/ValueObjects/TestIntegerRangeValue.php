<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Utilities\ValueObjects;

use EinarHansen\Toolkit\ValueObjects\IntegerRangeValue;
use Override;

class TestIntegerRangeValue extends IntegerRangeValue
{
    public function __construct(int $value)
    {
        parent::__construct($value);
    }

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
