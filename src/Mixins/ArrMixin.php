<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Mixins;

use ArrayAccess;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Closure;
use DateTimeInterface;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Stringable;
use JsonSerializable;
use Stringable as StringableContract;

final class ArrMixin
{
    public function tryKeys(): Closure
    {
        return function (array $array, string ...$keys) {
            foreach ($keys as $key) {
                $value = Arr::get($array, $key);
                if ($value !== null) {
                    return $value;
                }
            }

            return null;
        };
    }

    /**
     * Check if a key exists in the array and its value is not null.
     *
     * Mimics the behavior of PHP's isset() function for array elements accessed
     * via dot notation. Returns true only if the key path resolves and the
     * resulting value is not null.
     *
     * @return Closure(array<array-key, mixed>, string):bool
     */
    public function isset()
    {
        return function (ArrayAccess|array $array, string|int|null $key): bool {
            if (! Arr::has($array, $key)) {
                return false;
            }

            return Arr::get($array, $key) !== null;
        };
    }

    /**
     * Check if an array element accessed via dot notation is "empty".
     *
     * Mimics the behavior of PHP's empty() function. Returns true if the key
     * does not exist, or if the resolved value is considered empty by PHP
     * (null, false, 0, 0.0, "0", "", []).
     *
     * Note: Unlike isset(), empty() *can* be used on the result of a function call.
     *
     * @return Closure(array<array-key, mixed>, string):bool
     */
    public function isEmpty() // Renamed from the commented version for clarity
    {
        return fn (ArrayAccess|array $array, string|int|null $key): bool => empty(Arr::get($array, $key));
    }

