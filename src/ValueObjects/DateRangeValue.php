<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\ValueObjects;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
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

    abstract protected function getMaxDate(): ?CarbonInterface;

    abstract protected function getMinDate(): ?CarbonInterface;

    protected function getDateFormat(): string
    {
        return 'Y-m-d';
    }

    protected function parseDate(string|CarbonInterface $value): CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
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

    /**
     * Attempts to create a new instance from the given value.
     * Returns null if the value is invalid.
     */
    public static function tryFrom(string|CarbonInterface $value): ?static
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
    public static function from(string|CarbonInterface $value): static
    {
        return new static($value);
    }

    public function value(): CarbonInterface
    {
        return $this->date;
    }

    public function string(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->date->format($this->getDateFormat());
    }
}
