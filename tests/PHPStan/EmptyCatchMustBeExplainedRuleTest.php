<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\Tests\Support\PHPStan\EmptyCatchRuleTestCase;

class EmptyCatchMustBeExplainedRuleTest extends EmptyCatchRuleTestCase
{
    public function test_only_the_unexplained_empty_catches_are_flagged(): void
    {
        // The fixture also covers a line comment, a block comment, a comment placed
        // above the `catch`, a handled body and a multi-catch. analyse() asserts the
        // EXACT error set for the file, so any pass case that wrongly fired shows up
        // here as an extra error.
        //
        // The two numbers are the `catch` lines of EmptyCatches::bare() and
        // ::blank(). They shift if anything above them in the fixture moves —
        // including a pint run that adds an import — so re-read the fixture before
        // assuming a mismatch here means the rule broke.
        $this->analyse(
            [EmptyCatchRuleTestCase::FIXTURES.'/Exceptions/EmptyCatches.php'],
            [
                [EmptyCatchRuleTestCase::UNEXPLAINED, 24],
                [EmptyCatchRuleTestCase::UNEXPLAINED, 33],
            ],
        );
    }

    public function test_a_file_under_an_excluded_path_is_never_flagged(): void
    {
        $this->analyse(
            [EmptyCatchRuleTestCase::FIXTURES.'/Exceptions/Excluded/ExcludedCatch.php'],
            [],
        );
    }
}
