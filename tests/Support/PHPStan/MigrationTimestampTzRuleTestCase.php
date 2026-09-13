<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Support\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\MigrationTimestampTzRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MigrationTimestampTzRule>
 */
abstract class MigrationTimestampTzRuleTestCase extends RuleTestCase
{
    public const string FIXTURES = __DIR__.'/../../Fixtures/PHPStan';

    /**
     * The exact message the rule emits, with the offending method and its
     * replacement interpolated. Kept here so the test file reads as
     * expectations.
     */
    public const string NON_TZ_MESSAGE = 'Migration uses $table->%s(), which creates a '
        .'`timestamp without time zone` column. Use $table->%s() instead — the untyped '
        .'variant stores wall-clock time, so the offset is lost and the hour repeated at '
        .'the end of October is unrecoverable.';

    protected function getRule(): Rule
    {
        // Wired to the fixtures' Migrations/ dir, mirroring how phpstan.neon
        // wires it to database/migrations in the real run.
        return new MigrationTimestampTzRule([
            (string) realpath(self::FIXTURES.'/Migrations'),
        ]);
    }
}
