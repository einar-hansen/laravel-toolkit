<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\ValueObjects;

use EinarHansen\Toolkit\Tests\Utilities\ValueObjects\TestIntegerRangeValue;
use EinarHansen\Toolkit\ValueObjects\IntegerRangeValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class IntegerRangeValueTest extends TestCase
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

    public function test_from_method_creates_instance_with_valid_value(): void
    {
        $value = 50;
        $instance = TestIntegerRangeValue::from($value);

        $this->assertInstanceOf(TestIntegerRangeValue::class, $instance);
        $this->assertEquals($value, $instance->value());
    }

    public function test_from_method_throws_exception_with_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TestIntegerRangeValue::from(0); // Below min value
    }

    public function test_try_from_method_returns_instance_with_valid_value(): void
    {
        $value = 50;
        $instance = TestIntegerRangeValue::tryFrom($value);

        $this->assertInstanceOf(TestIntegerRangeValue::class, $instance);
        $this->assertEquals($value, $instance->value());
    }

    public function test_try_from_method_returns_null_with_invalid_value(): void
    {
        $instance = TestIntegerRangeValue::tryFrom(0); // Below min value
        $this->assertNull($instance);

        $instance = TestIntegerRangeValue::tryFrom(101); // Above max value
        $this->assertNull($instance);
    }

    public function test_try_from_array_method_returns_instance_with_valid_value(): void
    {
        $array = ['count' => 50];
        $instance = TestIntegerRangeValue::tryFromArray($array, 'count');

        $this->assertInstanceOf(TestIntegerRangeValue::class, $instance);
        $this->assertEquals(50, $instance->value());
    }

    public function test_try_from_array_method_returns_null_with_invalid_value(): void
    {
        $array = ['count' => 0]; // Below min value
        $instance = TestIntegerRangeValue::tryFromArray($array, 'count');
        $this->assertNull($instance);

        $array = ['count' => 101]; // Above max value
        $instance = TestIntegerRangeValue::tryFromArray($array, 'count');
        $this->assertNull($instance);
    }

    public function test_try_from_array_method_returns_null_with_missing_key(): void
    {
        $array = ['other' => 50];
        $instance = TestIntegerRangeValue::tryFromArray($array, 'count');
        $this->assertNull($instance);
    }

    public function test_from_array_method_creates_instance_with_valid_value(): void
    {
        $array = ['count' => 50];
        $instance = TestIntegerRangeValue::fromArray($array, 'count');

        $this->assertInstanceOf(TestIntegerRangeValue::class, $instance);
        $this->assertEquals(50, $instance->value());
    }

    public function test_from_array_method_uses_default_with_missing_key(): void
    {
        $array = ['other' => 50];
        $instance = TestIntegerRangeValue::fromArray($array, 'count', 25);

        $this->assertInstanceOf(TestIntegerRangeValue::class, $instance);
        $this->assertEquals(25, $instance->value());
    }

    public function test_from_array_method_throws_exception_with_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $array = ['count' => 0]; // Below min value
        TestIntegerRangeValue::fromArray($array, 'count');
    }
}
