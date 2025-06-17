<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\ValueObjects;

use ArrayAccess;
use EinarHansen\Toolkit\Concerns\UsesPhoneNumbers;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Override;
use Stringable;

abstract class PhoneNumberValue implements Stringable
{
    use UsesPhoneNumbers;

    /**
     * Create a new phone number value object
     *
     * @throws InvalidArgumentException If the phone number is invalid
     */
    public function __construct(
        protected readonly string $value,
        protected readonly ?string $region = null
    ) {
        $this->validate();
    }

    /**
     * Returns the phone number in international format when cast to string
     */
    #[Override]
    abstract public function __toString(): string;

    abstract protected function getDefaultRegion(): string;

    /**
     * Attempts to create a new instance from the given value.
     * Returns null if the value is invalid.
     */
    public static function tryFrom(string|int|null $value, ?string $region = null): ?static
    {
        if ($value === null) {
            return null;
        }

        try {
            return new static((string) $value, $region);
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
    public static function from(string|int|null $value, ?string $region = null): static
    {
        return new static((string) $value, $region);
    }

    /**
     * Attempts to create a new instance from the given array and key.
     * Returns null if the value is invalid or not present.
     *
     * @param  ArrayAccess<string, mixed>|array<string, mixed>  $array
     */
    public static function tryFromArray(ArrayAccess|array $array, string|int|null $key, ?string $region = null): ?static
    {
        $value = Arr::get($array, $key);

        if ($value === null) {
            return null;
        }

        try {
            return new static((string) $value, $region);
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
    public static function fromArray(ArrayAccess|array $array, string|int|null $key, ?string $default = null, ?string $region = null): static
    {
        $value = Arr::get($array, $key, $default);

        return new static($value, $region);
    }

    /**
     * Returns the normalized phone number
     */
    public function original(): ?string
    {
        return $this->value;
    }

    /**
     * Returns the normalized phone number
     */
    public function value(): ?string
    {
        return $this->normalizePhoneNumber($this->value);
    }

    public function string(): string
    {
        return $this->__toString();
    }

    /**
     * Returns the phone number in E164 format (+4712345678)
     */
    public function e164(): ?string
    {
        return $this->toE164($this->value, $this->region);
    }

    /**
     * Returns the phone number in national format (12 34 56 78)
     */
    public function national(): ?string
    {
        return $this->toNationalFormat($this->value, $this->region);
    }

    /**
     * Returns the phone number in international format (+47 12 34 56 78)
     */
    public function international(): ?string
    {
        return $this->toInternationalFormat($this->value, $this->region);
    }

    /**
     * Returns the phone number type (mobile, fixed_line, etc.)
     */
    public function type(): ?string
    {
        return $this->getPhoneNumberType($this->value, $this->region);
    }

    /**
     * Returns the region code for the phone number
     */
    public function region(): ?string
    {
        return $this->getPhoneNumberRegion($this->value, $this->region);
    }

    /**
     * Checks if this phone number matches another phone number
     */
    public function matches(self|string|null $other): bool
    {
        if ($other === null) {
            return false;
        }

        if ($other instanceof self) {
            return $this->phoneNumbersMatch($this->value, $other->value, $this->region);
        }

        return $this->phoneNumbersMatch($this->value, $other, $this->region);
    }

    /**
     * Validates the phone number
     *
     * @throws InvalidArgumentException If the phone number is invalid
     */
    protected function validate(): void
    {
        if ($this->value === '' || $this->value === '0') {
            throw new InvalidArgumentException('Phone number cannot be empty');
        }

        if (! $this->isValidPhoneNumber($this->value, $this->region)) {
            throw new InvalidArgumentException(
                sprintf('Invalid phone number: %s', $this->value)
            );
        }
    }
}
