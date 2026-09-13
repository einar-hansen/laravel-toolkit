<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Support\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\ModelDocblockNoHandwrittenAnnotationsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ModelDocblockNoHandwrittenAnnotationsRule>
 */
abstract class ModelDocblockRuleTestCase extends RuleTestCase
{
    public const string FIXTURES = __DIR__.'/../../Fixtures/PHPStan/Models';

    /**
     * The exact messages the rule emits. Kept here so the test file reads as
     * expectations rather than as string assembly.
     */
    public const string GENERATED_TAG_MESSAGE = '%s hand-writes `%s` on its class docblock. '
        .'That information belongs in _ide_helper_models.php, which `php artisan ide-helper:models --write-mixin` regenerates '
        ."from the schema — a hand-written copy has no generator behind it, outranks larastan's "
        .'reflection, and goes stale on the next migration. Delete the tag and run '
        .'`php artisan ide-helper:models --write-mixin`.';

    public const string WRONG_MIXIN_MESSAGE = '%s declares `@mixin %s` on its class docblock. '
        .'Only `@mixin IdeHelper%s` belongs there: any other mixin contributes no column '
        .'information while still typing $this, which makes magic attribute access look '
        .'statically valid when nothing has checked it.';

    protected function getRule(): Rule
    {
        return new ModelDocblockNoHandwrittenAnnotationsRule;
    }
}
