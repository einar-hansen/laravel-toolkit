<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

class UnannotatedModel extends Model
{
    #[Override]
    protected $table = 'unannotated_models';
}
