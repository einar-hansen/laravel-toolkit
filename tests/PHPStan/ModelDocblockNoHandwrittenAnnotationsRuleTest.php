<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Models\HandwrittenAnnotations;
use EinarHansen\Toolkit\Tests\Support\PHPStan\ModelDocblockRuleTestCase;

class ModelDocblockNoHandwrittenAnnotationsRuleTest extends ModelDocblockRuleTestCase
{
    public function test_every_generated_annotation_tag_on_a_model_docblock_is_reported(): void
    {
        // All five anchor to the `class` keyword line, not to the tag — that is
        // where InClassNode puts an error.
        $model = HandwrittenAnnotations::class;

        $this->analyse(
            [ModelDocblockRuleTestCase::FIXTURES.'/HandwrittenAnnotations.php'],
            [
                [sprintf(ModelDocblockRuleTestCase::GENERATED_TAG_MESSAGE, $model, '@property'), 22],
                [sprintf(ModelDocblockRuleTestCase::GENERATED_TAG_MESSAGE, $model, '@property-read'), 22],
                [sprintf(ModelDocblockRuleTestCase::GENERATED_TAG_MESSAGE, $model, '@property-write'), 22],
                [sprintf(ModelDocblockRuleTestCase::GENERATED_TAG_MESSAGE, $model, '@method static'), 22],
                [sprintf(ModelDocblockRuleTestCase::WRONG_MIXIN_MESSAGE, $model, 'Model', 'HandwrittenAnnotations'), 22],
            ],
        );
    }

    public function test_prose_the_correct_mixin_no_docblock_at_all_and_non_models_are_left_alone(): void
    {
        // analyse() asserts the EXACT error set. AnnotatedNonModel carries every
        // rejected tag and must still produce nothing, which is what proves the
        // rule is gated on Model rather than on the tags.
        $this->analyse(
            [
                ModelDocblockRuleTestCase::FIXTURES.'/CleanDocblockModel.php',
                ModelDocblockRuleTestCase::FIXTURES.'/UndocumentedModel.php',
                ModelDocblockRuleTestCase::FIXTURES.'/AnnotatedNonModel.php',
            ],
            [],
        );
    }
}
