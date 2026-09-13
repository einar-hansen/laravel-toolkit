<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Support\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<Rule>
 */
abstract class LiteralStringRuleTestCase extends RuleTestCase
{
    public const string FIXTURES = __DIR__.'/../../Fixtures/PHPStan/Sql';

    public const string RETURN_MESSAGE = '%s() is annotated `@return literal-string` but returns %s. '
        .'Every byte of a literal-string has to come from source code — build it from string '
        .'literals, constants, enum cases and other literal-strings, and put '
        .'anything runtime-derived in a query binding instead.';

    public const string ARGUMENT_MESSAGE = 'Parameter $%s of %s::%s() expects literal-string, %s given. '
        .'The annotation is a guarantee that the value comes from source code rather than from a '
        .'request; passing a plain string silently breaks it.';

    public Rule $rule;

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}
