<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\ValueObjects;

use InvalidArgumentException;
use Override;
use Stringable;

abstract class StringLengthValue implements Stringable
{
    public function __construct(
        protected readonly string $value
    ) {
        $this->validate();
    }

    abstract protected function getMaxLength(): ?int;

    abstract protected function getMinLength(): ?int;

    protected function validate(): void
    {
        if ($this->getMaxLength() !== null && mb_strlen($this->value) > $this->getMaxLength()) {
            throw new InvalidArgumentException(
                sprintf(
                    'String length exceeds maximum of %d characters for %s',
                    $this->getMaxLength(),
                    static::class
                )
            );
        }

        if ($this->getMinLength() !== null && mb_strlen($this->value) < $this->getMinLength()) {
            throw new InvalidArgumentException(
                sprintf(
                    'String length is less than minimum of %d characters for %s',
                    $this->getMinLength(),
                    static::class
                )
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
