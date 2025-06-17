<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Utilities\ValueObjects;

use EinarHansen\Toolkit\ValueObjects\PhoneNumberValue;
use Override;

final class TestPhoneNumberValue extends PhoneNumberValue
{
    #[Override]
    public function __toString(): string
    {
        return (string) $this->e164();
    }

    protected function getDefaultRegion(): string
    {
        return 'NO';
    }
}
