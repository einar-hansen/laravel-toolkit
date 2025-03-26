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
}
