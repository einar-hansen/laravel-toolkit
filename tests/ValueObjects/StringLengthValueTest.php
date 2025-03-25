<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Unit\ValueObjects;

use EinarHansen\Toolkit\Tests\Utilities\ValueObjects\TestStringLengthValue;
use EinarHansen\Toolkit\ValueObjects\StringLengthValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StringLengthValueTest extends TestCase
{
    public function test_can_create_valid_string_length_value(): void
    {
        $value = 'Valid string';
        $stringValue = new TestStringLengthValue($value);

        $this->assertInstanceOf(StringLengthValue::class, $stringValue);
        $this->assertEquals($value, $stringValue->value());
        $this->assertEquals($value, (string) $stringValue);
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
        $stringValue = new TestStringLengthValue($maxLengthString);

        $this->assertEquals(50, mb_strlen($stringValue->value()));
        $this->assertEquals($maxLengthString, (string) $stringValue);
    }

    public function test_can_create_string_with_minimum_length(): void
    {
        $minLengthString = str_repeat('a', 5);
        $stringValue = new TestStringLengthValue($minLengthString);

        $this->assertEquals(5, mb_strlen($stringValue->value()));
        $this->assertEquals($minLengthString, (string) $stringValue);
    }
}
