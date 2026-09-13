<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\LiteralStringArgumentRule;
use EinarHansen\Toolkit\PHPStan\Rules\LiteralStringReturnRule;
use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Sql\LiteralStringContracts;
use EinarHansen\Toolkit\Tests\Support\PHPStan\LiteralStringRuleTestCase;

class LiteralStringRulesTest extends LiteralStringRuleTestCase
{
    public function test_a_return_literal_string_that_is_not_one_is_flagged(): void
    {
        $fixture = LiteralStringRuleTestCase::FIXTURES.'/LiteralStringContracts.php';

        $this->rule = new LiteralStringReturnRule;

        // Only the two bad producers fire: the fixture's literal-from-constants
        // and literal-from-literal-parameter returns are proven silent by the
        // exact-set assertion.
        $this->analyse([$fixture], [
            [sprintf(LiteralStringRuleTestCase::RETURN_MESSAGE, 'returnsPlainString', 'string'), 33],
            [sprintf(LiteralStringRuleTestCase::RETURN_MESSAGE, 'returnsInterpolatedInt', 'string'), 44],
        ]);
    }

    public function test_a_param_literal_string_given_a_plain_string_is_flagged(): void
    {
        $fixture = LiteralStringRuleTestCase::FIXTURES.'/LiteralStringContracts.php';
        $subject = LiteralStringContracts::class;

        $this->rule = new LiteralStringArgumentRule(['EinarHansen\Toolkit\Tests\Fixtures\\']);

        $this->analyse([$fixture], [
            [
                sprintf(
                    LiteralStringRuleTestCase::ARGUMENT_MESSAGE,
                    'column',
                    $subject,
                    'literalFromLiteralParameter',
                    'string',
                ),
                54,
            ],
        ]);
    }

    public function test_a_method_outside_the_configured_namespaces_is_not_checked(): void
    {
        $fixture = LiteralStringRuleTestCase::FIXTURES.'/LiteralStringContracts.php';

        $this->rule = new LiteralStringArgumentRule(['App\Nothing\\']);

        $this->analyse([$fixture], []);
    }
}
