<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\ValueObjects;

use EinarHansen\Toolkit\Tests\Utilities\ValueObjects\TestPhoneNumberValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PhoneNumberTest extends TestCase
{
    public function test_can_create_valid_phone_number_with_a_custom_region(): void
    {
        $value = '70 123 4567';
        $phoneNumber = new TestPhoneNumberValue($value, 'SE');

        $this->assertInstanceOf(TestPhoneNumberValue::class, $phoneNumber);
        $this->assertEquals($value, $phoneNumber->original());
        $this->assertEquals('701234567', $phoneNumber->value());
        $this->assertEquals('+46701234567', (string) $phoneNumber);
        $this->assertEquals('SE', $phoneNumber->region());
    }

    public function test_can_create_valid_phone_number_with_a_different_region(): void
    {
        $value = '+45 23 45 67 89';
        $phoneNumber = new TestPhoneNumberValue($value);

        $this->assertInstanceOf(TestPhoneNumberValue::class, $phoneNumber);
        $this->assertEquals($value, $phoneNumber->original());
        $this->assertEquals('23 45 67 89', $phoneNumber->value());
        $this->assertEquals('+4523456789', (string) $phoneNumber);
        $this->assertEquals('DK', $phoneNumber->region());

    }

    public function test_can_create_valid_phone_number_with_region_provided(): void
    {
        $value = '+358 40 234 5678';
        $phoneNumber = new TestPhoneNumberValue($value, 'FI');

        $this->assertInstanceOf(TestPhoneNumberValue::class, $phoneNumber);
        $this->assertEquals($value, $phoneNumber->original());
        $this->assertEquals('040 2345678', $phoneNumber->value());
        $this->assertEquals('+358402345678', (string) $phoneNumber);
        $this->assertEquals('FI', $phoneNumber->region());

    }

    public function test_can_create_valid_phone_number_with_the_default_region(): void
    {
        $value = '815 493 00';
        $phoneNumber = new TestPhoneNumberValue($value);

        $this->assertInstanceOf(TestPhoneNumberValue::class, $phoneNumber);
        $this->assertEquals($value, $phoneNumber->original());
        $this->assertEquals('815 49 300', $phoneNumber->value());
        $this->assertEquals('+4781549300', (string) $phoneNumber);
        $this->assertEquals('NO', $phoneNumber->region());

    }

    public function test_throws_exception_when_phone_number_is_invalid_number(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number');

        new TestPhoneNumberValue('+4712345678');
    }

    public function test_throws_exception_when_phone_number_is_invalid_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number');

        new TestPhoneNumberValue('invalid-phone');
    }

    public function test_can_handle_different_phone_number_formats(): void
    {
        $validNumbers = [
            '+4781549300',
            '004781549300',
            '81549300',
        ];

        foreach ($validNumbers as $number) {
            $phoneNumber = new TestPhoneNumberValue($number);
            $this->assertNotNull($phoneNumber->value());
        }
    }

    public function test_from_method_creates_instance_with_valid_value(): void
    {
        $value = '+4781549300';
        $instance = TestPhoneNumberValue::from($value);

        $this->assertInstanceOf(TestPhoneNumberValue::class, $instance);
        $this->assertEquals($value, $instance->original());
        $this->assertEquals('815 49 300', $instance->value());
    }

    public function test_from_method_throws_exception_with_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TestPhoneNumberValue::from('invalid-phone');
    }

    public function test_try_from_method_returns_instance_with_valid_value(): void
    {
        $value = '+4781549300';
        $instance = TestPhoneNumberValue::tryFrom($value);

        $this->assertInstanceOf(TestPhoneNumberValue::class, $instance);
        $this->assertEquals($value, $instance->original());
        $this->assertEquals('815 49 300', $instance->value());
    }

    public function test_try_from_method_returns_null_with_invalid_value(): void
    {
        $instance = TestPhoneNumberValue::tryFrom('invalid-phone');
        $this->assertNull($instance);
    }

    public function test_try_from_array_method_returns_instance_with_valid_phone(): void
    {
        $array = ['phone' => '+4781549300'];
        $instance = TestPhoneNumberValue::tryFromArray($array, 'phone');

        $this->assertInstanceOf(TestPhoneNumberValue::class, $instance);
        $this->assertEquals('+4781549300', $instance->original());
        $this->assertEquals('815 49 300', $instance->value());
    }

    public function test_try_from_array_method_returns_null_with_invalid_phone(): void
    {
        $array = ['phone' => 'invalid-phone'];
        $instance = TestPhoneNumberValue::tryFromArray($array, 'phone');
        $this->assertNull($instance);
    }

    public function test_try_from_array_method_returns_null_with_missing_key(): void
    {
        $array = ['other' => '+4781549300'];
        $instance = TestPhoneNumberValue::tryFromArray($array, 'phone');
        $this->assertNull($instance);
    }

    public function test_from_array_method_creates_instance_with_valid_phone(): void
    {
        $array = ['phone' => '+4781549300'];
        $instance = TestPhoneNumberValue::fromArray($array, 'phone');

        $this->assertInstanceOf(TestPhoneNumberValue::class, $instance);
        $this->assertEquals('+4781549300', $instance->original());
        $this->assertEquals('815 49 300', $instance->value());
    }

    public function test_from_array_method_throws_exception_with_invalid_phone(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $array = ['phone' => 'invalid-phone'];
        TestPhoneNumberValue::fromArray($array, 'phone');
    }

    public function test_can_format_phone_number_in_different_formats(): void
    {
        $phoneNumber = new TestPhoneNumberValue('+4781549300');

        $this->assertEquals('+47 815 49 300', $phoneNumber->international());
        $this->assertEquals('+4781549300', $phoneNumber->e164());
        $this->assertEquals('815 49 300', $phoneNumber->national());
    }

    public function test_can_get_phone_number_type(): void
    {
        $phoneNumber = new TestPhoneNumberValue('+4781549300');
        $this->assertNotNull($phoneNumber->type());
    }

    public function test_can_get_phone_number_region(): void
    {
        $phoneNumber = new TestPhoneNumberValue('+4781549300');
        $this->assertEquals('NO', $phoneNumber->region());
    }

    public function test_can_check_if_phone_numbers_match(): void
    {
        $phoneNumber1 = new TestPhoneNumberValue('+4781549300');
        $phoneNumber2 = new TestPhoneNumberValue('004781549300');
        $phoneNumber3 = new TestPhoneNumberValue('+1 (312) 555-0123');

        $this->assertTrue($phoneNumber1->matches($phoneNumber2));
        $this->assertTrue($phoneNumber1->matches('+4781549300'));
        $this->assertTrue($phoneNumber1->matches('004781549300'));
        $this->assertFalse($phoneNumber1->matches($phoneNumber3));
        $this->assertFalse($phoneNumber1->matches('+1 (312) 555-0123'));
        $this->assertFalse($phoneNumber1->matches(null));
    }

    public function test_can_specify_custom_region(): void
    {
        $phoneNumber = new TestPhoneNumberValue('2025550179', 'US');
        $this->assertEquals('+1 202-555-0179', $phoneNumber->international());
        $this->assertEquals('US', $phoneNumber->region());
    }
}
