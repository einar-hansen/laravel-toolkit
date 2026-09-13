<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An abstract base legitimately does not know its model yet.
 */
abstract class AbstractBaseResource extends JsonResource {}
