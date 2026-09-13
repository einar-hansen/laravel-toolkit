<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

final class NotAResourceEither
{
    public string $name = '';

    public function read(): string
    {
        return $this->name;
    }
}
