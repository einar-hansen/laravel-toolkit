<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\Tests\Support\PHPStan\RawSqlRuleTestCase;

class RawSqlNoInterpolationRuleTest extends RawSqlRuleTestCase
{
    public function test_only_operands_that_are_not_provably_literal_are_flagged(): void
    {
        // analyse() asserts the EXACT error set for the file, so every pass-case
        // in the fixture — a binding, an 'asc'|'desc' ternary, an interpolated
        // int, a class constant, a constant-format sprintf, a literal-string
        // producer, SqlIdentifier::quote(), a builder select() that is a column
        // list rather than SQL — is proven silent by this same assertion.
        $this->analyse(
            [RawSqlRuleTestCase::FIXTURES.'/RawSqlCalls.php'],
            [
                [RawSqlRuleTestCase::message('orderByRaw'), 30],
                [RawSqlRuleTestCase::message('whereRaw'), 35],
                [RawSqlRuleTestCase::message('selectRaw'), 40],
                [RawSqlRuleTestCase::message('statement'), 45],
                [RawSqlRuleTestCase::message('select'), 50],
            ],
        );
    }

    public function test_an_excluded_path_is_never_flagged(): void
    {
        $this->analyse(
            [RawSqlRuleTestCase::FIXTURES.'/Excluded/ExcludedRawSql.php'],
            [],
        );
    }
}
