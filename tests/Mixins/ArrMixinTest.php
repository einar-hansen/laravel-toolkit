<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Mixins;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeImmutable;
use EinarHansen\Toolkit\Mixins\ArrMixin;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use JsonSerializable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stringable as StringableContract;

#[CoversClass(ArrMixin::class)]
final class ArrMixinTest extends TestCase
{
    private ArrMixin $arrMixin;

    private CarbonImmutable $knownDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->arrMixin = new ArrMixin;
        // Set a known date for predictable 'now' in tests
        $this->knownDate = CarbonImmutable::create(2024, 5, 15, 12, 30, 45, 'UTC');
        CarbonImmutable::setTestNow($this->knownDate);
    }

    protected function tearDown(): void
    {
        // Reset the 'now' instance
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public static function tryKeysProvider(): array
    {
        $data = [
            'name' => 'Einar',
            'alias' => 'EH',
            'nested' => ['value' => 'Nested Value'],
            'zero' => 0,
            'false_val' => false,
            'empty_string' => '',
        ];

        return [
            'first key exists' => [$data, ['name', 'username'], 'Einar'],
            'second key exists' => [$data, ['username', 'alias'], 'EH'],
            'no keys exist' => [$data, ['username', 'handle'], null],
            'nested key first' => [$data, ['nested.value', 'name'], 'Nested Value'],
            'nested key second' => [$data, ['missing.key', 'nested.value'], 'Nested Value'],
            'no nested keys exist' => [$data, ['missing.key', 'nested.missing'], null],
            'finds zero' => [$data, ['missing', 'zero'], 0],
            'finds false' => [$data, ['missing', 'false_val'], false],
            'finds empty string' => [$data, ['missing', 'empty_string'], ''],
            'no keys provided' => [$data, [], null],
            'empty array' => [[], ['name', 'alias'], null],
        ];
    }

    public static function issetProvider(): array
    {
        $data = [
            'string_val' => 'hello',
            'int_val' => 123,
            'zero_int' => 0,         // isset(0) is true
            'float_val' => 1.23,
            'zero_float' => 0.0,       // isset(0.0) is true
            'bool_true' => true,       // isset(true) is true
            'bool_false' => false,      // isset(false) is true
            'null_val' => null,        // isset(null) is false
            'empty_string' => '',       // isset('') is true
            'zero_string' => '0',        // isset('0') is true
            'empty_array' => [],       // isset([]) is true
            'non_empty_array' => [1], // isset([1]) is true
            'nested' => [
                'level1' => 'value1',
                'level1_null' => null,
                'level1_zero' => 0,
                'level1_false' => false,
                'level1_empty_string' => '',
            ],
        ];

        return [
            'string value' => [$data, 'string_val', true],
            'integer value' => [$data, 'int_val', true],
            'zero integer value' => [$data, 'zero_int', true],
            'float value' => [$data, 'float_val', true],
            'zero float value' => [$data, 'zero_float', true],
            'boolean true value' => [$data, 'bool_true', true],
            'boolean false value' => [$data, 'bool_false', true], // Important: isset(false) is true
            'null value' => [$data, 'null_val', false],            // Important: isset(null) is false
            'empty string value' => [$data, 'empty_string', true],
            'zero string value' => [$data, 'zero_string', true],
            'empty array value' => [$data, 'empty_array', true],
            'non-empty array value' => [$data, 'non_empty_array', true],
            'non-existent key' => [$data, 'non_existent_key', false],
            'nested existing value' => [$data, 'nested.level1', true],
            'nested null value' => [$data, 'nested.level1_null', false],
            'nested zero value' => [$data, 'nested.level1_zero', true],
            'nested false value' => [$data, 'nested.level1_false', true],
            'nested empty string value' => [$data, 'nested.level1_empty_string', true],
            'nested non-existent key' => [$data, 'nested.missing', false],
            'nested key path partly exists' => [$data, 'string_val.missing', false], // 'string_val' exists but is not array
            'nested path to top-level array' => [$data, 'nested', true], // 'nested' key itself exists and is not null
            'empty input array' => [[], 'any_key', false],
        ];
    }

    public static function isEmptyProvider(): array
    {
        $data = [
            'string_val' => 'hello',   // not empty
            'int_val' => 123,      // not empty
            'zero_int' => 0,         // empty
            'float_val' => 1.23,     // not empty
            'zero_float' => 0.0,       // empty
            'bool_true' => true,       // not empty
            'bool_false' => false,      // empty
            'null_val' => null,        // empty
            'empty_string' => '',       // empty
            'zero_string' => '0',        // empty
            'empty_array' => [],       // empty
            'non_empty_array' => [1], // not empty
            'nested' => [
                'level1' => 'value1',          // not empty
                'level1_null' => null,         // empty
                'level1_zero' => 0,            // empty
                'level1_false' => false,       // empty
                'level1_empty_string' => '',   // empty
                'level1_non_empty_array' => [0], // not empty
            ],
        ];

        return [
            'string value' => [$data, 'string_val', false],
            'integer value' => [$data, 'int_val', false],
            'zero integer value' => [$data, 'zero_int', true],
            'float value' => [$data, 'float_val', false],
            'zero float value' => [$data, 'zero_float', true],
            'boolean true value' => [$data, 'bool_true', false],
            'boolean false value' => [$data, 'bool_false', true],
            'null value' => [$data, 'null_val', true],
            'empty string value' => [$data, 'empty_string', true],
            'zero string value' => [$data, 'zero_string', true],
            'empty array value' => [$data, 'empty_array', true],
            'non-empty array value' => [$data, 'non_empty_array', false],
            'non-existent key' => [$data, 'non_existent_key', true], // Important: non-existent is empty
            'nested existing value' => [$data, 'nested.level1', false],
            'nested null value' => [$data, 'nested.level1_null', true],
            'nested zero value' => [$data, 'nested.level1_zero', true],
            'nested false value' => [$data, 'nested.level1_false', true],
            'nested empty string value' => [$data, 'nested.level1_empty_string', true],
            'nested non-empty array value' => [$data, 'nested.level1_non_empty_array', false],
            'nested non-existent key' => [$data, 'nested.missing', true],
            'nested key path partly exists' => [$data, 'string_val.missing', true], // Arr::get returns null, empty(null) is true
            'nested path to non-empty top-level array' => [$data, 'nested', false], // 'nested' key resolves to a non-empty array
            'empty input array' => [[], 'any_key', true],
        ];
    }

    public static function stringProvider(): array
    {
        $data = [
            'name' => 'Einar',
            'age' => 30,
            'height' => 1.85,
            'active' => true,
            'zero' => 0,
            'null_val' => null,
            'nested' => ['value' => 'Nested String'],
        ];

        return [
            'existing string' => [$data, 'name', 'default', 'Einar'],
            'existing integer' => [$data, 'age', 'default', '30'],
            'existing float' => [$data, 'height', 'default', '1.85'],
            'existing boolean true' => [$data, 'active', 'default', '1'], // PHP casts true to '1'
            'existing boolean false' => [['active' => false], 'active', 'default', ''], // PHP casts false to ''
            'existing zero' => [$data, 'zero', 'default', '0'],
            'existing null' => [$data, 'null_val', 'default_string', 'default_string'],
            'missing key' => [$data, 'missing', 'fallback', 'fallback'],
            'nested key' => [$data, 'nested.value', 'default', 'Nested String'],
            'missing nested key' => [$data, 'nested.missing', 'fallback_nested', 'fallback_nested'],
            'empty array' => [[], 'name', 'empty_default', 'empty_default'],
            'default value used when key exists but value is null' => [['value' => null], 'value', 'was_null', 'was_null'],
        ];
    }

    public static function stringOrNullProvider(): array
    {
        $data = [
            'name' => 'Einar',
            'age' => 30,
            'height' => 1.85,
            'active' => true,
            'zero' => 0,
            'null_val' => null,
            'nested' => ['value' => 'Nested String'],
        ];

        return [
            'existing string' => [$data, 'name', 'Einar'],
            'existing integer' => [$data, 'age', '30'],
            'existing float' => [$data, 'height', '1.85'],
            'existing boolean true' => [$data, 'active', '1'],
            'existing boolean false' => [['active' => false], 'active', ''],
            'existing zero' => [$data, 'zero', '0'],
            'existing null' => [$data, 'null_val', null],
            'missing key' => [$data, 'missing', null],
            'nested key' => [$data, 'nested.value', 'Nested String'],
            'missing nested key' => [$data, 'nested.missing', null],
            'empty array' => [[], 'name', null],
        ];
    }

    public static function integerProvider(): array
    {
        $data = [
            'age' => 30,
            'count_str' => '123',
            'float_val' => 45.67,
            'float_str' => '89.12',
            'negative_int' => -5,
            'negative_str' => '-10',
            'zero' => 0,
            'zero_str' => '0',
            'bool_true' => true,
            'bool_false' => false,
            'string_val' => 'abc',
            'null_val' => null,
            'nested' => ['count' => 99],
        ];

        return [
            'existing integer' => [$data, 'age', 0, 30],
            'existing integer string' => [$data, 'count_str', 0, 123],
            'existing float' => [$data, 'float_val', 0, 45], // Cast truncates
            'existing float string' => [$data, 'float_str', 0, 89], // Cast truncates
            'existing negative integer' => [$data, 'negative_int', 0, -5],
            'existing negative integer string' => [$data, 'negative_str', 0, -10],
            'existing zero' => [$data, 'zero', 5, 0],
            'existing zero string' => [$data, 'zero_str', 5, 0],
            'existing bool true' => [$data, 'bool_true', 0, 1], // Casts to 1
            'existing bool false' => [$data, 'bool_false', 5, 0], // Casts to 0
            'non-numeric string' => [$data, 'string_val', 999, 999], // Uses default
            'existing null' => [$data, 'null_val', 555, 555], // Uses default
            'missing key' => [$data, 'missing', 111, 111], // Uses default
            'nested key' => [$data, 'nested.count', 0, 99],
            'missing nested key' => [$data, 'nested.missing', 222, 222],
            'empty array' => [[], 'age', 777, 777],
        ];
    }

    public static function integerOrNullProvider(): array
    {
        $data = [
            'age' => 30,
            'count_str' => '123',
            'float_val' => 45.67,
            'float_str' => '89.12',
            'negative_int' => -5,
            'negative_str' => '-10',
            'zero' => 0,
            'zero_str' => '0',
            'bool_true' => true,
            'bool_false' => false,
            'string_val' => 'abc',
            'null_val' => null,
            'nested' => ['count' => 99],
        ];

        return [
            'existing integer' => [$data, 'age', 30],
            'existing integer string' => [$data, 'count_str', 123],
            'existing float' => [$data, 'float_val', 45],
            'existing float string' => [$data, 'float_str', 89],
            'existing negative integer' => [$data, 'negative_int', -5],
            'existing negative integer string' => [$data, 'negative_str', -10],
            'existing zero' => [$data, 'zero', 0],
            'existing zero string' => [$data, 'zero_str', 0],
            'existing bool true' => [$data, 'bool_true', 1], // is_numeric is true, cast is 1
            'existing bool false' => [$data, 'bool_false', 0], // is_numeric is true, cast is 0
            'non-numeric string' => [$data, 'string_val', null], // Not numeric
            'existing null' => [$data, 'null_val', null], // Value is null
            'missing key' => [$data, 'missing', null], // Value is null
            'nested key' => [$data, 'nested.count', 99],
            'missing nested key' => [$data, 'nested.missing', null],
            'empty array' => [[], 'age', null],
        ];
    }

    public static function floatProvider(): array
    {
        $data = [
            'price' => 19.99,
            'price_str' => '29.50',
            'int_val' => 45,
            'int_str' => '89',
            'negative_float' => -5.5,
            'negative_str' => '-10.75',
            'zero' => 0.0,
            'zero_str' => '0.0',
            'bool_true' => true,
            'bool_false' => false,
            'string_val' => 'abc',
            'null_val' => null,
            'nested' => ['rate' => 0.05],
        ];

        return [
            'existing float' => [$data, 'price', 0.0, 19.99],
            'existing float string' => [$data, 'price_str', 0.0, 29.50],
            'existing integer' => [$data, 'int_val', 0.0, 45.0],
            'existing integer string' => [$data, 'int_str', 0.0, 89.0],
            'existing negative float' => [$data, 'negative_float', 0.0, -5.5],
            'existing negative float string' => [$data, 'negative_str', 0.0, -10.75],
            'existing zero float' => [$data, 'zero', 5.0, 0.0],
            'existing zero string' => [$data, 'zero_str', 5.0, 0.0],
            'existing bool true' => [$data, 'bool_true', 0.0, 1.0], // Casts to 1.0
            'existing bool false' => [$data, 'bool_false', 5.0, 0.0], // Casts to 0.0
            'non-numeric string' => [$data, 'string_val', 99.9, 99.9], // Uses default
            'existing null' => [$data, 'null_val', 55.5, 55.5], // Uses default
            'missing key' => [$data, 'missing', 11.1, 11.1], // Uses default
            'nested key' => [$data, 'nested.rate', 0.0, 0.05],
            'missing nested key' => [$data, 'nested.missing', 2.22, 2.22],
            'empty array' => [[], 'price', 77.7, 77.7],
        ];
    }

    public static function floatOrNullProvider(): array
    {
        $data = [
            'price' => 19.99,
            'price_str' => '29.50',
            'int_val' => 45,
            'int_str' => '89',
            'negative_float' => -5.5,
            'negative_str' => '-10.75',
            'zero' => 0.0,
            'zero_str' => '0.0',
            'bool_true' => true,
            'bool_false' => false,
            'string_val' => 'abc',
            'null_val' => null,
            'nested' => ['rate' => 0.05],
        ];

        return [
            'existing float' => [$data, 'price', 19.99],
            'existing float string' => [$data, 'price_str', 29.50],
            'existing integer' => [$data, 'int_val', 45.0],
            'existing integer string' => [$data, 'int_str', 89.0],
            'existing negative float' => [$data, 'negative_float', -5.5],
            'existing negative float string' => [$data, 'negative_str', -10.75],
            'existing zero float' => [$data, 'zero', 0.0],
            'existing zero string' => [$data, 'zero_str', 0.0],
            'existing bool true' => [$data, 'bool_true', 1.0], // is_numeric true
            'existing bool false' => [$data, 'bool_false', 0.0], // is_numeric true
            'non-numeric string' => [$data, 'string_val', null], // Not numeric
            'existing null' => [$data, 'null_val', null], // Value is null
            'missing key' => [$data, 'missing', null], // Value is null
            'nested key' => [$data, 'nested.rate', 0.05],
            'missing nested key' => [$data, 'nested.missing', null],
            'empty array' => [[], 'price', null],
        ];
    }

    public static function booleanProvider(): array
    {
        $data = [
            'is_active_true' => true,
            'is_active_false' => false,
            'str_true' => 'true',
            'str_false' => 'false',
            'str_TRUE' => 'TRUE',
            'str_FALSE' => 'FALSE',
            'str_1' => '1',
            'str_0' => '0',
            'str_yes' => 'yes',
            'str_no' => 'no',
            'str_on' => 'on',
            'str_off' => 'off',
            'int_1' => 1,
            'int_0' => 0,
            'int_5' => 5,      // numeric evaluates to true
            'int_neg_1' => -1, // numeric evaluates to true
            'float_1' => 1.0,
            'float_0' => 0.0,
            'float_non_zero' => 0.1,
            'string_random' => 'random',
            'null_val' => null,
            'nested' => ['flag' => 'yes'],
        ];

        return [
            // Boolean values
            'actual true' => [$data, 'is_active_true', false, true],
            'actual false' => [$data, 'is_active_false', true, false],
            // String representations (case-insensitive)
            'string true lower' => [$data, 'str_true', false, true],
            'string false lower' => [$data, 'str_false', true, false],
            'string true upper' => [$data, 'str_TRUE', false, true],
            'string false upper' => [$data, 'str_FALSE', true, false],
            'string 1' => [$data, 'str_1', false, true],
            'string 0' => [$data, 'str_0', true, false],
            'string yes' => [$data, 'str_yes', false, true],
            'string no' => [$data, 'str_no', true, false],
            'string on' => [$data, 'str_on', false, true],
            'string off' => [$data, 'str_off', true, false],
            // Numeric representations
            'integer 1' => [$data, 'int_1', false, true],
            'integer 0' => [$data, 'int_0', true, false],
            'integer non-zero' => [$data, 'int_5', false, true],
            'integer negative' => [$data, 'int_neg_1', false, true],
            'float 1.0' => [$data, 'float_1', false, true],
            'float 0.0' => [$data, 'float_0', true, false],
            'float non-zero' => [$data, 'float_non_zero', false, true],
            // Default cases
            'random string (default false)' => [$data, 'string_random', false, false],
            'random string (default true)' => [$data, 'string_random', true, true],
            'null value (default false)' => [$data, 'null_val', false, false],
            'null value (default true)' => [$data, 'null_val', true, true],
            'missing key (default false)' => [$data, 'missing', false, false],
            'missing key (default true)' => [$data, 'missing', true, true],
            // Nested
            'nested key' => [$data, 'nested.flag', false, true],
            'missing nested key (default true)' => [$data, 'nested.missing', true, true],
            'empty array (default false)' => [[], 'flag', false, false],
        ];
    }

    public static function booleanOrNullProvider(): array
    {
        // Reuse most cases from booleanProvider but change expectation to null for defaults/invalid
        $cases = self::booleanProvider();
        $orNullCases = [];
        foreach ($cases as $name => $case) {
            // If the original expected value matched the default, it means the original value
            // was invalid/null/missing, so the *OrNull version should return null.
            // Keep cases where a valid boolean was found.
            if (str_contains($name, ' (default ') || str_contains($name, 'null value') || str_contains($name, 'missing key') || str_contains($name, 'random string')) {
                $orNullCases[$name.' -> null'] = [$case[0], $case[1], null];
            } else {
                // Remove the default value argument ($case[2])
                $orNullCases[$name] = [$case[0], $case[1], $case[3]];
            }
        }

        // Add specific check for empty array
        $orNullCases['empty array -> null'] = [[], 'flag', null];

        return $orNullCases;
    }

    public static function dateProvider(): array
    {
        $nowDateString = CarbonImmutable::create(2024, 5, 15, 12, 30, 45, 'UTC')->toDateString();
        $defaultDateString = '2023-01-01';
        $defaultDateTime = Carbon::parse('2023-01-01 15:00:00'); // Time should be ignored
        $validDateString = '2024-02-20';
        $validDateTimeString = '2024-03-10 10:20:30'; // Time should be ignored
        $validDateTimeObj = new DateTimeImmutable($validDateTimeString);
        $validCarbonObj = CarbonImmutable::parse($validDateTimeString);

        $data = [
            'date_str' => $validDateString,
            'datetime_str' => $validDateTimeString,
            'datetime_obj' => $validDateTimeObj,
            'carbon_obj' => $validCarbonObj,
            'invalid_str' => 'not-a-date',
            'null_val' => null,
            'nested' => ['event_date' => '2024-12-25'],
        ];

        return [
            'valid date string' => [$data, 'date_str', null, $validDateString],
            'valid datetime string' => [$data, 'datetime_str', null, '2024-03-10'],
            'valid DateTime object' => [$data, 'datetime_obj', null, '2024-03-10'],
            'valid Carbon object' => [$data, 'carbon_obj', null, '2024-03-10'],

            // Default handling (when value is invalid or missing)
            'invalid string, default null (uses now)' => [$data, 'invalid_str', null, $nowDateString],
            'null value, default null (uses now)' => [$data, 'null_val', null, $nowDateString],
            'missing key, default null (uses now)' => [$data, 'missing', null, $nowDateString],

            'invalid string, default string' => [$data, 'invalid_str', $defaultDateString, $defaultDateString],
            'null value, default string' => [$data, 'null_val', $defaultDateString, $defaultDateString],
            'missing key, default string' => [$data, 'missing', $defaultDateString, $defaultDateString],

            'invalid string, default DateTime' => [$data, 'invalid_str', $defaultDateTime, $defaultDateString],
            'null value, default DateTime' => [$data, 'null_val', $defaultDateTime, $defaultDateString],
            'missing key, default DateTime' => [$data, 'missing', $defaultDateTime, $defaultDateString],

            'invalid string, default invalid string (uses now)' => [$data, 'invalid_str', 'invalid-default', $nowDateString],
            'null value, default invalid string (uses now)' => [$data, 'null_val', 'invalid-default', $nowDateString],
            'missing key, default invalid string (uses now)' => [$data, 'missing', 'invalid-default', $nowDateString],

            // Nested
            'nested key' => [$data, 'nested.event_date', null, '2024-12-25'],
            'missing nested key (uses now)' => [$data, 'nested.missing', null, $nowDateString],
            'empty array (uses now)' => [[], 'date', null, $nowDateString],
        ];
    }

    public static function dateOrNullProvider(): array
    {
        $validDateString = '2024-02-20';
        $validDateTimeString = '2024-03-10 10:20:30'; // Time should be ignored
        $validDateTimeObj = new DateTimeImmutable($validDateTimeString);
        $validCarbonObj = CarbonImmutable::parse($validDateTimeString);

        $data = [
            'date_str' => $validDateString,
            'datetime_str' => $validDateTimeString,
            'datetime_obj' => $validDateTimeObj,
            'carbon_obj' => $validCarbonObj,
            'invalid_str' => 'not-a-date',
            'null_val' => null,
            'nested' => ['event_date' => '2024-12-25'],
        ];

        return [
            'valid date string' => [$data, 'date_str', $validDateString],
            'valid datetime string' => [$data, 'datetime_str', '2024-03-10'],
            'valid DateTime object' => [$data, 'datetime_obj', '2024-03-10'],
            'valid Carbon object' => [$data, 'carbon_obj', '2024-03-10'],
            'invalid string' => [$data, 'invalid_str', null],
            'null value' => [$data, 'null_val', null],
            'missing key' => [$data, 'missing', null],
            'nested key' => [$data, 'nested.event_date', '2024-12-25'],
            'missing nested key' => [$data, 'nested.missing', null],
            'empty array' => [[], 'date', null],
        ];
    }

    public static function dateTimeProvider(): array
    {
        $now = CarbonImmutable::create(2024, 5, 15, 12, 30, 45, 'UTC'); // Use the mocked 'now'
        $defaultDateTimeString = '2023-01-01 15:30:00';
        $defaultDateTime = CarbonImmutable::parse($defaultDateTimeString);
        $validDateTimeString = '2024-03-10 10:20:30';
        $validDateTime = CarbonImmutable::parse($validDateTimeString);
        $validDateTimeObj = new DateTimeImmutable($validDateTimeString); // Native DateTime
        $validCarbonObj = CarbonImmutable::parse($validDateTimeString); // CarbonImmutable

        $data = [
            'datetime_str' => $validDateTimeString,
            'datetime_obj' => $validDateTimeObj,
            'carbon_obj' => $validCarbonObj,
            'invalid_str' => 'not-a-datetime',
            'null_val' => null,
            'nested' => ['event_time' => '2024-12-25 18:00:00'],
        ];
        $nestedExpected = CarbonImmutable::parse('2024-12-25 18:00:00');

        return [
            'valid datetime string' => [$data, 'datetime_str', null, $validDateTime],
            'valid DateTime object' => [$data, 'datetime_obj', null, $validDateTime],
            'valid Carbon object' => [$data, 'carbon_obj', null, $validCarbonObj], // Should return instance

            // Default handling (when value is invalid or missing)
            'invalid string, default null (uses now)' => [$data, 'invalid_str', null, $now],
            'null value, default null (uses now)' => [$data, 'null_val', null, $now],
            'missing key, default null (uses now)' => [$data, 'missing', null, $now],

            'invalid string, default string' => [$data, 'invalid_str', $defaultDateTimeString, $defaultDateTime],
            'null value, default string' => [$data, 'null_val', $defaultDateTimeString, $defaultDateTime],
            'missing key, default string' => [$data, 'missing', $defaultDateTimeString, $defaultDateTime],

            'invalid string, default DateTime obj' => [$data, 'invalid_str', $defaultDateTime, $defaultDateTime],
            'null value, default DateTime obj' => [$data, 'null_val', $defaultDateTime, $defaultDateTime],
            'missing key, default DateTime obj' => [$data, 'missing', $defaultDateTime, $defaultDateTime],

            'invalid string, default invalid string (uses now)' => [$data, 'invalid_str', 'invalid-default', $now],
            'null value, default invalid string (uses now)' => [$data, 'null_val', 'invalid-default', $now],
            'missing key, default invalid string (uses now)' => [$data, 'missing', 'invalid-default', $now],

            // Nested
            'nested key' => [$data, 'nested.event_time', null, $nestedExpected],
            'missing nested key (uses now)' => [$data, 'nested.missing', null, $now],
            'empty array (uses now)' => [[], 'datetime', null, $now],
        ];
    }

    public static function dateTimeOrNullProvider(): array
    {
        $validDateTimeString = '2024-03-10 10:20:30';
        $validDateTime = CarbonImmutable::parse($validDateTimeString);
        $validDateTimeObj = new DateTimeImmutable($validDateTimeString);
        $validCarbonObj = CarbonImmutable::parse($validDateTimeString);

        $data = [
            'datetime_str' => $validDateTimeString,
            'datetime_obj' => $validDateTimeObj,
            'carbon_obj' => $validCarbonObj,
            'invalid_str' => 'not-a-datetime',
            'null_val' => null,
            'nested' => ['event_time' => '2024-12-25 18:00:00'],
        ];
        $nestedExpected = CarbonImmutable::parse('2024-12-25 18:00:00');

        return [
            'valid datetime string' => [$data, 'datetime_str', $validDateTime],
            'valid DateTime object' => [$data, 'datetime_obj', $validDateTime],
            'valid Carbon object' => [$data, 'carbon_obj', $validCarbonObj],
            'invalid string' => [$data, 'invalid_str', null],
            'null value' => [$data, 'null_val', null],
            'missing key' => [$data, 'missing', null],
            'nested key' => [$data, 'nested.event_time', $nestedExpected],
            'missing nested key' => [$data, 'nested.missing', null],
            'empty array' => [[], 'datetime', null],
        ];
    }

    public static function arrayProvider(): array
    {
        $arrayValue = ['key' => 'value', 'nested' => ['foo' => 'bar']];
        $arrayableObj = new class() implements Arrayable
        {
            public function toArray(): array
            {
                return ['from_arrayable' => 'value'];
            }
        };
        $enumerableObj = new Collection(['from_enumerable' => 'value']);

        $jsonableObj = new class() implements Jsonable
        {
            public function toJson($options = 0): string
            {
                return json_encode(['from_jsonable' => 'value'], $options);
            }
        };
        $jsonSerializableObj = new class() implements JsonSerializable
        {
            public function jsonSerialize(): mixed
            {
                return ['from_jsonSerializable' => 'value'];
            }
        };
        $stringableObj = new class() implements StringableContract
        {
            public function __toString(): string
            {
                return '{"from_stringable":"value"}';
            }
        };
        $invalidJsonString = '{"invalid":json';

        $data = [
            'array_val' => $arrayValue,
            'arrayable_val' => $arrayableObj,
            'enumerable_val' => $enumerableObj,
            'jsonable_val' => $jsonableObj,
            'json_serializable_val' => $jsonSerializableObj,
            'stringable_val' => $stringableObj,
            'json_string' => '{"from_json":"value"}',
            'invalid_json_string' => $invalidJsonString,
            'string_val' => 'simple string',
            'int_val' => 123,
            'float_val' => 45.67,
            'bool_val' => true,
            'null_val' => null,
            'nested' => ['array_key' => $arrayValue],
        ];

        return [
            'array value' => [$data, 'array_val', [], $arrayValue],
            'arrayable object' => [$data, 'arrayable_val', [], ['from_arrayable' => 'value']],
            'enumerable object' => [$data, 'enumerable_val', [], ['from_enumerable' => 'value']],
            'jsonable object' => [$data, 'jsonable_val', [], ['from_jsonable' => 'value']],
            'json serializable object' => [$data, 'json_serializable_val', [], ['from_jsonSerializable' => 'value']],
            'stringable object with valid JSON' => [$data, 'stringable_val', [], ['from_stringable' => 'value']],
            'JSON string' => [$data, 'json_string', [], ['from_json' => 'value']],
            'invalid JSON string' => [$data, 'invalid_json_string', [], [$invalidJsonString]],
            'simple string' => [$data, 'string_val', [], ['simple string']],
            'integer' => [$data, 'int_val', [], [123]],
            'float' => [$data, 'float_val', [], [45.67]],
            'boolean' => [$data, 'bool_val', [], [true]],
            'null value' => [$data, 'null_val', ['default' => 'array'], ['default' => 'array']],
            'missing key' => [$data, 'missing_key', ['default' => 'array'], ['default' => 'array']],
            'nested key' => [$data, 'nested.array_key', [], $arrayValue],
            'empty array' => [[], 'any_key', ['fallback'], ['fallback']],
        ];
    }

    public static function arrayOrNullProvider(): array
    {
        $arrayValue = ['key' => 'value', 'nested' => ['foo' => 'bar']];
        $arrayableObj = new class() implements Arrayable
        {
            public function toArray(): array
            {
                return ['from_arrayable' => 'value'];
            }
        };
        $enumerableObj = new Collection(['from_enumerable' => 'value']);

        $jsonableObj = new class() implements Jsonable
        {
            public function toJson($options = 0): string
            {
                return json_encode(['from_jsonable' => 'value'], $options);
            }
        };
        $jsonSerializableObj = new class() implements JsonSerializable
        {
            public function jsonSerialize(): mixed
            {
                return ['from_jsonSerializable' => 'value'];
            }
        };
        $stringableObj = new class() implements StringableContract
        {
            public function __toString(): string
            {
                return '{"from_stringable":"value"}';
            }
        };
        $invalidJsonString = '{"invalid":json';

        $data = [
            'array_val' => $arrayValue,
            'arrayable_val' => $arrayableObj,
            'enumerable_val' => $enumerableObj,
            'jsonable_val' => $jsonableObj,
            'json_serializable_val' => $jsonSerializableObj,
            'stringable_val' => $stringableObj,
            'json_string' => '{"from_json":"value"}',
            'invalid_json_string' => $invalidJsonString,
            'string_val' => 'simple string',
            'int_val' => 123,
            'float_val' => 45.67,
            'bool_val' => true,
            'null_val' => null,
            'nested' => ['array_key' => $arrayValue],
        ];

        return [
            'array value' => [$data, 'array_val', $arrayValue],
            'arrayable object' => [$data, 'arrayable_val', ['from_arrayable' => 'value']],
            'enumerable object' => [$data, 'enumerable_val', ['from_enumerable' => 'value']],
            'jsonable object' => [$data, 'jsonable_val', ['from_jsonable' => 'value']],
            'json serializable object' => [$data, 'json_serializable_val', ['from_jsonSerializable' => 'value']],
            'stringable object with valid JSON' => [$data, 'stringable_val', ['from_stringable' => 'value']],
            'JSON string' => [$data, 'json_string', ['from_json' => 'value']],
            'invalid JSON string' => [$data, 'invalid_json_string', [$invalidJsonString]],
            'simple string' => [$data, 'string_val', ['simple string']],
            'integer' => [$data, 'int_val', [123]],
            'float' => [$data, 'float_val', [45.67]],
            'boolean' => [$data, 'bool_val', [true]],
            'null value' => [$data, 'null_val', null],
            'missing key' => [$data, 'missing_key', null],
            'nested key' => [$data, 'nested.array_key', $arrayValue],
            'empty array' => [[], 'any_key', null],
        ];
    }

    public static function collectionProvider(): array
    {
        $arrayValue = ['key' => 'value', 'nested' => ['foo' => 'bar']];
        $arrayableObj = new class() implements Arrayable
        {
            public function toArray(): array
            {
                return ['from_arrayable' => 'value'];
            }
        };

        $enumerableObj = new Collection(['from_enumerable' => 'value']);

        $jsonableObj = new class() implements Jsonable
        {
            public function toJson($options = 0): string
            {
                return json_encode(['from_jsonable' => 'value'], $options);
            }
        };

        $data = [
            'array_val' => $arrayValue,
            'arrayable_val' => $arrayableObj,
            'enumerable_val' => $enumerableObj,
            'jsonable_val' => $jsonableObj,
            'json_string' => '{"from_json":"value"}',
            'string_val' => 'simple string',
            'int_val' => 123,
            'null_val' => null,
            'nested' => ['array_key' => $arrayValue],
        ];

        $defaultCollection = new Collection(['default' => 'collection']);
        $defaultArray = ['default' => 'array'];

        return [
            'array value with no default' => [
                $data, 'array_val', new Collection(),
                new Collection($arrayValue),
            ],
            'arrayable object with collection default' => [
                $data, 'arrayable_val', $defaultCollection,
                new Collection(['from_arrayable' => 'value']),
            ],
            'enumerable object with array default' => [
                $data, 'enumerable_val', $defaultArray,
                new Collection(['from_enumerable' => 'value']),
            ],
            'jsonable object with collection default' => [
                $data, 'jsonable_val', $defaultCollection,
                new Collection(['from_jsonable' => 'value']),
            ],
            'JSON string with array default' => [
                $data, 'json_string', $defaultArray,
                new Collection(['from_json' => 'value']),
            ],
            'string value with no default' => [
                $data, 'string_val', new Collection(),
                new Collection(['simple string']),
            ],
            'integer with collection default' => [
                $data, 'int_val', $defaultCollection,
                new Collection([123]),
            ],
            'null value with collection default' => [
                $data, 'null_val', $defaultCollection,
                $defaultCollection,
            ],
            'null value with array default' => [
                $data, 'null_val', $defaultArray,
                new Collection($defaultArray),
            ],
            'missing key with collection default' => [
                $data, 'missing_key', $defaultCollection,
                $defaultCollection,
            ],
            'missing key with array default' => [
                $data, 'missing_key', $defaultArray,
                new Collection($defaultArray),
            ],
            'nested key with no default' => [
                $data, 'nested.array_key', new Collection(),
                new Collection($arrayValue),
            ],
            'empty array with collection default' => [
                [], 'any_key', $defaultCollection,
                $defaultCollection,
            ],
        ];
    }

    public static function collectionOrNullProvider(): array
    {
        $arrayValue = ['key' => 'value', 'nested' => ['foo' => 'bar']];
        $arrayableObj = new class() implements Arrayable
        {
            public function toArray(): array
            {
                return ['from_arrayable' => 'value'];
            }
        };

        $enumerableObj = new Collection(['from_enumerable' => 'value']);

        $data = [
            'array_val' => $arrayValue,
            'arrayable_val' => $arrayableObj,
            'enumerable_val' => $enumerableObj,
            'json_string' => '{"from_json":"value"}',
            'string_val' => 'simple string',
            'int_val' => 123,
            'null_val' => null,
            'nested' => ['array_key' => $arrayValue],
        ];

        return [
            'array value' => [
                $data, 'array_val',
                new Collection($arrayValue),
            ],
            'arrayable object' => [
                $data, 'arrayable_val',
                new Collection(['from_arrayable' => 'value']),
            ],
            'enumerable object' => [
                $data, 'enumerable_val',
                new Collection(['from_enumerable' => 'value']),
            ],
            'JSON string' => [
                $data, 'json_string',
                new Collection(['from_json' => 'value']),
            ],
            'string value' => [
                $data, 'string_val',
                new Collection(['simple string']),
            ],
            'integer' => [
                $data, 'int_val',
                new Collection([123]),
            ],
            'null value' => [
                $data, 'null_val',
                null,
            ],
            'missing key' => [
                $data, 'missing_key',
                null,
            ],
            'nested key' => [
                $data, 'nested.array_key',
                new Collection($arrayValue),
            ],
            'empty array' => [
                [], 'any_key',
                null,
            ],
        ];
    }

    #[DataProvider('tryKeysProvider')]
    #[Test]
    public function it_can_try_multiple_keys_and_return_first_found(array $array, array $keys, mixed $expected): void
    {
        $closure = $this->arrMixin->tryKeys();
        $result = $closure($array, ...$keys);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('issetProvider')]
    #[Test]
    public function it_checks_if_key_is_set(array $array, string $key, bool $expected): void
    {
        $closure = $this->arrMixin->isset();
        $result = $closure($array, $key);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('isEmptyProvider')]
    #[Test]
    public function it_checks_if_key_is_empty(array $array, string $key, bool $expected): void
    {
        $closure = $this->arrMixin->isEmpty();
        $result = $closure($array, $key);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('stringProvider')]
    #[Test]
    public function it_can_get_a_value_as_string_with_default(array $array, string $key, string $default, string $expected): void
    {
        $closure = $this->arrMixin->string();
        $result = $closure($array, $key, $default);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('stringOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_string_or_null(array $array, string $key, ?string $expected): void
    {
        $closure = $this->arrMixin->stringOrNull();
        $result = $closure($array, $key);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('integerProvider')]
    #[Test]
    public function it_can_get_a_value_as_integer_with_default(array $array, string $key, int $default, int $expected): void
    {
        $closure = $this->arrMixin->integer();
        $result = $closure($array, $key, $default);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('integerOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_integer_or_null(array $array, string $key, ?int $expected): void
    {
        $closure = $this->arrMixin->integerOrNull();
        $result = $closure($array, $key);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('floatProvider')]
    #[Test]
    public function it_can_get_a_value_as_float_with_default(array $array, string $key, float $default, float $expected): void
    {
        $closure = $this->arrMixin->float();
        $result = $closure($array, $key, $default);
        $this->assertEqualsWithDelta($expected, $result, 0.00001); // Use delta for float comparison
    }

    #[DataProvider('floatOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_float_or_null(array $array, string $key, ?float $expected): void
    {
        $closure = $this->arrMixin->floatOrNull();
        $result = $closure($array, $key);

        if ($expected === null) {
            $this->assertNull($result);
        } else {
            $this->assertEqualsWithDelta($expected, $result, 0.00001);
        }
    }

    #[DataProvider('booleanProvider')]
    #[Test]
    public function it_can_get_a_value_as_boolean_with_default(array $array, string $key, bool $default, bool $expected): void
    {
        $closure = $this->arrMixin->boolean();
        $result = $closure($array, $key, $default);
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function it_can_get_a_value_as_stringable_with_default(): void
    {
        $closure = $this->arrMixin->stringable();

        $array = [
            'key1' => 'Hello',
            'key2' => 123,
            'key3' => null,
            'key4' => false,
        ];

        $this->assertSame('Hello', (string) $closure($array, 'key1', new Stringable('Default')));
        $this->assertInstanceOf(Stringable::class, $closure($array, 'key1', new Stringable('Default')));
        $this->assertSame('123', (string) $closure($array, 'key2', new Stringable('Default')));
        $this->assertSame('Default', (string) $closure($array, 'key3', new Stringable('Default')));
        $this->assertSame('', (string) $closure($array, 'key4', new Stringable('Default')));
        $this->assertSame('Default', (string) $closure($array, 'key5', new Stringable('Default')));
        $this->assertSame('Default', (string) $closure($array, 'key5', 'Default'));
        $this->assertInstanceOf(Stringable::class, $closure($array, 'key5', 'Default'));
    }

    #[Test]
    public function it_can_get_a_value_as_stringable_or_null(): void
    {
        $closure = $this->arrMixin->stringableOrNull();

        $array = [
            'key1' => 'World',      // Existing string
            'key2' => 456,          // Numeric (integer)
            'key3' => null,         // Null value
            'key4' => false,        // Boolean false
            'key5' => true,         // Boolean true
            'key6' => 12.34,        // Float
            'key7' => ['key' => 'value'], // Array
        ];

        $this->assertSame('World', (string) $closure($array, 'key1')); // String remains unchanged
        $this->assertSame('456', (string) $closure($array, 'key2'));  // Integer converted to string
        $this->assertNull($closure($array, 'key3'));                 // Null remains null
        $this->assertSame('', (string) $closure($array, 'key4'));    // Boolean false converted to empty string
        $this->assertSame('1', (string) $closure($array, 'key5'));   // Boolean true converted to '1'
        $this->assertSame('12.34', (string) $closure($array, 'key6')); // Float converted to string
        $this->assertSame('{"key":"value"}', (string) $closure($array, 'key7'));                 // Non-scalar (array) returns null
        $this->assertNull($closure($array, 'key8'));                 // Missing key returns null
    }

    #[DataProvider('booleanOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_boolean_or_null(array $array, string $key, ?bool $expected): void
    {
        $closure = $this->arrMixin->booleanOrNull();
        $result = $closure($array, $key);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('dateProvider')]
    #[Test]
    public function it_can_get_a_value_as_date_with_default(array $array, string $key, mixed $default, string $expectedDateString): void
    {
        $closure = $this->arrMixin->date();
        $result = $closure($array, $key, $default);

        $this->assertInstanceOf(CarbonImmutable::class, $result);
        // Compare date part only, as time should be zeroed
        $this->assertSame($expectedDateString, $result->toDateString());
    }

    #[DataProvider('dateOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_date_or_null(array $array, string $key, ?string $expectedDateString): void
    {
        $closure = $this->arrMixin->dateOrNull();
        $result = $closure($array, $key);

        if ($expectedDateString === null) {
            $this->assertNull($result);
        } else {
            $this->assertInstanceOf(CarbonImmutable::class, $result);
            $this->assertSame($expectedDateString, $result->toDateString());
        }
    }

    #[DataProvider('dateTimeProvider')]
    #[Test]
    public function it_can_get_a_value_as_datetime_with_default(array $array, string $key, mixed $default, CarbonInterface $expectedDateTime): void
    {
        $closure = $this->arrMixin->dateTime();
        $result = $closure($array, $key, $default);

        $this->assertInstanceOf(CarbonInterface::class, $result);
        // Compare using assertEquals for Carbon instances (checks value)
        $this->assertEquals($expectedDateTime, $result);
    }

    #[DataProvider('dateTimeOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_datetime_or_null(array $array, string $key, ?CarbonInterface $expectedDateTime): void
    {
        $closure = $this->arrMixin->dateTimeOrNull();
        $result = $closure($array, $key);

        if (! $expectedDateTime instanceof CarbonInterface) {
            $this->assertNull($result);
        } else {
            $this->assertInstanceOf(CarbonInterface::class, $result);
            $this->assertEquals($expectedDateTime, $result);
        }
    }

    #[DataProvider('arrayProvider')]
    #[Test]
    public function it_can_get_a_value_as_array_with_default(array $array, string $key, array $default, array $expected): void
    {
        $closure = $this->arrMixin->array();
        $result = $closure($array, $key, $default);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('arrayOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_array_or_null(array $array, string $key, ?array $expected): void
    {
        $closure = $this->arrMixin->arrayOrNull();
        $result = $closure($array, $key);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('collectionProvider')]
    #[Test]
    public function it_can_get_a_value_as_collection_with_default(
        array $array,
        string $key,
        Collection|array $default,
        Collection $expected
    ): void {
        $closure = $this->arrMixin->collection();
        $result = $closure($array, $key, $default);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals($expected, $result);
    }

    #[DataProvider('collectionOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_collection_or_null(
        array $array,
        string $key,
        ?Collection $expected
    ): void {
        $closure = $this->arrMixin->collectionOrNull();
        $result = $closure($array, $key);

        if (! $expected instanceof Collection) {
            $this->assertNull($result);
        } else {
            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals($expected, $result);
        }
    }

    #[Test]
    public function it_can_use_to_string_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toString();
        $this->assertSame('test', $method(['key' => 'test'], 'key'));
    }

    #[Test]
    public function it_can_use_to_string_or_null_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toStringOrNull();
        $this->assertSame('test', $method(['key' => 'test'], 'key'));
        $this->assertNull($method(['key' => null], 'key'));
    }

    #[Test]
    public function it_can_use_to_stringable_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toStringable();
        $result = $method(['key' => 'test'], 'key');
        $this->assertInstanceOf(Stringable::class, $result);
        $this->assertSame('test', (string) $result);
    }

    #[Test]
    public function it_can_use_to_stringable_or_null_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toStringableOrNull();
        $result = $method(['key' => 'test'], 'key');
        $this->assertInstanceOf(Stringable::class, $result);
        $this->assertSame('test', (string) $result);
        $this->assertNull($method(['key' => null], 'key'));
    }

    #[Test]
    public function it_can_use_to_integer_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toInteger();
        $this->assertSame(123, $method(['key' => '123'], 'key'));
    }

    #[Test]
    public function it_can_use_to_integer_or_null_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toIntegerOrNull();
        $this->assertSame(123, $method(['key' => '123'], 'key'));
        $this->assertNull($method(['key' => null], 'key'));
    }

    #[Test]
    public function it_can_use_to_float_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toFloat();
        $this->assertSame(123.45, $method(['key' => '123.45'], 'key'));
    }

    #[Test]
    public function it_can_use_to_float_or_null_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toFloatOrNull();
        $this->assertSame(123.45, $method(['key' => '123.45'], 'key'));
        $this->assertNull($method(['key' => null], 'key'));
    }

    #[Test]
    public function it_can_use_to_boolean_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toBoolean();
        $this->assertTrue($method(['key' => 'true'], 'key'));
        $this->assertFalse($method(['key' => 'false'], 'key'));
    }

    #[Test]
    public function it_can_use_to_boolean_or_null_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toBooleanOrNull();
        $this->assertTrue($method(['key' => 'true'], 'key'));
        $this->assertFalse($method(['key' => 'false'], 'key'));
        $this->assertNull($method(['key' => null], 'key'));
    }

    #[Test]
    public function it_can_use_to_date_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toDate();
        $result = $method(['key' => '2024-03-14'], 'key');
        $this->assertSame('2024-03-14', $result->format('Y-m-d'));
    }

    #[Test]
    public function it_can_use_to_date_or_null_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toDateOrNull();
        $result = $method(['key' => '2024-03-14'], 'key');
        $this->assertSame('2024-03-14', $result->format('Y-m-d'));
        $this->assertNull($method(['key' => null], 'key'));
    }

    #[Test]
    public function it_can_use_to_date_time_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toDateTime();
        $result = $method(['key' => '2024-03-14 15:30:00'], 'key');
        $this->assertSame('2024-03-14 15:30:00', $result->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_can_use_to_date_time_or_null_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toDateTimeOrNull();
        $result = $method(['key' => '2024-03-14 15:30:00'], 'key');
        $this->assertSame('2024-03-14 15:30:00', $result->format('Y-m-d H:i:s'));
        $this->assertNull($method(['key' => null], 'key'));
    }

    #[Test]
    public function it_can_use_to_array_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toArray();
        $result = $method(['key' => ['nested' => 'value']], 'key');
        $this->assertSame(['nested' => 'value'], $result);
    }

    #[Test]
    public function it_can_use_to_array_or_null_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toArrayOrNull();
        $result1 = $method(['key' => ['nested' => 'value']], 'key');
        $this->assertSame(['nested' => 'value'], $result1);
        $result2 = $method(['key' => null], 'key');
        $this->assertNull($result2);
    }

    #[Test]
    public function it_can_use_to_collection_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toCollection();
        $result = $method(['key' => ['nested' => 'value']], 'key');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(new Collection(['nested' => 'value']), $result);
    }

    #[Test]
    public function it_can_use_to_collection_or_null_alias(): void
    {
        $mixin = new ArrMixin();
        $method = $mixin->toCollectionOrNull();

        $result1 = $method(['key' => ['nested' => 'value']], 'key');
        $this->assertInstanceOf(Collection::class, $result1);
        $this->assertEquals(new Collection(['nested' => 'value']), $result1);

        $result2 = $method(['key' => null], 'key');
        $this->assertNull($result2);
    }
}
