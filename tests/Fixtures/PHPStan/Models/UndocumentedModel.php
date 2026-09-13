<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

class UndocumentedModel extends Model
{
    #[Override]
    protected $table = 'undocumented_models';
}
