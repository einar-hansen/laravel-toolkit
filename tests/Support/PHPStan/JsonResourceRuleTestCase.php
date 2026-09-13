<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Support\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<Rule>
 */
abstract class JsonResourceRuleTestCase extends RuleTestCase
{
    public const string FIXTURES = __DIR__.'/../../Fixtures/PHPStan/Resources';

    public Rule $rule;

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}
