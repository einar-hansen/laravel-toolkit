<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\ValueObjects;

use InvalidArgumentException;
use Override;
use Stringable;

abstract class StringRegexValue implements Stringable
{
    public function __construct(
        protected readonly string $value
    ) {
        $this->validate();
    }

    /**
     * Get the regex pattern to validate against.
     * Return null to skip regex validation.
     */
    abstract protected function getPattern(): ?string;

    /**
     * Get custom error message for regex validation failure.
     * If not overridden, a default message will be used.
     */
    protected function getPatternErrorMessage(): string
    {
        return sprintf(
            'String "%s" does not match required pattern for %s',
            $this->value,
            static::class
        );
    }

    /**
     * Validates the string against the regex pattern.
     *
     * @throws InvalidArgumentException if validation fails
     */
    protected function validate(): void
    {
        $pattern = $this->getPattern();

        if ($pattern !== null && in_array(preg_match($pattern, $this->value), [0, false], true)) {
            throw new InvalidArgumentException($this->getPatternErrorMessage());
        }
    }

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
     * Returns the raw string value.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Returns the string value when cast to string.
     */
    #[Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
