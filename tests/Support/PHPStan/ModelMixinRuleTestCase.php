<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Support\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\ModelMixinRequiredRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ModelMixinRequiredRule>
 */
abstract class ModelMixinRuleTestCase extends RuleTestCase
{
    public const string FIXTURES = __DIR__.'/../../Fixtures/PHPStan/Models';

    protected function getRule(): Rule
    {
        return new ModelMixinRequiredRule;
    }
}
