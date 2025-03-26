<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\ValueObjects;

use InvalidArgumentException;
use Stringable;

abstract class IntegerRangeValue implements Stringable
{
    public function __construct(
        protected readonly int $value
    ) {
        $this->validate();
    }

    abstract protected function getMaxValue(): ?int;

    abstract protected function getMinValue(): ?int;

    protected function validate(): void
    {
        if ($this->getMaxValue() !== null && $this->value > $this->getMaxValue()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Integer value exceeds maximum of %d for %s',
                    $this->getMaxValue(),
                    static::class
                )
            );
        }

        if ($this->getMinValue() !== null && $this->value < $this->getMinValue()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Integer value is less than minimum of %d for %s',
                    $this->getMinValue(),
                    static::class
                )
            );
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
