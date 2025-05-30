<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\ValueObjects;

use EinarHansen\Toolkit\Tests\Utilities\ValueObjects\TestStringRegexValue;
use EinarHansen\Toolkit\ValueObjects\StringRegexValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class StringRegexValueTest extends TestCase
{
    public function test_can_create_valid_string_regex_value(): void
    {
        $value = 'test@example.com';
        $stringRegexValue = new TestStringRegexValue($value);

        $this->assertInstanceOf(StringRegexValue::class, $stringRegexValue);
        $this->assertEquals($value, $stringRegexValue->value());
        $this->assertEquals($value, (string) $stringRegexValue);
    }

    public function test_throws_exception_when_string_does_not_match_pattern(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is not a valid email address format');

        new TestStringRegexValue('invalid-email');
    }

    public function test_can_handle_complex_valid_patterns(): void
    {
        $validEmails = [
            'simple@example.com',
            'very.common@example.com',
            'disposable.style.email.with+symbol@example.com',
            'other.email-with-hyphen@example.com',
            'fully-qualified-domain@example.com',
            'user.name+tag+sorting@example.com',
            'x@example.com',
            'example-indeed@strange-example.com',
            'example@s.example',
        ];

        foreach ($validEmails as $email) {
            $stringRegexValue = new TestStringRegexValue($email);
            $this->assertEquals($email, $stringRegexValue->value());
        }
    }

    public function test_rejects_invalid_patterns(): void
    {
        $invalidEmails = [
            'Abc.example.com', // no @ character
            'A@b@c@example.com', // multiple @ characters
            'a"b(c)d,e:f;g<h>i[j\k]l@example.com', // special characters
            'just"not"right@example.com', // quoted strings
            'this is"not\allowed@example.com', // spaces
            'this\ still\"not\\allowed@example.com', // escaped characters
            'i_like_underscore.but_its_not_allowed_in_this_part@example.com', // underscore in domain
        ];

        foreach ($invalidEmails as $email) {
            $this->expectException(InvalidArgumentException::class);
            new TestStringRegexValue($email);
        }
    }

    public function test_from_method_creates_instance_with_valid_value(): void
    {
        $value = 'test@example.com';
        $instance = TestStringRegexValue::from($value);

        $this->assertInstanceOf(TestStringRegexValue::class, $instance);
        $this->assertEquals($value, $instance->value());
    }

    public function test_from_method_throws_exception_with_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TestStringRegexValue::from('invalid-email');
    }

    public function test_try_from_method_returns_instance_with_valid_value(): void
    {
        $value = 'test@example.com';
        $instance = TestStringRegexValue::tryFrom($value);

        $this->assertInstanceOf(TestStringRegexValue::class, $instance);
        $this->assertEquals($value, $instance->value());
    }

    public function test_try_from_method_returns_null_with_invalid_value(): void
    {
        $instance = TestStringRegexValue::tryFrom('invalid-email');
        $this->assertNull($instance);
    }

    public function test_try_from_array_method_returns_instance_with_valid_email(): void
    {
        $array = ['email' => 'test@example.com'];
        $instance = TestStringRegexValue::tryFromArray($array, 'email');

        $this->assertInstanceOf(TestStringRegexValue::class, $instance);
        $this->assertEquals('test@example.com', $instance->value());
    }

    public function test_try_from_array_method_returns_null_with_invalid_email(): void
    {
        $array = ['email' => 'invalid-email'];
        $instance = TestStringRegexValue::tryFromArray($array, 'email');
        $this->assertNull($instance);
    }

    public function test_try_from_array_method_returns_null_with_missing_key_for_regex(): void
    {
        $array = ['other' => 'test@example.com'];
        $instance = TestStringRegexValue::tryFromArray($array, 'email');
        $this->assertNull($instance);
    }

    public function test_from_array_method_creates_instance_with_valid_email(): void
    {
        $array = ['email' => 'test@example.com'];
        $instance = TestStringRegexValue::fromArray($array, 'email');

        $this->assertInstanceOf(TestStringRegexValue::class, $instance);
        $this->assertEquals('test@example.com', $instance->value());
    }

    public function test_from_array_method_uses_default_with_missing_key_for_regex(): void
    {
        $array = ['other' => 'test@example.com'];
        $instance = TestStringRegexValue::fromArray($array, 'email', 'default@example.com');

        $this->assertInstanceOf(TestStringRegexValue::class, $instance);
        $this->assertEquals('default@example.com', $instance->value());
    }

    public function test_from_array_method_throws_exception_with_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $array = ['email' => 'invalid-email'];
        TestStringRegexValue::fromArray($array, 'email');
    }

    public function test_from_array_method_handles_non_string_values(): void
    {
        // Testing with a numeric value that can be cast to valid email
        $array = ['email' => 123];
        $this->expectException(InvalidArgumentException::class);
        TestStringRegexValue::fromArray($array, 'email');
    }

    public function test_from_array_method_uses_empty_default_when_not_specified(): void
    {
        $array = ['other' => 'test@example.com'];

        $this->expectException(InvalidArgumentException::class);
        TestStringRegexValue::fromArray($array, 'email');
    }
}
