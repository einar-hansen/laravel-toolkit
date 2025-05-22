<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Mixins;

use ArrayAccess;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Closure;
use DateTimeInterface;
use EinarHansen\Toolkit\Utilities\StringHelper;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use JsonSerializable;
use Stringable as StringableContract;

final class ArrMixin
{
    public function tryKeys(): Closure
    {
        return function (array $array, string|array|null $keys = null, string ...$additionalKeys): mixed {
            if ($keys === null) {
                $keys = (array) null;
            }

            // If the first argument is an array, use it as the keys
            // Otherwise, combine the first key with any additional keys
            $keysToTry = is_array($keys) ? $keys : array_merge([$keys], $additionalKeys);

            foreach ($keysToTry as $key) {
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

    public function toString(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key, string $default = '', bool $emptyStringAsNull = false): string {
            $value = Arr::get($array, $key, $default);

            if (StringHelper::canBeCastToString($value)) {
                if (StringHelper::shouldCastToJson($value)) {
                    $value = json_encode($value);
                }

                $stringValue = is_string($value) ? $value : (string) $value;

                if ($emptyStringAsNull && $stringValue === '') {
                    return $default;
                }

                return $stringValue;
            }

            return $default;

        };
    }

    public function toStringOrNull(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key, bool $emptyStringAsNull = false): ?string {
            $value = Arr::get($array, $key);

            if (StringHelper::canBeCastToString($value)) {
                if (StringHelper::shouldCastToJson($value)) {
                    $value = json_encode($value);
                }

                $stringValue = is_string($value) ? $value : (string) $value;

                if ($emptyStringAsNull && $stringValue === '') {
                    return null;
                }

                return $stringValue;
            }

            return null;
        };
    }

    public function toStringable(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key, Stringable|string $default = '', bool $emptyStringAsNull = false): Stringable {
            $value = Arr::get($array, $key, $default);

            if (StringHelper::canBeCastToString($value)) {
                if (StringHelper::shouldCastToJson($value)) {
                    $value = json_encode($value);
                }

                $stringValue = is_string($value) ? $value : (string) $value;

                if ($emptyStringAsNull && $stringValue === '') {
                    return new Stringable($default);
                }

                return new Stringable($stringValue);
            }

            return new Stringable($default);

        };
    }

    public function toStringableOrNull(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key, bool $emptyStringAsNull = false): ?Stringable {
            $value = Arr::get($array, $key);

            if (StringHelper::canBeCastToString($value)) {
                if (StringHelper::shouldCastToJson($value)) {
                    $value = json_encode($value);
                }

                $stringValue = is_string($value) ? $value : (string) $value;

                if ($emptyStringAsNull && $stringValue === '') {
                    return null;
                }

                return new Stringable($stringValue);
            }

            return null;
        };
    }

    public function toArray(): Closure
    {
        return static function (ArrayAccess|array $source, string|int|null $key, array $default = [], bool $emptyArrayAsNull = false): array {
            $value = Arr::get($source, $key);

            if ($value === null) {
                return $default;
            }

            if (is_array($value)) {
                if ($emptyArrayAsNull && $value === []) {
                    return $default;
                }

                return $value;
            }

            if ($value instanceof Arrayable) {
                return $value->toArray();
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

    public function toArrayOrNull(): Closure
    {
        return static function (ArrayAccess|array $source, string|int|null $key, bool $emptyArrayAsNull = false): ?array {
            $value = Arr::get($source, $key);

            if ($value === null) {
                return null;
            }

            if (is_array($value)) {
                if ($emptyArrayAsNull && $value === []) {
                    return null;
                }

                return $value;
            }

            if ($value instanceof Arrayable) {
                return $value->toArray();
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

    public function toCollection(): Closure
    {
        return static function (ArrayAccess|array $source, string|int|null $key, Collection|array $default = new Collection, bool $emptyArrayAsNull = false): Collection {
            $method = new ArrMixin()->toArrayOrNull();
            $array = $method($source, $key, $emptyArrayAsNull);

            if ($array === null) {
                return $default instanceof Collection ? $default : new Collection($default);
            }

            return new Collection($array);
        };
    }

    public function toCollectionOrNull(): Closure
    {
        return static function (ArrayAccess|array $source, string|int|null $key, bool $emptyArrayAsNull = false): ?Collection {
            $method = new ArrMixin()->toArrayOrNull();
            $array = $method($source, $key, $emptyArrayAsNull);

            if ($array === null) {
                return null;
            }

            return new Collection($array);
        };
    }

    public function toInteger(): Closure
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

    public function toIntegerOrNull(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key): ?int {
            $value = Arr::get($array, $key);
            if (is_bool($value)) {
                return $value ? 1 : 0;
            }

            return $value === null ? null : (is_numeric($value) ? (int) $value : null);
        };
    }

    public function toFloat(): Closure
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

    public function toFloatOrNull(): Closure
    {
        return function (ArrayAccess|array $array, string|int|null $key): ?float {
            $value = Arr::get($array, $key);
            if (is_bool($value)) {
                return $value ? 1 : 0;
            }

            return $value === null ? null : (is_numeric($value) ? (float) $value : null);
        };
    }

    public function toBoolean(): Closure
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

    public function toBooleanOrNull(): Closure
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

    public function toDate(): Closure
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

    public function toDateOrNull(): Closure
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

    public function toDateTime(): Closure
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

    public function toDateTimeOrNull(): Closure
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

    /**
     * Wrap the given value in a list collection if applicable.
     */
    public function wrapList(): Closure
    {
        return function (mixed $value): array {
            $array = Arr::wrap($value);
            if (Arr::isAssoc($array)) {
                return [$array];
            }

            return $array;
        };
    }
}
