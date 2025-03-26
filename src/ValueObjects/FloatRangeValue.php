<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\ValueObjects;

use InvalidArgumentException;
use Stringable;

abstract class FloatRangeValue implements Stringable
{
    public function __construct(
        protected readonly float $value
    ) {
        $this->validate();
    }

    abstract protected function getMaxValue(): ?float;

    abstract protected function getMinValue(): ?float;

    abstract protected function getPrecision(): int;

    protected function validate(): void
    {
        if ($this->getMaxValue() !== null && $this->value > $this->getMaxValue()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Float value exceeds maximum of %F for %s',
                    $this->getMaxValue(),
                    static::class
                )
            );
        }

        if ($this->getMinValue() !== null && $this->value < $this->getMinValue()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Float value is less than minimum of %F for %s',
                    $this->getMinValue(),
                    static::class
                )
            );
        }
    }

    public function value(): float
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return number_format($this->value, $this->getPrecision(), '.', '');
    }
}
