<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @mixin IdeHelperAnnotatedModel
 */
class AnnotatedModel extends Model
{
    #[Override]
    protected $table = 'annotated_models';
}
