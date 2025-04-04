<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Mixins;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTime;
use EinarHansen\Toolkit\Mixins\ArrMixin;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(\EinarHansen\Toolkit\Mixins\ArrMixin::class)]
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

    #[DataProvider('tryKeysProvider')]
    #[Test]
    public function it_can_try_multiple_keys_and_return_first_found(array $array, array $keys, mixed $expected): void
    {
        $closure = $this->arrMixin->tryKeys();
        $result = $closure($array, ...$keys);
        $this->assertSame($expected, $result);
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

    #[DataProvider('stringProvider')]
    #[Test]
    public function it_can_get_a_value_as_string_with_default(array $array, string $key, string $default, string $expected): void
    {
        $closure = $this->arrMixin->string();
        $result = $closure($array, $key, $default);
        $this->assertSame($expected, $result);
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

    #[DataProvider('stringOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_string_or_null(array $array, string $key, ?string $expected): void
    {
        $closure = $this->arrMixin->stringOrNull();
        $result = $closure($array, $key);
        $this->assertSame($expected, $result);
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

    #[DataProvider('integerProvider')]
    #[Test]
    public function it_can_get_a_value_as_integer_with_default(array $array, string $key, int $default, int $expected): void
    {
        $closure = $this->arrMixin->integer();
        $result = $closure($array, $key, $default);
        $this->assertSame($expected, $result);
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

    #[DataProvider('integerOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_integer_or_null(array $array, string $key, ?int $expected): void
    {
        $closure = $this->arrMixin->integerOrNull();
        $result = $closure($array, $key);
        $this->assertSame($expected, $result);
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

    #[DataProvider('floatProvider')]
    #[Test]
    public function it_can_get_a_value_as_float_with_default(array $array, string $key, float $default, float $expected): void
    {
        $closure = $this->arrMixin->float();
        $result = $closure($array, $key, $default);
        $this->assertEqualsWithDelta($expected, $result, 0.00001); // Use delta for float comparison
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

    #[DataProvider('booleanProvider')]
    #[Test]
    public function it_can_get_a_value_as_boolean_with_default(array $array, string $key, bool $default, bool $expected): void
    {
        $closure = $this->arrMixin->boolean();
        $result = $closure($array, $key, $default);
        $this->assertSame($expected, $result);
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

    #[DataProvider('booleanOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_boolean_or_null(array $array, string $key, ?bool $expected): void
    {
        $closure = $this->arrMixin->booleanOrNull();
        $result = $closure($array, $key);
        $this->assertSame($expected, $result);
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

    public static function dateProvider(): array
    {
        $nowDateString = CarbonImmutable::create(2024, 5, 15, 12, 30, 45, 'UTC')->toDateString();
        $defaultDateString = '2023-01-01';
        $defaultDateTime = new DateTime('2023-01-01 15:00:00'); // Time should be ignored
        $validDateString = '2024-02-20';
        $validDateTimeString = '2024-03-10 10:20:30'; // Time should be ignored
        $validDateTimeObj = new DateTime($validDateTimeString);
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

    public static function dateOrNullProvider(): array
    {
        $validDateString = '2024-02-20';
        $validDateTimeString = '2024-03-10 10:20:30'; // Time should be ignored
        $validDateTimeObj = new DateTime($validDateTimeString);
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

    public static function dateTimeProvider(): array
    {
        $now = CarbonImmutable::create(2024, 5, 15, 12, 30, 45, 'UTC'); // Use the mocked 'now'
        $defaultDateTimeString = '2023-01-01 15:30:00';
        $defaultDateTime = CarbonImmutable::parse($defaultDateTimeString);
        $validDateTimeString = '2024-03-10 10:20:30';
        $validDateTime = CarbonImmutable::parse($validDateTimeString);
        $validDateTimeObj = new DateTime($validDateTimeString); // Native DateTime
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

    #[DataProvider('dateTimeOrNullProvider')]
    #[Test]
    public function it_can_get_a_value_as_datetime_or_null(array $array, string $key, ?CarbonInterface $expectedDateTime): void
    {
        $closure = $this->arrMixin->dateTimeOrNull();
        $result = $closure($array, $key);

        if ($expectedDateTime === null) {
            $this->assertNull($result);
        } else {
            $this->assertInstanceOf(CarbonInterface::class, $result);
            $this->assertEquals($expectedDateTime, $result);
        }
    }

    public static function dateTimeOrNullProvider(): array
    {
        $validDateTimeString = '2024-03-10 10:20:30';
        $validDateTime = CarbonImmutable::parse($validDateTimeString);
        $validDateTimeObj = new DateTime($validDateTimeString);
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
}
