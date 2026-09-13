<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Prose explaining what this model is for survives — only the generated
 * annotation tags are rejected.
 *
 * @mixin IdeHelperCleanDocblockModel
 */
class CleanDocblockModel extends Model
{
    #[Override]
    protected $table = 'clean_docblock_models';
}
