<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Sql;

final class LiteralStringContracts
{
    private const string TABLE = 'payments';

    /**
     * @return literal-string
     */
    public function literalFromParts(): string
    {
        return 'SELECT * FROM '.self::TABLE.' WHERE status = ?';
    }

    /**
     * @param  literal-string  $column
     * @return literal-string
     */
    public function literalFromLiteralParameter(string $column): string
    {
        return $column.' IS NOT NULL';
    }

    /**
     * @return literal-string
     */
    public function returnsPlainString(string $column): string
    {
        return $column.' IS NOT NULL';
    }

    /**
     * An interpolated int is safe to embed in SQL but is not a literal-string,
     * and claiming otherwise would let the raw-SQL rule trust it transitively.
     *
     * @return literal-string
     */
    public function returnsInterpolatedInt(int $limit): string
    {
        return "LIMIT {$limit}";
    }

    public function callsWithLiteral(): string
    {
        return $this->literalFromLiteralParameter('deleted_at');
    }

    public function callsWithPlainString(string $column): string
    {
        return $this->literalFromLiteralParameter($column);
    }

    /**
     * A parameter with no literal-string annotation takes anything.
     */
    public function unannotatedParameterIsUnchecked(string $column): string
    {
        return $this->plain($column);
    }

    private function plain(string $column): string
    {
        return $column;
    }
}