    public function string(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key, string $default = ''): string {
            $value = Arr::get($array, $key, $default);

            if ($this->canBeCastToString($value)) {
                if ($this->shouldCastToJson($value)) {
                    $value = json_encode($value);
                }

                return is_string($value) ? $value : (string) $value;
            }

            return $default;

        };
    }

    public function stringOrNull(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key): ?string {
            $value = Arr::get($array, $key);

            if ($this->canBeCastToString($value)) {
                if ($this->shouldCastToJson($value)) {
                    $value = json_encode($value);
                }

                return is_string($value) ? $value : (string) $value;
            }

            return null;
        };
    }

    public function stringable(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key, Stringable|string $default = ''): Stringable {
            $value = Arr::get($array, $key, $default);

            if ($this->canBeCastToString($value)) {
                if ($this->shouldCastToJson($value)) {
                    $value = json_encode($value);
                }

                return new Stringable(is_string($value) ? $value : (string) $value);
            }

            return new Stringable($default);

        };
    }

    public function stringableOrNull(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key): ?Stringable {
            $value = Arr::get($array, $key);

            if ($this->canBeCastToString($value)) {
                if ($this->shouldCastToJson($value)) {
                    $value = json_encode($value);
                }

                return new Stringable(is_string($value) ? $value : (string) $value);
            }

            return null;
        };
    }

    public function array(): Closure
    {
        return static function (ArrayAccess|array $source, string|int|null $key, array $default = []): array {
            $value = Arr::get($source, $key);

            if ($value === null) {
                return $default;
            }

            if (is_array($value)) {
                return $value;
            }

            if ($value instanceof Arrayable) {
                return $value->toArray();
            }

            if ($value instanceof Enumerable) {
                return $value->all();
            }

            // Check for Laravel's Jsonable interface
            if ($value instanceof Jsonable) {
                $value = $value->toJson();
            }

            // Check for PHP's JsonSerializable interface
            if ($value instanceof JsonSerializable) {
                $value = json_encode($value->jsonSerialize());
            }

            // Check for native PHP 8 Stringable interface
            if ($value instanceof StringableContract) {
                $value = (string) $value;
            }

            if (is_string($value) && json_validate($value)) {
                return json_decode($value, true);
            }

            // For skalarer og andre objekttyper, bruk (array) type-casting.
            return (array) $value;
        };
    }

    public function arrayOrNull(): Closure
    {
        return static function (ArrayAccess|array $source, string|int|null $key): ?array {
            $value = Arr::get($source, $key);

            if ($value === null) {
                return null;
            }

            if (is_array($value)) {
                return $value;
            }

            if ($value instanceof Arrayable) {
                return $value->toArray();
            }

            if ($value instanceof Enumerable) {
                return $value->all();
            }

            // Check for Laravel's Jsonable interface
            if ($value instanceof Jsonable) {
                $value = $value->toJson();
            }

            // Check for PHP's JsonSerializable interface
            if ($value instanceof JsonSerializable) {
                $value = json_encode($value->jsonSerialize());
            }

            // Check for native PHP 8 Stringable interface
            if ($value instanceof StringableContract) {
                $value = (string) $value;
            }

            if (is_string($value) && json_validate($value)) {
                return json_decode($value, true);
            }

            // For skalarer og andre objekttyper, bruk (array) type-casting.
            return (array) $value;
        };
    }

    public function collection(): Closure
    {
        return static function (ArrayAccess|array $source, string|int|null $key, Collection|array $default = new Collection): Collection {
            $method = new ArrMixin()->toArrayOrNull();
            $array = $method($source, $key);

            if ($array === null) {
                return $default instanceof Collection ? $default : new Collection($default);
            }

            return new Collection($array);
        };
    }

    public function collectionOrNull(): Closure
    {
        return static function (ArrayAccess|array $source, string|int|null $key): ?Collection {
            $method = new ArrMixin()->toArrayOrNull();
            $array = $method($source, $key);

            if ($array === null) {
                return null;
            }

            return new Collection($array);
        };
    }

    public function integer(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key, int $default = 0): int {
            $value = Arr::get($array, $key, $default);
            if ($value === null) {
                return $default;
            }

            if (is_bool($value)) {
                return $value ? 1 : 0;
            }

            return is_numeric($value) ? (int) $value : $default;
        };
    }

    public function integerOrNull(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key): ?int {
            $value = Arr::get($array, $key);
            if (is_bool($value)) {
                return $value ? 1 : 0;
            }

            return $value === null ? null : (is_numeric($value) ? (int) $value : null);
        };
    }

    public function float(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key, float $default = 0.0): float {
            $value = Arr::get($array, $key, $default);
            if ($value === null) {
                return $default;
            }

            if (is_bool($value)) {
                return $value ? 1 : 0;
            }

            return is_numeric($value) ? (float) $value : $default;
        };
    }

    public function floatOrNull(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key): ?float {
            $value = Arr::get($array, $key);
            if (is_bool($value)) {
                return $value ? 1 : 0;
            }

            return $value === null ? null : (is_numeric($value) ? (float) $value : null);
        };
    }

    public function boolean(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key, bool $default = false): bool {
            $value = Arr::get($array, $key, $default);

            if ($value === null) {
                return $default;
            }

            if (is_bool($value)) {
                return $value;
            }

            if (is_string($value)) {
                $value = mb_strtolower($value);
                $trueValues = ['true', '1', 'yes', 'on'];
                $falseValues = ['false', '0', 'no', 'off'];

                if (in_array($value, $trueValues, true)) {
                    return true;
                }

                if (in_array($value, $falseValues, true)) {
                    return false;
                }
            }

            if (is_numeric($value)) {
                return (bool) $value;
            }

            return $default;
        };
    }

    public function booleanOrNull(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key): ?bool {
            $value = Arr::get($array, $key);

            if ($value === null) {
                return null;
            }

            if (is_bool($value)) {
                return $value;
            }

            if (is_string($value)) {
                $value = mb_strtolower($value);
                $trueValues = ['true', '1', 'yes', 'on'];
                $falseValues = ['false', '0', 'no', 'off'];

                if (in_array($value, $trueValues, true)) {
                    return true;
                }

                if (in_array($value, $falseValues, true)) {
                    return false;
                }
            }

            if (is_numeric($value)) {
                return (bool) $value;
            }

            return null;
        };
    }

    public function date(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key, $default = null): CarbonImmutable {
            $value = Arr::get($array, $key);

            if ($value === null) {
                if ($default !== null) {
                    try {
                        return CarbonImmutable::parse($default)->startOfDay();
                    } catch (Exception) {
                        return CarbonImmutable::now()->startOfDay();
                    }
                }

                return CarbonImmutable::now()->startOfDay();
            }

            if ($value instanceof DateTimeInterface) {
                return CarbonImmutable::instance($value)->startOfDay();
            }

            try {
                return CarbonImmutable::parse($value)->startOfDay();
            } catch (Exception) {
                if ($default !== null) {
                    try {
                        return CarbonImmutable::parse($default)->startOfDay();
                    } catch (Exception) {
                        return CarbonImmutable::now()->startOfDay();
                    }
                }

                return CarbonImmutable::now()->startOfDay();
            }
        };
    }

    public function dateOrNull(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key): ?CarbonImmutable {
            $value = Arr::get($array, $key);

            if ($value === null) {
                return null;
            }

            if ($value instanceof DateTimeInterface) {
                return CarbonImmutable::instance($value)->startOfDay();
            }

            try {
                return CarbonImmutable::parse($value)->startOfDay();
            } catch (Exception) {
                return null;
            }
        };
    }

    public function dateTime(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key, $default = null): CarbonInterface {
            $value = Arr::get($array, $key);

            if ($value === null) {
                if ($default !== null) {
                    try {
                        return CarbonImmutable::parse($default);
                    } catch (Exception) {
                        return CarbonImmutable::now();
                    }
                }

                return CarbonImmutable::now();
            }

            if ($value instanceof DateTimeInterface) {
                return CarbonImmutable::instance($value);
            }

            try {
                // Preserve full timestamp for dateTime functions
                return CarbonImmutable::parse($value);
            } catch (Exception) {
                if ($default !== null) {
                    try {
                        return CarbonImmutable::parse($default);
                    } catch (Exception) {
                        return CarbonImmutable::now();
                    }
                }

                return CarbonImmutable::now();
            }
        };
    }

    public function dateTimeOrNull(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key): ?CarbonInterface {
            $value = Arr::get($array, $key);

            if ($value === null) {
                return null;
            }

            if ($value instanceof DateTimeInterface) {
                return CarbonImmutable::instance($value);
            }

            try {
                return CarbonImmutable::parse($value);
            } catch (Exception) {
                return null;
            }
        };
    }

    public function toString(): Closure
    {
        return $this->string();
    }

    public function toStringOrNull(): Closure
    {
        return $this->stringOrNull();
    }

    public function toStringable(): Closure
    {
        return $this->stringable();
    }

    public function toStringableOrNull(): Closure
    {
        return $this->stringableOrNull();
    }

    public function toInteger(): Closure
    {
        return $this->integer();
    }

    public function toIntegerOrNull(): Closure
    {
        return $this->integerOrNull();
    }

    public function toFloat(): Closure
    {
        return $this->float();
    }

    public function toFloatOrNull(): Closure
    {
        return $this->floatOrNull();
    }

    public function toBoolean(): Closure
    {
        return $this->boolean();
    }

    public function toBooleanOrNull(): Closure
    {
        return $this->booleanOrNull();
    }

    public function toDate(): Closure
    {
        return $this->date();
    }

    public function toDateOrNull(): Closure
    {
        return $this->dateOrNull();
    }

    public function toDateTime(): Closure
    {
        return $this->dateTime();
    }

    public function toDateTimeOrNull(): Closure
    {
        return $this->dateTimeOrNull();
    }

    public function toArray(): Closure
    {
        return $this->array();
    }

    public function toArrayOrNull(): Closure
    {
        return $this->arrayOrNull();
    }

    public function toCollection(): Closure
    {
        return $this->collection();
    }

    public function toCollectionOrNull(): Closure
    {
        return $this->collectionOrNull();
    }

    /**
     * Determines if a variable can be cast to a string.
     *
     * @param  mixed  $variable  The variable to check
     * @return bool True if the variable can be cast to a string, false otherwise
     */
    private function canBeCastToString(mixed $variable): bool
    {
        // Check for native PHP 8 Stringable interface
        if ($variable instanceof StringableContract) {
            return true;
        }

        // Check for Laravel's Jsonable interface
        if ($variable instanceof Jsonable) {
            return true;
        }

        // Check for PHP's JsonSerializable interface
        if ($variable instanceof JsonSerializable) {
            // Note: This doesn't guarantee a string result, as jsonSerialize can return any type
            // You might want to add additional checks here
            return true;
        }

        // Scalar types (excludes null)
        if (is_scalar($variable)) {
            return true;
        }

        // Resources can be converted
        if (is_resource($variable)) {
            return true;
        }

        // Resources can be converted
        // Arrays and null cannot be converted to strings directly
        return is_array($variable);
    }

    /**
     * Determines if a variable can be cast to a string.
     *
     * @param  mixed  $variable  The variable to check
     * @return bool True if the variable should be cast to a JSON string, false otherwise
     */
    private function shouldCastToJson(mixed $variable): bool
    {
        // Check for native PHP 8 Stringable interface
        if ($variable instanceof StringableContract) {
            return false;
        }

        // Check for Laravel's Jsonable interface
        if ($variable instanceof Jsonable) {
            return true;
        }

        // Check for PHP's JsonSerializable interface
        if ($variable instanceof JsonSerializable) {
            return true;
        }

        // Resources can be converted
        // Arrays and null cannot be converted to strings directly
        return is_array($variable);
    }
}
