<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * A model carrying every tag the rule rejects, plus the one it wants.
 *
 * @property int $id
 * @property-read string $display_name
 * @property-write string $secret
 *
 * @method static self findOrFail(int $id)
 *
 * @mixin Model
 * @mixin IdeHelperHandwrittenAnnotations
 */
class HandwrittenAnnotations extends Model
{
    #[Override]
    protected $table = 'handwritten_annotations';
}
