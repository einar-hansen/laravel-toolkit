<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * The copy-paste case: a real-looking mixin naming somebody else's helper, so
 * the model types against a different table entirely.
 *
 * @mixin IdeHelperAnnotatedModel
 */
class WrongMixinModel extends Model
{
    #[Override]
    protected $table = 'wrong_mixin_models';
}
