<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\ValueObjects;

use ArrayAccess;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Stringable;

abstract class DateRangeValue implements Stringable
{
    protected readonly CarbonInterface $date;

    public function __construct(
        string|CarbonInterface $value
    ) {
        $this->date = $this->parseDate($value);
        $this->validate();
    }

    public function __toString(): string
    {
        return $this->date->format($this->getDateFormat());
    }

    abstract protected function getMaxDate(): ?CarbonInterface;

    abstract protected function getMinDate(): ?CarbonInterface;

    /**
     * Attempts to create a new instance from the given value.
     * Returns null if the value is invalid.
     */
    public static function tryFrom(string|CarbonInterface|self $value): ?static
    {
        if ($value instanceof self) {
            $value = $value->string();
        }

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
    public static function from(string|CarbonInterface|self $value): static
    {
        if ($value instanceof self) {
            $value = $value->string();
        }

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

        if ($value === null) {
            return null;
        }

        return static::tryFrom($value);
    }

    /**
     * Creates a new instance from the given array and key.
     * Uses default if the value is invalid or not present.
     * Throws an exception if the resulting value is invalid.
     *
     * @param  ArrayAccess<string, mixed>|array<string, mixed>  $array
     *
     * @throws InvalidArgumentException If the value is invalid
     */
    public static function fromArray(ArrayAccess|array $array, string|int|null $key, string|CarbonInterface|null|self $default = null): static
    {
        $value = Arr::get($array, $key, $default);

        if ($value === null) {
            if ($default === null) {
                throw new InvalidArgumentException('No default value provided for null array value');
            }

            return static::from($default);
        }

        return static::from($value);
    }

    public function value(): CarbonInterface
    {
        return $this->date;
    }

    public function string(): string
    {
        return $this->__toString();
    }

    protected function getDateFormat(): string
    {
        return 'Y-m-d';
    }

    protected function parseDate(string|CarbonInterface|self $value): CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if ($value instanceof self) {
            return $value->value();
        }

        try {
            return Carbon::parse($value);
        } catch (Exception $exception) {
            throw new InvalidArgumentException(sprintf(
                'Invalid date format: %s. Error: %s',
                $value,
                $exception->getMessage()
            ), $exception->getCode(), $exception);
        }
    }

    protected function validate(): void
    {
        if ($this->getMaxDate() instanceof CarbonInterface && $this->date->isAfter($this->getMaxDate())) {
            throw new InvalidArgumentException(
                sprintf(
                    'Date exceeds maximum of %s for %s',
                    $this->getMaxDate()->format($this->getDateFormat()),
                    static::class
                )
            );
        }

        if ($this->getMinDate() instanceof CarbonInterface && $this->date->isBefore($this->getMinDate())) {
            throw new InvalidArgumentException(
                sprintf(
                    'Date is before minimum of %s for %s',
                    $this->getMinDate()->format($this->getDateFormat()),
                    static::class
                )
            );
        }
    }
}
