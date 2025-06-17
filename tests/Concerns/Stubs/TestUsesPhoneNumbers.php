<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Concerns\Stubs;

use EinarHansen\Toolkit\Concerns\UsesPhoneNumbers;
use libphonenumber\PhoneNumberUtil;

final class TestUsesPhoneNumbers
{
    use UsesPhoneNumbers;

    /**
     * Public wrapper for the protected getDefaultRegion method.
     */
    public function publicGetDefaultRegion(): string
    {
        return $this->getDefaultRegion();
    }

    /**
     * Public wrapper for the private getPhoneUtil method.
     */
    public function publicGetPhoneUtil(): PhoneNumberUtil
    {
        return $this->getPhoneUtil();
    }

    /**
     * Public wrapper for the private manualNorwegianNormalization method.
     */
    public function publicManualNorwegianNormalization(string $phone): string
    {
        return $this->manualNorwegianNormalization($phone);
    }

    /**
     * Override the default region for testing purposes.
     */
    protected function getDefaultRegion(): string
    {
        return 'NO'; // Keep Norwegian as default for tests
    }
}
