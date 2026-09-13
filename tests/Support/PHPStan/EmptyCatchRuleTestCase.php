<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Support\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\EmptyCatchMustBeExplainedRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<EmptyCatchMustBeExplainedRule>
 */
abstract class EmptyCatchRuleTestCase extends RuleTestCase
{
    /** The exact message the rule emits. Kept here so the test file reads as expectations. */
    public const string UNEXPLAINED = 'Empty catch block with no explanation. A discarded exception is '
        .'indistinguishable from a swallowed failure — add a comment saying '
        .'why this one is safe to ignore, or handle it (log, rethrow, or '
        .'surface it to the caller).';

    public const string FIXTURES = __DIR__.'/../../Fixtures/PHPStan';

    protected function getRule(): Rule
    {
        // Wired to a fixture subdirectory, mirroring how phpstan.neon excludes
        // src/App/Console/Commands in the real run.
        return new EmptyCatchMustBeExplainedRule([
            realpath(self::FIXTURES.'/Exceptions').'/Excluded',
        ]);
    }
}
