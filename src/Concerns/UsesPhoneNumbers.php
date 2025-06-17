<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Concerns;

use libphonenumber\MatchType;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

trait UsesPhoneNumbers
{
    private static ?PhoneNumberUtil $phoneUtil = null;

    /**
     * Check if two phone numbers match
     */
    public function phoneNumbersMatch(?string $phone1, ?string $phone2, ?string $region = null): bool
    {
        if ($phone1 === null || $phone1 === '' || $phone1 === '0' || ($phone2 === null || $phone2 === '' || $phone2 === '0')) {
            return false;
        }

        $region ??= $this->getDefaultRegion();
        $phoneUtil = $this->getPhoneUtil();

        try {
            $number1 = $phoneUtil->parse($phone1, $region);
            $number2 = $phoneUtil->parse($phone2, $region);

            return $phoneUtil->isNumberMatch($number1, $number2) === MatchType::EXACT_MATCH;
        } catch (NumberParseException) {
            // Fallback to manual normalization if parsing fails
            return $this->normalizePhoneNumber($phone1, $region) === $this->normalizePhoneNumber($phone2, $region);
        }
    }

    /**
     * Normalize a phone number to its basic format
     */
    public function normalizePhoneNumber(?string $phone, ?string $region = null): ?string
    {
        if ($phone === null || $phone === '' || $phone === '0') {
            return null;
        }

        $region ??= $this->getDefaultRegion();

        // Try with libphonenumber first
        try {
            $phoneUtil = $this->getPhoneUtil();
            $number = $phoneUtil->parse($phone, $region);

            if ($phoneUtil->isValidNumber($number)) {
                return $phoneUtil->format($number, PhoneNumberFormat::NATIONAL);
            }
        } catch (NumberParseException) {
            // Fall through to manual normalization
        }

        // Manual normalization as fallback (Norwegian-specific)
        if ($region === 'NO') {
            return $this->manualNorwegianNormalization($phone);
        }

        // For other regions, just clean digits
        return preg_replace('/\D/', '', $phone);
    }

    /**
     * Format a phone number in a specific format
     */
    public function formatPhoneNumber(?string $phone, PhoneNumberFormat $format = PhoneNumberFormat::INTERNATIONAL, ?string $region = null): ?string
    {
        if ($phone === null || $phone === '' || $phone === '0') {
            return null;
        }

        $region ??= $this->getDefaultRegion();

        try {
            $phoneUtil = $this->getPhoneUtil();
            $number = $phoneUtil->parse($phone, $region);

            if ($phoneUtil->isValidNumber($number)) {
                return $phoneUtil->format($number, $format);
            }
        } catch (NumberParseException) {
            // Return original if parsing fails
        }

        return $phone;
    }

    /**
     * Validate if a phone number is valid for the specified region
     */
    public function isValidPhoneNumber(?string $phone, ?string $region = null): bool
    {
        if ($phone === null || $phone === '' || $phone === '0') {
            return false;
        }

        $region ??= $this->getDefaultRegion();

        try {
            $phoneUtil = $this->getPhoneUtil();
            $number = $phoneUtil->parse($phone, $region);

            return $phoneUtil->isValidNumber($number);
        } catch (NumberParseException) {
            return false;
        }
    }

    /**
     * Get the phone number type (mobile, fixed line, etc.)
     */
    public function getPhoneNumberType(?string $phone, ?string $region = null): ?string
    {
        if ($phone === null || $phone === '' || $phone === '0') {
            return null;
        }

        $region ??= $this->getDefaultRegion();

        try {
            $phoneUtil = $this->getPhoneUtil();
            $number = $phoneUtil->parse($phone, $region);

            if (! $phoneUtil->isValidNumber($number)) {
                return null;
            }

            $type = $phoneUtil->getNumberType($number);

            return match ($type) {
                PhoneNumberType::MOBILE => 'mobile',
                PhoneNumberType::FIXED_LINE => 'fixed_line',
                PhoneNumberType::FIXED_LINE_OR_MOBILE => 'fixed_line_or_mobile',
                PhoneNumberType::VOIP => 'voip',
                PhoneNumberType::PREMIUM_RATE => 'premium_rate',
                PhoneNumberType::TOLL_FREE => 'toll_free',
                default => 'unknown'
            };
        } catch (NumberParseException) {
            return null;
        }
    }

    /**
     * Get the region code for a phone number
     */
    public function getPhoneNumberRegion(?string $phone, ?string $defaultRegion = null): ?string
    {
        if ($phone === null || $phone === '' || $phone === '0') {
            return null;
        }

        $defaultRegion ??= $this->getDefaultRegion();

        try {
            $phoneUtil = $this->getPhoneUtil();
            $number = $phoneUtil->parse($phone, $defaultRegion);

            return $phoneUtil->getRegionCodeForNumber($number);
        } catch (NumberParseException) {
            return null;
        }
    }

    /**
     * Convert phone number to E164 format (+4712345678)
     */
    public function toE164(?string $phone, ?string $region = null): ?string
    {
        return $this->formatPhoneNumber($phone, PhoneNumberFormat::E164, $region);
    }

    /**
     * Convert phone number to national format (12 34 56 78)
     */
    public function toNationalFormat(?string $phone, ?string $region = null): ?string
    {
        return $this->formatPhoneNumber($phone, PhoneNumberFormat::NATIONAL, $region);
    }

    /**
     * Convert phone number to international format (+47 12 34 56 78)
     */
    public function toInternationalFormat(?string $phone, ?string $region = null): ?string
    {
        return $this->formatPhoneNumber($phone, PhoneNumberFormat::INTERNATIONAL, $region);
    }

    /**
     * Default region code (can be overridden in implementing classes)
     */
    protected function getDefaultRegion(): string
    {
        return 'NO'; // Norwegian default
    }

    /**
     * Get the PhoneNumberUtil instance (singleton pattern for performance)
     */
    private function getPhoneUtil(): PhoneNumberUtil
    {
        if (! self::$phoneUtil instanceof PhoneNumberUtil) {
            self::$phoneUtil = PhoneNumberUtil::getInstance();
        }

        return self::$phoneUtil;
    }

    /**
     * Manual Norwegian phone number normalization
     */
    private function manualNorwegianNormalization(string $phone): string
    {
        $cleaned = preg_replace('/[^\d+]/', '', $phone);

        if (str_starts_with((string) $cleaned, '+47')) {
            return mb_substr((string) $cleaned, 3);
        }

        if (str_starts_with((string) $cleaned, '0047')) {
            return mb_substr((string) $cleaned, 4);
        }

        if (str_starts_with((string) $cleaned, '47') && mb_strlen((string) $cleaned) === 10) {
            return mb_substr((string) $cleaned, 2);
        }

        return $cleaned;
    }
}
