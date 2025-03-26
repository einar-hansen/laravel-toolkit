<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\ValueObjects;

use EinarHansen\Toolkit\Tests\Utilities\ValueObjects\TestStringLengthValue;
use EinarHansen\Toolkit\ValueObjects\StringLengthValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StringLengthValueTest extends TestCase
{
    public function test_can_create_valid_string_length_value(): void
    {
        $value = 'Valid string';
        $testStringLengthValue = new TestStringLengthValue($value);

        $this->assertInstanceOf(StringLengthValue::class, $testStringLengthValue);
        $this->assertEquals($value, $testStringLengthValue->value());
        $this->assertEquals($value, (string) $testStringLengthValue);
    }

    public function test_throws_exception_when_string_exceeds_max_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('String length exceeds maximum of 50 characters');

        new TestStringLengthValue(str_repeat('a', 51));
    }

    public function test_throws_exception_when_string_below_min_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('String length is less than minimum of 5 characters');

        new TestStringLengthValue('abcd');
    }

    public function test_can_create_string_with_maximum_length(): void
    {
        $maxLengthString = str_repeat('a', 50);
        $testStringLengthValue = new TestStringLengthValue($maxLengthString);

        $this->assertEquals(50, mb_strlen($testStringLengthValue->value()));
        $this->assertEquals($maxLengthString, (string) $testStringLengthValue);
    }

    public function test_can_create_string_with_minimum_length(): void
    {
        $minLengthString = str_repeat('a', 5);
        $testStringLengthValue = new TestStringLengthValue($minLengthString);

        $this->assertEquals(5, mb_strlen($testStringLengthValue->value()));
        $this->assertEquals($minLengthString, (string) $testStringLengthValue);
    }

    public function test_from_method_creates_instance_with_valid_value(): void
    {
        $value = 'Valid string';
        $instance = TestStringLengthValue::from($value);

        $this->assertInstanceOf(TestStringLengthValue::class, $instance);
        $this->assertEquals($value, $instance->value());
    }

    public function test_from_method_throws_exception_with_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TestStringLengthValue::from('abcd'); // Below min length
    }

    public function test_try_from_method_returns_instance_with_valid_value(): void
    {
        $value = 'Valid string';
        $instance = TestStringLengthValue::tryFrom($value);

        $this->assertInstanceOf(TestStringLengthValue::class, $instance);
        $this->assertEquals($value, $instance->value());
    }

    public function test_try_from_method_returns_null_with_invalid_value(): void
    {
        $instance = TestStringLengthValue::tryFrom('abcd'); // Below min length
        $this->assertNull($instance);

        $instance = TestStringLengthValue::tryFrom(str_repeat('a', 51)); // Above max length
        $this->assertNull($instance);
    }

    public function test_try_from_array_method_returns_instance_with_valid_string_length_value(): void
    {
        $array = ['name' => 'Valid string'];
        $instance = TestStringLengthValue::tryFromArray($array, 'name');

        $this->assertInstanceOf(TestStringLengthValue::class, $instance);
        $this->assertEquals('Valid string', $instance->value());
    }

    public function test_try_from_array_method_returns_null_with_invalid_string_length_value(): void
    {
        $array = ['name' => 'abcd']; // Below min length
        $instance = TestStringLengthValue::tryFromArray($array, 'name');
        $this->assertNull($instance);

        $array = ['name' => str_repeat('a', 51)]; // Above max length
        $instance = TestStringLengthValue::tryFromArray($array, 'name');
        $this->assertNull($instance);
    }

    public function test_try_from_array_method_returns_null_with_missing_key_for_string_length(): void
    {
        $array = ['other' => 'Valid string'];
        $instance = TestStringLengthValue::tryFromArray($array, 'name');
        $this->assertNull($instance);
    }

    public function test_from_array_method_creates_instance_with_valid_string_length_value(): void
    {
        $array = ['name' => 'Valid string'];
        $instance = TestStringLengthValue::fromArray($array, 'name');

        $this->assertInstanceOf(TestStringLengthValue::class, $instance);
        $this->assertEquals('Valid string', $instance->value());
    }

    public function test_from_array_method_uses_default_with_missing_key_for_string_length(): void
    {
        $array = ['other' => 'Valid string'];
        $instance = TestStringLengthValue::fromArray($array, 'name', 'Default value');

        $this->assertInstanceOf(TestStringLengthValue::class, $instance);
        $this->assertEquals('Default value', $instance->value());
    }

    public function test_from_array_method_throws_exception_with_invalid_string_length_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $array = ['name' => 'abcd']; // Below min length
        TestStringLengthValue::fromArray($array, 'name');
    }
}
