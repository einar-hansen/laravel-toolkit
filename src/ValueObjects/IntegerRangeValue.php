<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\ValueObjects;

use ArrayAccess;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Stringable;

abstract class IntegerRangeValue implements Stringable
{
    public function __construct(
        protected readonly int $value
    ) {
        $this->validate();
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    abstract protected function getMaxValue(): ?int;

    abstract protected function getMinValue(): ?int;

    /**
     * Attempts to create a new instance from the given value.
     * Returns null if the value is invalid.
     */
    public static function tryFrom(int $value): ?static
    {
        try {
            return new static($value);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Creates a new instance from the given value.
     * Throws an exception if the value is invalid.
     *
     * @throws InvalidArgumentException If the value is invalid
     */
    public static function from(int $value): static
    {
        return new static($value);
    }

    /**
     * Attempts to create a new instance from the given array and key.
     * Returns null if the value is invalid or not present.
     *
     * @param  ArrayAccess<string, mixed>|array<string, mixed>  $array
     */
    public static function tryFromArray(ArrayAccess|array $array, string|int|null $key): ?static
    {
        $value = Arr::get($array, $key);
        try {
            if ($value === null) {
                return null;
            }

            return new static((int) $value);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Creates a new instance from the given array and key.
     * Uses default if the value is invalid or not present.
     * Throw an exception if the resulting value is invalid.
     *
     * @param  ArrayAccess<string, mixed>|array<string, mixed>  $array
     *
     * @throws InvalidArgumentException If the value is invalid
     */
    public static function fromArray(ArrayAccess|array $array, string|int|null $key, int $default = 0): static
    {
        $value = Arr::get($array, $key, $default);

        if ($value === null) {
            return new static($default);
        }

        return new static((int) $value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function float(): float
    {
        return (float) $this->value;
    }

    public function string(): string
    {
        return $this->__toString();
    }

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
}
