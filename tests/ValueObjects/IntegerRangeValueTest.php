<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\ValueObjects;

use EinarHansen\Toolkit\Tests\Utilities\ValueObjects\TestIntegerRangeValue;
use EinarHansen\Toolkit\ValueObjects\IntegerRangeValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class IntegerRangeValueTest extends TestCase
{
    public function test_can_create_valid_integer_range_value(): void
    {
        $value = 50;
        $testIntegerRangeValue = new TestIntegerRangeValue($value);

        $this->assertInstanceOf(IntegerRangeValue::class, $testIntegerRangeValue);
        $this->assertEquals($value, $testIntegerRangeValue->value());
        $this->assertEquals((string) $value, (string) $testIntegerRangeValue);
    }

    public function test_throws_exception_when_integer_exceeds_max_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Integer value exceeds maximum of 100');

        new TestIntegerRangeValue(101);
    }

    public function test_throws_exception_when_integer_below_min_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Integer value is less than minimum of 1');

        new TestIntegerRangeValue(0);
    }

    public function test_can_create_integer_with_maximum_value(): void
    {
        $maxValue = 100;
        $testIntegerRangeValue = new TestIntegerRangeValue($maxValue);

        $this->assertEquals($maxValue, $testIntegerRangeValue->value());
        $this->assertEquals((string) $maxValue, (string) $testIntegerRangeValue);
    }

    public function test_can_create_integer_with_minimum_value(): void
    {
        $minValue = 1;
        $testIntegerRangeValue = new TestIntegerRangeValue($minValue);

        $this->assertEquals($minValue, $testIntegerRangeValue->value());
        $this->assertEquals((string) $minValue, (string) $testIntegerRangeValue);
    }
}
