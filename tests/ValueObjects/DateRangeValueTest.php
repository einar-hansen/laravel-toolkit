<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\ValueObjects;

use Carbon\Carbon;
use EinarHansen\Toolkit\Tests\Utilities\ValueObjects\TestDateRangeValue;
use EinarHansen\Toolkit\ValueObjects\DateRangeValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DateRangeValueTest extends TestCase
{
    public function test_can_create_valid_date_range_value_from_string(): void
    {
        $dateString = '2022-06-15';
        $testDateRangeValue = new TestDateRangeValue($dateString);

        $this->assertInstanceOf(DateRangeValue::class, $testDateRangeValue);
        $this->assertEquals(Carbon::parse($dateString), $testDateRangeValue->value());
        $this->assertEquals($dateString, (string) $testDateRangeValue);
    }

    public function test_can_create_valid_date_range_value_from_carbon_instance(): void
    {
        $carbonDate = Carbon::parse('2022-06-15');
        $testDateRangeValue = new TestDateRangeValue($carbonDate);

        $this->assertInstanceOf(DateRangeValue::class, $testDateRangeValue);
        $this->assertEquals($carbonDate, $testDateRangeValue->value());
        $this->assertEquals('2022-06-15', (string) $testDateRangeValue);
    }

    public function test_throws_exception_when_date_exceeds_max_date(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Date exceeds maximum of 2025-12-31');

        new TestDateRangeValue('2026-01-01');
    }

    public function test_throws_exception_when_date_before_min_date(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Date is before minimum of 2020-01-01');

        new TestDateRangeValue('2019-12-31');
    }

    public function test_can_create_date_with_maximum_allowed_date(): void
    {
        $maxDate = '2025-12-31';
        $testDateRangeValue = new TestDateRangeValue($maxDate);

        $this->assertEquals(Carbon::parse($maxDate), $testDateRangeValue->value());
        $this->assertEquals($maxDate, (string) $testDateRangeValue);
    }

    public function test_can_create_date_with_minimum_allowed_date(): void
    {
        $minDate = '2020-01-01';
        $testDateRangeValue = new TestDateRangeValue($minDate);

        $this->assertEquals(Carbon::parse($minDate), $testDateRangeValue->value());
        $this->assertEquals($minDate, (string) $testDateRangeValue);
    }

    public function test_can_parse_various_date_formats(): void
    {
        $formats = [
            '2022-06-15',
            '15-Jun-2022',
            'June 15, 2022',
            '2022/06/15',
            '15.06.2022',
            '15 June 2022',
            'next Monday',
        ];

        // We need to set a fixed "now" to make the "next Monday" test predictable
        Carbon::setTestNow(Carbon::parse('2022-06-10'));

        foreach ($formats as $format) {
            $expectedCarbon = Carbon::parse($format);
            // Skip if the date is outside our test range
            if ($expectedCarbon->isBefore(Carbon::parse('2020-01-01'))) {
                continue;
            }

            if ($expectedCarbon->isAfter(Carbon::parse('2025-12-31'))) {
                continue;
            }

            $dateValue = new TestDateRangeValue($format);
            $this->assertEquals($expectedCarbon->format('Y-m-d'), (string) $dateValue);
        }

        // Reset test time
        Carbon::setTestNow();
    }

    public function test_throws_exception_for_invalid_date_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date format');

        new TestDateRangeValue('not a date');
    }

    public function test_from_method_creates_instance_with_valid_value(): void
    {
        $value = '2022-06-15';
        $instance = TestDateRangeValue::from($value);

        $this->assertInstanceOf(TestDateRangeValue::class, $instance);
        $this->assertEquals(Carbon::parse($value)->format('Y-m-d'), (string) $instance);
    }

    public function test_from_method_throws_exception_with_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TestDateRangeValue::from('2019-12-31'); // Before min date
    }

    public function test_try_from_method_returns_instance_with_valid_value(): void
    {
        $value = '2022-06-15';
        $instance = TestDateRangeValue::tryFrom($value);

        $this->assertInstanceOf(TestDateRangeValue::class, $instance);
        $this->assertEquals(Carbon::parse($value)->format('Y-m-d'), (string) $instance);
    }

    public function test_try_from_method_returns_null_with_invalid_value(): void
    {
        $instance = TestDateRangeValue::tryFrom('2019-12-31'); // Before min date
        $this->assertNull($instance);

        $instance = TestDateRangeValue::tryFrom('not a date'); // Invalid format
        $this->assertNull($instance);
    }

    public function test_from_method_creates_instance_with_carbon_instance(): void
    {
        $carbonDate = Carbon::parse('2022-06-15');
        $instance = TestDateRangeValue::from($carbonDate);

        $this->assertInstanceOf(TestDateRangeValue::class, $instance);
        $this->assertEquals('2022-06-15', (string) $instance);
    }

    public function test_try_from_method_returns_instance_with_carbon_instance(): void
    {
        $carbonDate = Carbon::parse('2022-06-15');
        $instance = TestDateRangeValue::tryFrom($carbonDate);

        $this->assertInstanceOf(TestDateRangeValue::class, $instance);
        $this->assertEquals('2022-06-15', (string) $instance);
    }
}
