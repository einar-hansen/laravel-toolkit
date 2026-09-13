<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models;

use stdClass;

/**
 * Not an Eloquent model, so its hand-written annotations are its own business.
 *
 * @property int $id
 *
 * @method static self make()
 *
 * @mixin stdClass
 */
final class AnnotatedNonModel {}
