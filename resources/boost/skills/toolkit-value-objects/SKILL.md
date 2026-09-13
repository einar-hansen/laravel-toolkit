---
name: toolkit-value-objects
description: Implement Laravel Toolkit range, regex, length, date, and phone value objects and use UsesPhoneNumbers with explicit validation and region behavior.
---

# Toolkit value objects

Create concrete subclasses in the application's existing value-object directory. All six bases live in `EinarHansen\Toolkit\ValueObjects`; they are abstract. Constructors validate immediately. Keep constructor signatures compatible with inherited `from` / `tryFrom` factories, which instantiate `new static(...)`.

## Choose the base and implement its hooks

| Base | Required hooks | Key behavior / verification |
| --- | --- | --- |
| `IntegerRangeValue` | `getMinValue(): ?int`, `getMaxValue(): ?int` | Inclusive integer bounds; null disables a bound. Test both boundaries and one value outside each. `value`, `float`, `string` expose representations. |
| `FloatRangeValue` | `getMinValue(): ?float`, `getMaxValue(): ?float`, `getPrecision(): int` | Rounds to configured precision; test rounding near both bounds. `integer()` currently returns a rounded float, not an int. Do not use floating point for exact currency accounting. |
| `StringLengthValue` | `getMinLength(): ?int`, `getMaxLength(): ?int` | Bounds on multibyte length; test Unicode and empty strings. Null bounds are unbounded. |
| `StringRegexValue` | `getPattern(): ?string` | Supply a valid delimited PHP regex; null disables the constraint. Test complete matches and nonmatches; anchor patterns when the whole string must match. |
| `DateRangeValue` | `getMinDate(): ?CarbonInterface`, `getMaxDate(): ?CarbonInterface` | Accepts strings/Carbon values; optional `getDateFormat()` controls string output. Test range boundaries, invalid input, timezones and retained time. `value()` returns CarbonInterface; readonly storage does not guarantee the contained date is immutable. |
| `PhoneNumberValue` | `getDefaultRegion(): string`, public `__toString(): string` | Validates with libphonenumber. Decide the string representation explicitly, usually E.164. Test invalid numbers, a valid local number, international input and region overrides. |

```php
use EinarHansen\Toolkit\ValueObjects\IntegerRangeValue;

final class Percentage extends IntegerRangeValue
{
    protected function getMinValue(): ?int { return 0; }
    protected function getMaxValue(): ?int { return 100; }
}

$percentage = Percentage::from(75);
$invalid = Percentage::tryFrom(101); // null
```

Use `from` for values whose failure should raise `InvalidArgumentException`, and `tryFrom` for that validation failure as null. These methods do not catch arbitrary TypeError/other programming errors. `fromArray` / `tryFromArray` read dot-notation keys. Defaults mainly handle missing/null inputs: they do not universally replace an invalid present value. Validate source types first; scalar casts can turn malformed input into plausible numbers. Test missing keys, explicit null, invalid present values and an invalid default separately.

## Phone objects and trait

```php
use EinarHansen\Toolkit\ValueObjects\PhoneNumberValue;

final class ContactPhone extends PhoneNumberValue
{
    protected function getDefaultRegion(): string { return 'NO'; }
    public function __toString(): string { return (string) $this->e164(); }
}

$phone = ContactPhone::tryFrom('+4791234567');
$canonical = $phone?->e164();
```

`original()` returns supplied text; `e164()`, `national()`, `international()`, `type()`, `region()` and `matches()` expose interpretations. `value()` uses normalization and currently does not forward the constructor's region override: use `e164()` for canonical storage with the selected region. Keep phone numbers as strings to preserve `+` and leading zeroes.

For formatting/matching without a value object, use `EinarHansen\Toolkit\Concerns\UsesPhoneNumbers`. Its default region is `NO`; override protected `getDefaultRegion()` or pass a region explicitly. It provides `isValidPhoneNumber`, `normalizePhoneNumber`, `formatPhoneNumber`, `phoneNumbersMatch`, `getPhoneNumberType`, `getPhoneNumberRegion`, `toE164`, `toNationalFormat`, `toInternationalFormat`.

Normalization has a Norwegian fallback and can return cleaned text for invalid numbers. Matching also has a normalization fallback; neither substitutes for `isValidPhoneNumber()`. Test validation separately from normalization and compare exact expected format strings.
