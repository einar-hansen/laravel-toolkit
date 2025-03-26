<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\ValueObjects;

use EinarHansen\Toolkit\Tests\Utilities\ValueObjects\TestStringRegexValue;
use EinarHansen\Toolkit\ValueObjects\StringRegexValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StringRegexValueTest extends TestCase
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
}
