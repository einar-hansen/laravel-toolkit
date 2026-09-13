<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Sql;

use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Sql\SqlIdentifier;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class RawSqlCalls
{
    private const string SORT_COLUMN = 'created_at';

    public function safe(Builder $query, string $direction, int $threshold): void
    {
        $query->whereRaw('amount_minor > ?', [$threshold]);
        $query->orderByRaw('created_at '.($direction === 'asc' ? 'asc' : 'desc'));
        $query->selectRaw('(amount_minor + '.$threshold.') AS total');
        $query->groupByRaw(self::SORT_COLUMN);
        $query->havingRaw("SUM(amount_minor) > {$threshold}");
        $query->selectRaw(sprintf('%s AS sort_key', self::SORT_COLUMN));
        $query->whereRaw($this->fragment().' AND deleted_at IS NULL');
        DB::statement('DROP SCHEMA '.SqlIdentifier::quote($direction).' CASCADE');
    }

    public function unsafeInterpolation(Builder $query, string $column): void
    {
        $query->orderByRaw("{$column} desc");
    }

    public function unsafeConcatenation(Builder $query, string $column): void
    {
        $query->whereRaw($column.' IS NOT NULL');
    }

    public function unsafeSprintf(Builder $query, string $column): void
    {
        $query->selectRaw(sprintf('%s AS sort_key', $column));
    }

    public function unsafeConnectionStatement(ConnectionInterface $connection, string $schema): void
    {
        $connection->statement('DROP SCHEMA '.$schema);
    }

    public function unsafeFacadeSelect(string $table): void
    {
        DB::select('SELECT * FROM '.$table);
    }

    /**
     * Bindings are never inspected — only argument 0 is the SQL.
     */
    public function bindingsAreNotSql(Builder $query, string $anything): void
    {
        $query->whereRaw('name = ?', [$anything]);
    }

    /**
     * A `select()` that is not on a connection is a column list, not SQL.
     */
    public function builderSelectIsNotRawSql(Builder $query, string $column): void
    {
        $query->select($column);
    }

    /**
     * @return literal-string
     */
    private function fragment(): string
    {
        return "status = 'active'";
    }
}
