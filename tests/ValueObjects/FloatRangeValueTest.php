<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\ValueObjects;

use EinarHansen\Toolkit\Tests\Utilities\ValueObjects\TestFloatRangeValue;
use EinarHansen\Toolkit\ValueObjects\FloatRangeValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FloatRangeValueTest extends TestCase
{
    public function testCanCreateValidFloatRangeValue(): void
    {
        $value = 50.25;
        $floatValue = new TestFloatRangeValue($value);

        $this->assertInstanceOf(FloatRangeValue::class, $floatValue);
        $this->assertEquals($value, $floatValue->value());
        $this->assertEquals('50.25', (string) $floatValue);
    }

    public function testThrowsExceptionWhenFloatExceedsMaxValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Float value exceeds maximum of');

        new TestFloatRangeValue(100.01);
    }

    public function testThrowsExceptionWhenFloatBelowMinValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Float value is less than minimum of');

        new TestFloatRangeValue(-0.01);
    }

    public function testCanCreateFloatWithMaximumValue(): void
    {
        $maxValue = 100.0;
        $floatValue = new TestFloatRangeValue($maxValue);

        $this->assertEquals($maxValue, $floatValue->value());
        $this->assertEquals('100.00', (string) $floatValue);
    }

    public function testCanCreateFloatWithMinimumValue(): void
    {
        $minValue = 0.0;
        $floatValue = new TestFloatRangeValue($minValue);

        $this->assertEquals($minValue, $floatValue->value());
        $this->assertEquals('0.00', (string) $floatValue);
    }

    public function testStringRepresentationHasCorrectPrecision(): void
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