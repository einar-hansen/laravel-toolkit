<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * A model whose docblock says plenty and declares nothing. Prose is not the
 * tag: larastan still cannot see a single column here.
 */
class DescribedModel extends Model
{
    #[Override]
    protected $table = 'described_models';
}
