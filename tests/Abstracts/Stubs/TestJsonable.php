<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Abstracts\Stubs;

use EinarHansen\Toolkit\Abstracts\Jsonable;
use Override;

/**
 * Konkret implementasjon av den abstrakte Jsonable-klassen for testing
 */
final class TestJsonable extends Jsonable
{
    #[Override]
    public function toArray(): array
    {
        return [
            'name' => 'Test',
            'value' => 123,
        ];
    }
}
