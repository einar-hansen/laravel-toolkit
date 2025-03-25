<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Utilities\ValueObjects;

use EinarHansen\Toolkit\ValueObjects\StringLengthValue;
use Override;

class TestStringLengthValue extends StringLengthValue
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    #[Override]
    protected function getMaxLength(): ?int
    {
        return 50;
    }

    #[Override]
    protected function getMinLength(): ?int
    {
        return 5;
    }
}
