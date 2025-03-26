<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Utilities\ValueObjects;

use EinarHansen\Toolkit\ValueObjects\StringRegexValue;
use Override;

class TestStringRegexValue extends StringRegexValue
{
    /**
     * Example pattern for email validation
     */
    #[Override]
    protected function getPattern(): ?string
    {
        return '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    }

    /**
     * Custom error message for this specific implementation
     */
    #[Override]
    protected function getPatternErrorMessage(): string
    {
        return sprintf('"%s" is not a valid email address format', $this->value);
    }
}
