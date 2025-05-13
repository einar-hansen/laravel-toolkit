<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Utilities;

use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Stringable as StringableContract;

class StringHelper
{
    /**
     * Determines if a variable can be cast to a string.
     *
     * @param  mixed  $variable  The variable to check
     * @return bool True if the variable can be cast to a string, false otherwise
     */
    public static function canBeCastToString(mixed $variable): bool
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
    public static function shouldCastToJson(mixed $variable): bool
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
