<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * `$collection`, not `$resource`, is the typed thing on a collection.
 */
final class FixtureResourceCollection extends ResourceCollection {}
