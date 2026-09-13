<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Support\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\ResourceIdIsNotAutoIncrementRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ResourceIdIsNotAutoIncrementRule>
 */
abstract class ResourceIdRuleTestCase extends RuleTestCase
{
    public const string FIXTURES = __DIR__.'/../../Fixtures/PHPStan/Resources';

    /**
     * The exact message the rule emits, with the offending key interpolated.
     * Kept here so the test file reads as expectations.
     */
    public const string EXPOSED_MESSAGE = "Resource exposes `%s` as an integer identifier. Use the record's `ulid` or `slug` "
        .'to follow the public-identifier policy. Keep authorization checks in place.';

    protected function getRule(): Rule
    {
        return new ResourceIdIsNotAutoIncrementRule;
    }
}
