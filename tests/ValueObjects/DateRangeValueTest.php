<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Unit\ValueObjects;

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
        $dateValue = new TestDateRangeValue($dateString);

        $this->assertInstanceOf(DateRangeValue::class, $dateValue);
        $this->assertEquals(Carbon::parse($dateString), $dateValue->value());
        $this->assertEquals($dateString, (string) $dateValue);
    }

    public function test_can_create_valid_date_range_value_from_carbon_instance(): void
    {
        $carbonDate = Carbon::parse('2022-06-15');
        $dateValue = new TestDateRangeValue($carbonDate);

        $this->assertInstanceOf(DateRangeValue::class, $dateValue);
        $this->assertEquals($carbonDate, $dateValue->value());
        $this->assertEquals('2022-06-15', (string) $dateValue);
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
        $dateValue = new TestDateRangeValue($maxDate);

        $this->assertEquals(Carbon::parse($maxDate), $dateValue->value());
        $this->assertEquals($maxDate, (string) $dateValue);
    }

    public function test_can_create_date_with_minimum_allowed_date(): void
    {
        $minDate = '2020-01-01';
        $dateValue = new TestDateRangeValue($minDate);

        $this->assertEquals(Carbon::parse($minDate), $dateValue->value());
        $this->assertEquals($minDate, (string) $dateValue);
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
            if ($expectedCarbon->isBefore(Carbon::parse('2020-01-01')) ||
                $expectedCarbon->isAfter(Carbon::parse('2025-12-31'))) {
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
}
