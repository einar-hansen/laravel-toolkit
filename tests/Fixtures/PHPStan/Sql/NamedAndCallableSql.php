<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Sql;

use Illuminate\Support\Facades\DB;

final class NamedAndCallableSql
{
    public function queries(string $sql): void
    {
        $callable = DB::statement(...);
        DB::statement(bindings: [], query: $sql);
        DB::statement(query: 'select 1', bindings: []);
    }

    /** @param literal-string $column */
    public function column(string $column): void {}

    public function calls(string $input): void
    {
        $callable = $this->column(...);
        $this->column(column: $input);
        $this->column(column: 'email');
    }
}
