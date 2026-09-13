<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Exceptions\Excluded;

use RuntimeException;

/**
 * Fixture for the rule's excludedPaths wiring — stands in for
 * src/App/Console/Commands, where an empty catch degrades a debug printout
 * rather than a user flow. The bare catch here must NOT be flagged.
 */
final class ExcludedCatch
{
    public function bare(): void
    {
        try {
            $this->work();
        } catch (RuntimeException) {
        }
    }

    private function work(): never
    {
        throw new RuntimeException('boom');
    }
}
