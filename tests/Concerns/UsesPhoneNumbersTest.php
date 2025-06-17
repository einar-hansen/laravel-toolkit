<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Concerns;

use EinarHansen\Toolkit\Concerns\UsesPhoneNumbers;
use EinarHansen\Toolkit\Tests\Concerns\Stubs\TestUsesPhoneNumbers;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsesPhoneNumbers::class)]
final class UsesPhoneNumbersTest extends TestCase
{
    private TestUsesPhoneNumbers $phoneNumbers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->phoneNumbers = new TestUsesPhoneNumbers();
    }

    #[Test]
    public function it_can_match_phone_numbers(): void
    {
        // Test matching phone numbers
        $this->assertTrue($this->phoneNumbers->phoneNumbersMatch('+4712345678', '004712345678'));
        $this->assertTrue($this->phoneNumbers->phoneNumbersMatch('+4712345678', '12345678'));

        // Test non-matching phone numbers
        $this->assertFalse($this->phoneNumbers->phoneNumbersMatch('+4712345678', '+1 (555) 123-4567'));

        // Test edge cases
        $this->assertFalse($this->phoneNumbers->phoneNumbersMatch(null, '+4712345678'));
        $this->assertFalse($this->phoneNumbers->phoneNumbersMatch('+4712345678', null));
        $this->assertFalse($this->phoneNumbers->phoneNumbersMatch('', '+4712345678'));
        $this->assertFalse($this->phoneNumbers->phoneNumbersMatch('+4712345678', ''));
        $this->assertFalse($this->phoneNumbers->phoneNumbersMatch('0', '+4712345678'));
        $this->assertFalse($this->phoneNumbers->phoneNumbersMatch('+4712345678', '0'));
    }

    #[Test]
    public function it_can_normalize_phone_numbers(): void
    {
        // Test valid phone numbers
        $normalized1 = $this->phoneNumbers->normalizePhoneNumber('+4712345678');
        $this->assertNotNull($normalized1);
        $this->assertIsString($normalized1);

        $normalized2 = $this->phoneNumbers->normalizePhoneNumber('004712345678');
        $this->assertNotNull($normalized2);
        $this->assertIsString($normalized2);

        // Test with different regions
        $normalized3 = $this->phoneNumbers->normalizePhoneNumber('12345678', 'NO');
        $this->assertNotNull($normalized3);
        $this->assertIsString($normalized3);

        // Test invalid phone numbers (fallback to manual normalization)
        $this->assertEquals('12345', $this->phoneNumbers->normalizePhoneNumber('12345', 'NO'));

        // Test edge cases
        $this->assertNull($this->phoneNumbers->normalizePhoneNumber(null));
        $this->assertNull($this->phoneNumbers->normalizePhoneNumber(''));
        $this->assertNull($this->phoneNumbers->normalizePhoneNumber('0'));
    }

    #[Test]
    public function it_can_format_phone_numbers(): void
    {
        // Test international format (default)
        $formatted1 = $this->phoneNumbers->formatPhoneNumber('+4712345678');
        $this->assertNotNull($formatted1);
        $this->assertIsString($formatted1);
        $this->assertStringContainsString('47', $formatted1);

        // Test E164 format
        $formatted2 = $this->phoneNumbers->formatPhoneNumber('+4712345678', PhoneNumberFormat::E164);
        $this->assertNotNull($formatted2);
        $this->assertIsString($formatted2);
        $this->assertStringContainsString('+47', $formatted2);

        // Test national format
        $formatted3 = $this->phoneNumbers->formatPhoneNumber('+4712345678', PhoneNumberFormat::NATIONAL);
        $this->assertNotNull($formatted3);
        $this->assertIsString($formatted3);

        // Test with different regions
        $formatted4 = $this->phoneNumbers->formatPhoneNumber('99999999', PhoneNumberFormat::INTERNATIONAL, 'NO');
        $this->assertNotNull($formatted4);
        $this->assertIsString($formatted4);
        $this->assertStringContainsString('+47', $formatted4);

        // Test invalid phone numbers (returns original)
        $this->assertEquals('invalid', $this->phoneNumbers->formatPhoneNumber('invalid'));

        // Test edge cases
        $this->assertNull($this->phoneNumbers->formatPhoneNumber(null));
        $this->assertNull($this->phoneNumbers->formatPhoneNumber(''));
        $this->assertNull($this->phoneNumbers->formatPhoneNumber('0'));
    }

    #[Test]
    public function it_can_validate_phone_numbers(): void
    {
        // Test valid phone numbers with full international format
        $this->assertTrue($this->phoneNumbers->isValidPhoneNumber('+4799999999'));

        // Test invalid phone numbers
        $this->assertFalse($this->phoneNumbers->isValidPhoneNumber('invalid'));
        $this->assertFalse($this->phoneNumbers->isValidPhoneNumber('123'));

        // Test edge cases
        $this->assertFalse($this->phoneNumbers->isValidPhoneNumber(null));
        $this->assertFalse($this->phoneNumbers->isValidPhoneNumber(''));
        $this->assertFalse($this->phoneNumbers->isValidPhoneNumber('0'));
    }

    #[Test]
    public function it_can_get_phone_number_type(): void
    {
        // Test mobile numbers
        $type1 = $this->phoneNumbers->getPhoneNumberType('+4799999999');
        $this->assertNotNull($type1);
        $this->assertIsString($type1);

        // Test invalid numbers
        $this->assertNull($this->phoneNumbers->getPhoneNumberType('invalid'));

        // Test edge cases
        $this->assertNull($this->phoneNumbers->getPhoneNumberType(null));
        $this->assertNull($this->phoneNumbers->getPhoneNumberType(''));
        $this->assertNull($this->phoneNumbers->getPhoneNumberType('0'));
    }

    #[Test]
    public function it_can_get_phone_number_region(): void
    {
        // Test Norwegian numbers
        $region1 = $this->phoneNumbers->getPhoneNumberRegion('+4799999999');
        $this->assertNotNull($region1);
        $this->assertIsString($region1);
        $this->assertEquals('NO', $region1);

        // Test US numbers
        $region2 = $this->phoneNumbers->getPhoneNumberRegion('+12025550179');
        $this->assertNotNull($region2);
        $this->assertIsString($region2);
        $this->assertEquals('US', $region2);

        // Test invalid numbers
        $this->assertNull($this->phoneNumbers->getPhoneNumberRegion('invalid'));

        // Test edge cases
        $this->assertNull($this->phoneNumbers->getPhoneNumberRegion(null));
        $this->assertNull($this->phoneNumbers->getPhoneNumberRegion(''));
        $this->assertNull($this->phoneNumbers->getPhoneNumberRegion('0'));
    }

    #[Test]
    public function it_can_convert_to_e164_format(): void
    {
        // Test valid phone numbers
        $e164_1 = $this->phoneNumbers->toE164('+4799999999');
        $this->assertNotNull($e164_1);
        $this->assertIsString($e164_1);
        $this->assertStringStartsWith('+47', $e164_1);

        // Test with different regions
        $e164_2 = $this->phoneNumbers->toE164('99999999', 'NO');
        $this->assertNotNull($e164_2);
        $this->assertIsString($e164_2);
        $this->assertStringStartsWith('+47', $e164_2);

        // Test invalid phone numbers
        $this->assertEquals('invalid', $this->phoneNumbers->toE164('invalid'));

        // Test edge cases
        $this->assertNull($this->phoneNumbers->toE164(null));
        $this->assertNull($this->phoneNumbers->toE164(''));
        $this->assertNull($this->phoneNumbers->toE164('0'));
    }

    #[Test]
    public function it_can_convert_to_national_format(): void
    {
        // Test valid phone numbers
        $national1 = $this->phoneNumbers->toNationalFormat('+4799999999');
        $this->assertNotNull($national1);
        $this->assertIsString($national1);
        $this->assertStringNotContainsString('+', $national1);

        // Test with different regions
        $national2 = $this->phoneNumbers->toNationalFormat('99999999', 'NO');
        $this->assertNotNull($national2);
        $this->assertIsString($national2);
        $this->assertStringNotContainsString('+', $national2);

        // Test invalid phone numbers
        $this->assertEquals('invalid', $this->phoneNumbers->toNationalFormat('invalid'));

        // Test edge cases
        $this->assertNull($this->phoneNumbers->toNationalFormat(null));
        $this->assertNull($this->phoneNumbers->toNationalFormat(''));
        $this->assertNull($this->phoneNumbers->toNationalFormat('0'));
    }

    #[Test]
    public function it_can_convert_to_international_format(): void
    {
        // Test valid phone numbers
        $international1 = $this->phoneNumbers->toInternationalFormat('+4799999999');
        $this->assertNotNull($international1);
        $this->assertIsString($international1);
        $this->assertStringStartsWith('+47', $international1);
        $this->assertStringContainsString(' ', $international1);

        // Test with different regions
        $international2 = $this->phoneNumbers->toInternationalFormat('99999999', 'NO');
        $this->assertNotNull($international2);
        $this->assertIsString($international2);
        $this->assertStringStartsWith('+47', $international2);
        $this->assertStringContainsString(' ', $international2);

        // Test invalid phone numbers
        $this->assertEquals('invalid', $this->phoneNumbers->toInternationalFormat('invalid'));

        // Test edge cases
        $this->assertNull($this->phoneNumbers->toInternationalFormat(null));
        $this->assertNull($this->phoneNumbers->toInternationalFormat(''));
        $this->assertNull($this->phoneNumbers->toInternationalFormat('0'));
    }

    #[Test]
    public function it_has_default_region(): void
    {
        $this->assertEquals('NO', $this->phoneNumbers->publicGetDefaultRegion());
    }

    #[Test]
    public function it_can_get_phone_util_instance(): void
    {
        $this->assertInstanceOf(PhoneNumberUtil::class, $this->phoneNumbers->publicGetPhoneUtil());
    }

    #[Test]
    public function it_can_manually_normalize_norwegian_numbers(): void
    {
        // Test with +47 prefix
        $this->assertEquals('12345678', $this->phoneNumbers->publicManualNorwegianNormalization('+4712345678'));

        // Test with 0047 prefix
        $this->assertEquals('12345678', $this->phoneNumbers->publicManualNorwegianNormalization('004712345678'));

        // Test with 47 prefix and correct length
        $this->assertEquals('12345678', $this->phoneNumbers->publicManualNorwegianNormalization('4712345678'));

        // Test without prefix
        $this->assertEquals('12345678', $this->phoneNumbers->publicManualNorwegianNormalization('12345678'));

        // Test with spaces and other characters
        $this->assertEquals('12345678', $this->phoneNumbers->publicManualNorwegianNormalization('+47 123 45 678'));
    }
}
