<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

/**
 * Not a JsonResource, so its toArray() is not an API payload.
 */
final class NotAResourceWithIntId
{
    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return ['id' => 7, 'location_id' => 9];
    }
}
