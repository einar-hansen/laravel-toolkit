<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\ValueObjects;

use EinarHansen\Toolkit\Tests\Utilities\ValueObjects\TestFloatRangeValue;
use EinarHansen\Toolkit\ValueObjects\FloatRangeValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FloatRangeValueTest extends TestCase
{
    public function test_can_create_valid_float_range_value(): void
    {
        $value = 50.25;
        $floatValue = new TestFloatRangeValue($value);

        $this->assertInstanceOf(FloatRangeValue::class, $floatValue);
        $this->assertEquals($value, $floatValue->value());
        $this->assertEquals('50.25', (string) $floatValue);
    }

    public function test_throws_exception_when_float_exceeds_max_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Float value exceeds maximum of');

        new TestFloatRangeValue(100.01);
    }

    public function test_throws_exception_when_float_below_min_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Float value is less than minimum of');

        new TestFloatRangeValue(-0.01);
    }

    public function test_can_create_float_with_maximum_value(): void
    {
        $maxValue = 100.0;
        $floatValue = new TestFloatRangeValue($maxValue);

        $this->assertEquals($maxValue, $floatValue->value());
        $this->assertEquals('100.00', (string) $floatValue);
    }

    public function test_can_create_float_with_minimum_value(): void
    {
        $minValue = 0.0;
        $floatValue = new TestFloatRangeValue($minValue);

        $this->assertEquals($minValue, $floatValue->value());
        $this->assertEquals('0.00', (string) $floatValue);
    }

    public function test_string_representation_has_correct_precision(): void
    {
        $tests = [
            [42.0, '42.00'],
            [42.1, '42.10'],
            [42.12, '42.12'],
            [42.123, '42.12'],  // Should round to 2 decimal places
            [42.125, '42.13'],  // Should round to 2 decimal places
        ];

        foreach ($tests as [$input, $expected]) {
            $floatValue = new TestFloatRangeValue($input);
            $this->assertEquals($expected, (string) $floatValue);
        }
    }

    public function test_from_method_creates_instance_with_valid_value(): void
    {
        $value = 50.25;
        $instance = TestFloatRangeValue::from($value);

        $this->assertInstanceOf(TestFloatRangeValue::class, $instance);
        $this->assertEquals($value, $instance->value());
    }

    public function test_from_method_throws_exception_with_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TestFloatRangeValue::from(-0.01); // Below min value
    }

    public function test_try_from_method_returns_instance_with_valid_value(): void
    {
        $value = 50.25;
        $instance = TestFloatRangeValue::tryFrom($value);

        $this->assertInstanceOf(TestFloatRangeValue::class, $instance);
        $this->assertEquals($value, $instance->value());
    }

    public function test_try_from_method_returns_null_with_invalid_value(): void
    {
        $instance = TestFloatRangeValue::tryFrom(-0.01); // Below min value
        $this->assertNull($instance);

        $instance = TestFloatRangeValue::tryFrom(100.01); // Above max value
        $this->assertNull($instance);
    }

    // New tests for parsing integers
    public function test_can_create_from_integer_value(): void
    {
        $intValue = 42;
        $floatValue = new TestFloatRangeValue($intValue);

        $this->assertEquals(42.0, $floatValue->value());
        $this->assertEquals('42.00', (string) $floatValue);
    }

    public function test_from_method_handles_integer_value(): void
    {
        $intValue = 75;
        $instance = TestFloatRangeValue::from($intValue);

        $this->assertInstanceOf(TestFloatRangeValue::class, $instance);
        $this->assertEquals(75.0, $instance->value());
    }

    public function test_try_from_method_handles_integer_value(): void
    {
        $intValue = 50;
        $instance = TestFloatRangeValue::tryFrom($intValue);

        $this->assertInstanceOf(TestFloatRangeValue::class, $instance);
        $this->assertEquals(50.0, $instance->value());
    }

    // New tests for parsing strings
    public function test_from_method_handles_valid_string_values(): void
    {
        $testCases = [
            ['42', 42.0],
            ['42.5', 42.5],
            [' 42.5 ', 42.5],  // Test trimming
            ['42,5', 42.5],    // Test comma as decimal separator
            ['0', 0.0],
            ['100', 100.0],
        ];

        foreach ($testCases as [$input, $expected]) {
            $instance = TestFloatRangeValue::from($input);
            $this->assertEquals($expected, $instance->value(), 'Failed parsing string: '.$input);
        }
    }

    public function test_from_method_throws_exception_with_invalid_string_value(): void
    {
        $testCases = [
            'abc',        // Non-numeric string
            '42a',        // Partially numeric string
            '',           // Empty string
            '42,5,6',     // Multiple decimal separators
        ];

        foreach ($testCases as $input) {
            try {
                TestFloatRangeValue::from($input);
                $this->fail('Expected exception for input: '.$input);
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('Cannot parse', $e->getMessage());
            }
        }
    }

    public function test_try_from_method_handles_valid_string_values(): void
    {
        $testCases = [
            ['42', 42.0],
            ['42.5', 42.5],
            [' 42.5 ', 42.5],  // Test trimming
            ['42,5', 42.5],    // Test comma as decimal separator
        ];

        foreach ($testCases as [$input, $expected]) {
            $instance = TestFloatRangeValue::tryFrom($input);
            $this->assertNotNull($instance, 'Failed parsing string: '.$input);
            $this->assertEquals($expected, $instance->value());
        }
    }

    public function test_try_from_method_returns_null_with_invalid_string_value(): void
    {
        $testCases = [
            'abc',        // Non-numeric string
            '42a',        // Partially numeric string
            '',           // Empty string
            '42,5,6',     // Multiple decimal separators
            '-1',         // Below minimum
            '101',        // Above maximum
        ];

        foreach ($testCases as $input) {
            $instance = TestFloatRangeValue::tryFrom($input);
            $this->assertNull($instance, 'Expected null for input: '.$input);
        }
    }

    public function test_try_from_array_method_returns_instance_with_valid_float_value(): void
    {
        $array = ['amount' => 50.25];
        $instance = TestFloatRangeValue::tryFromArray($array, 'amount');

        $this->assertInstanceOf(TestFloatRangeValue::class, $instance);
        $this->assertEquals(50.25, $instance->value());
    }

    public function test_try_from_array_method_returns_instance_with_valid_string_value(): void
    {
        $array = ['amount' => '42.5'];
        $instance = TestFloatRangeValue::tryFromArray($array, 'amount');

        $this->assertInstanceOf(TestFloatRangeValue::class, $instance);
        $this->assertEquals(42.5, $instance->value());
    }

    public function test_try_from_array_method_returns_null_with_invalid_float_value(): void
    {
        $array = ['amount' => -0.01]; // Below min value
        $instance = TestFloatRangeValue::tryFromArray($array, 'amount');
        $this->assertNull($instance);

        $array = ['amount' => 100.01]; // Above max value
        $instance = TestFloatRangeValue::tryFromArray($array, 'amount');
        $this->assertNull($instance);
    }

    public function test_try_from_array_method_returns_null_with_missing_key_for_float(): void
    {
        $array = ['other' => 50.25];
        $instance = TestFloatRangeValue::tryFromArray($array, 'amount');
        $this->assertNull($instance);
    }

    public function test_from_array_method_creates_instance_with_valid_float_value(): void
    {
        $array = ['amount' => 50.25];
        $instance = TestFloatRangeValue::fromArray($array, 'amount');

        $this->assertInstanceOf(TestFloatRangeValue::class, $instance);
        $this->assertEquals(50.25, $instance->value());
    }

    public function test_from_array_method_uses_default_with_missing_key_for_float(): void
    {
        $array = ['other' => 50.25];
        $instance = TestFloatRangeValue::fromArray($array, 'amount', 25.5);

        $this->assertInstanceOf(TestFloatRangeValue::class, $instance);
        $this->assertEquals(25.5, $instance->value());
    }

    public function test_from_array_method_throws_exception_with_invalid_float_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $array = ['amount' => -0.01]; // Below min value
        TestFloatRangeValue::fromArray($array, 'amount');
    }
}
