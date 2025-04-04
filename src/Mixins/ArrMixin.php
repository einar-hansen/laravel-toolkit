<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Mixins;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Closure;
use DateTimeInterface;
use Exception;
use Illuminate\Support\Arr;

class ArrMixin
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
        return function (array $array, string $key): bool {
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
        return fn (array $array, string $key): bool => empty(Arr::get($array, $key));
    }

    public function string(): Closure
    {
        return function (array $array, string $key, string $default = ''): string {
            $value = Arr::get($array, $key, $default);

            if ($value === null) {
                return $default;
            }

            return is_string($value) ? $value : (string) $value;
        };
    }

    public function stringOrNull(): Closure
    {
        return function (array $array, string $key): ?string {
            $value = Arr::get($array, $key);

            return $value === null ? null : (string) $value;
        };
    }

    public function integer(): Closure
    {
        return function (array $array, string $key, int $default = 0): int {
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
        return function (array $array, string $key): ?int {
            $value = Arr::get($array, $key);
            if (is_bool($value)) {
                return $value ? 1 : 0;
            }

            return $value === null ? null : (is_numeric($value) ? (int) $value : null);
        };
    }

    public function float(): Closure
    {
        return function (array $array, string $key, float $default = 0.0): float {
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
        return function (array $array, string $key): ?float {
            $value = Arr::get($array, $key);
            if (is_bool($value)) {
                return $value ? 1 : 0;
            }

            return $value === null ? null : (is_numeric($value) ? (float) $value : null);
        };
    }

    public function boolean(): Closure
    {
        return function (array $array, string $key, bool $default = false): bool {
            $value = Arr::get($array, $key, $default);

            if ($value === null) {
                return $default;
            }

            if (is_bool($value)) {
                return $value;
            }

            if (is_string($value)) {
                $value = strtolower($value);
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
        return function (array $array, string $key): ?bool {
            $value = Arr::get($array, $key);

            if ($value === null) {
                return null;
            }

            if (is_bool($value)) {
                return $value;
            }

            if (is_string($value)) {
                $value = strtolower($value);
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
        return function (array $array, string $key, $default = null): CarbonImmutable {
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
        return function (array $array, string $key): ?CarbonImmutable {
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
        return function (array $array, string $key, $default = null): CarbonInterface {
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
        return function (array $array, string $key): ?CarbonInterface {
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
}
