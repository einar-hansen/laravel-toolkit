<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\ValueObjects;

use ArrayAccess;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Stringable;

abstract class FloatRangeValue implements Stringable
{
    public function __construct(
        protected readonly float $value
    ) {
        $this->validate();
    }

    public function __toString(): string
    {
        return number_format($this->value, $this->getPrecision(), '.', '');
    }

    abstract protected function getMaxValue(): ?float;

    abstract protected function getMinValue(): ?float;

    abstract protected function getPrecision(): int;

    /**
     * Attempts to create a new instance from the given value.
     * Returns null if the value is invalid.
     */
    public static function tryFrom(int|float|string $value): ?static
    {
        try {
            $floatValue = static::parseFloat($value);

            return new static($floatValue);
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
    public static function from(int|float|string $value): static
    {
        $floatValue = static::parseFloat($value);

        return new static($floatValue);
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

        if ($value === null) {
            return null;
        }

        return static::tryFrom($value);
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
    public static function fromArray(ArrayAccess|array $array, string|int|null $key, float|int|string $default = 0): static
    {
        $value = Arr::get($array, $key, $default);

        if ($value === null) {
            return static::from($default);
        }

        return static::from($value);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function string(): string
    {
        return $this->__toString();
    }

    public function integer(): float
    {
        return (int) $this->value;
    }

    /**
     * Parses a value into a float.
     *
     * @param  int|float|string  $value  The value to parse
     * @return float The parsed float value
     *
     * @throws InvalidArgumentException If the value cannot be parsed as a float
     */
    protected static function parseFloat(int|float|string $value): float
    {
        if (is_float($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (float) $value;
        }

        // Normalize the string by trimming and replacing comma with dot
        $normalizedValue = str_replace(',', '.', mb_trim($value));
        // Check if the string is a valid numeric representation
        if (is_numeric($normalizedValue)) {
            return (float) $normalizedValue;
        }

        throw new InvalidArgumentException(sprintf(
            'Cannot parse "%s" as a float value for %s',
            $value,
            static::class
        ));
    }

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
}
