<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Support\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\NoHardcodedApiMessageRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoHardcodedApiMessageRule>
 */
abstract class HardcodedApiMessageRuleTestCase extends RuleTestCase
{
    /**
     * The exact message the rule emits, with the offending literal
     * interpolated. Kept here so the test file reads as expectations.
     */
    public const string HARDCODED_MESSAGE = 'Hardcoded API message "%s" in response()->json(). '
        .'Translate user-facing messages with __() or trans(), '
        ."or 'message' => __('messages.namespace.key') for success responses.";

    public const string FIXTURES = __DIR__.'/../../Fixtures/PHPStan';

    protected function getRule(): Rule
    {
        // Wired to the fixtures' Controllers/ dir, mirroring how phpstan.neon
        // wires it to src/App/Http/Controllers in the real run.
        return new NoHardcodedApiMessageRule([
            (string) realpath(self::FIXTURES.'/Controllers'),
        ]);
    }
}
