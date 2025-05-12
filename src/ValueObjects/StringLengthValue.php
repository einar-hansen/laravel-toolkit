<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\ValueObjects;

use ArrayAccess;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Override;
use Stringable;

abstract class StringLengthValue implements Stringable
{
    public function __construct(
        protected readonly string $value
    ) {
        $this->validate();
    }

    #[Override]
    public function __toString(): string
    {
        return $this->value;
    }

    abstract protected function getMaxLength(): ?int;

    abstract protected function getMinLength(): ?int;

    /**
     * Attempts to create a new instance from the given value.
     * Returns null if the value is invalid.
     */
    public static function tryFrom(string $value): ?static
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
    public static function from(string $value): static
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
            return $value === null ? null : new static((string) $value);
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
    public static function fromArray(ArrayAccess|array $array, string|int|null $key, string $default = ''): static
    {
        $value = Arr::get($array, $key, $default);

        if ($value === null) {
            return new static($default);
        }

        return new static(is_string($value) ? $value : (string) $value);
    }

    public function value(): string
    {
        return $this->value;
    }

    protected function validate(): void
    {
        if ($this->getMaxLength() !== null && mb_strlen($this->value) > $this->getMaxLength()) {
            throw new InvalidArgumentException(
                sprintf(
                    'String length exceeds maximum of %d characters for %s',
                    $this->getMaxLength(),
                    static::class
                )
            );
        }

        if ($this->getMinLength() !== null && mb_strlen($this->value) < $this->getMinLength()) {
            throw new InvalidArgumentException(
                sprintf(
                    'String length is less than minimum of %d characters for %s',
                    $this->getMinLength(),
                    static::class
                )
            );
        }
    }
}
