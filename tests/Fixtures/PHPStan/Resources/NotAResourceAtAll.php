<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

final class NotAResourceAtAll
{
    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [];
    }
}
