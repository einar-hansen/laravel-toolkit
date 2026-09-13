<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Sql\Excluded;

use Illuminate\Database\Query\Builder;

final class ExcludedRawSql
{
    public function wouldOtherwiseBeFlagged(Builder $query, string $column): void
    {
        $query->orderByRaw("{$column} desc");
    }
}
