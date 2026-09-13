<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\Tests\Support\PHPStan\ModelMixinRuleTestCase;

class ModelMixinRequiredRuleTest extends ModelMixinRuleTestCase
{
    public function test_a_model_with_no_docblock_at_all_is_flagged(): void
    {
        $missing = static fn (string $class, string $mixin): string => 'EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models\\'.$class
            .' is an Eloquent model without `@mixin '.$mixin.'` on its class docblock. The configured IDE Helper policy '
            .'requires this link to the generated model annotations. '
            .'Add the line, then run `php artisan ide-helper:models --write-mixin` to generate the helper class '
            .'it points at.';

        $this->analyse(
            [ModelMixinRuleTestCase::FIXTURES.'/UnannotatedModel.php'],
            [[$missing('UnannotatedModel', 'IdeHelperUnannotatedModel'), 10]],
        );
    }

    public function test_a_docblock_without_the_mixin_is_flagged(): void
    {
        $missing = static fn (string $class, string $mixin): string => 'EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models\\'.$class
            .' is an Eloquent model without `@mixin '.$mixin.'` on its class docblock. The configured IDE Helper policy '
            .'requires this link to the generated model annotations. '
            .'Add the line, then run `php artisan ide-helper:models --write-mixin` to generate the helper class '
            .'it points at.';

        $this->analyse(
            [ModelMixinRuleTestCase::FIXTURES.'/DescribedModel.php'],
            [[$missing('DescribedModel', 'IdeHelperDescribedModel'), 14]],
        );
    }

    public function test_a_mixin_naming_the_wrong_helper_is_flagged(): void
    {
        $missing = static fn (string $class, string $mixin): string => 'EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models\\'.$class
            .' is an Eloquent model without `@mixin '.$mixin.'` on its class docblock. The configured IDE Helper policy '
            .'requires this link to the generated model annotations. '
            .'Add the line, then run `php artisan ide-helper:models --write-mixin` to generate the helper class '
            .'it points at.';

        // The whole point of matching on the expected name rather than on
        // `@mixin IdeHelper*`: this fixture HAS a mixin, and it types the model
        // against another table's columns.
        $this->analyse(
            [ModelMixinRuleTestCase::FIXTURES.'/WrongMixinModel.php'],
            [[$missing('WrongMixinModel', 'IdeHelperWrongMixinModel'), 16]],
        );
    }

    public function test_a_correctly_annotated_model_and_a_non_model_are_left_alone(): void
    {
        // analyse() asserts the EXACT error set, so the empty expectation is the
        // assertion.
        $this->analyse(
            [
                ModelMixinRuleTestCase::FIXTURES.'/AnnotatedModel.php',
                ModelMixinRuleTestCase::FIXTURES.'/NotAModel.php',
            ],
            [],
        );
    }
}
