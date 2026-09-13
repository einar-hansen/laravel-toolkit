<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\JsonResourceAnnotationRule;
use EinarHansen\Toolkit\PHPStan\Rules\JsonResourceMagicMethodRule;
use EinarHansen\Toolkit\PHPStan\Rules\JsonResourceMagicPropertyRule;
use EinarHansen\Toolkit\Tests\Support\PHPStan\JsonResourceRuleTestCase;

class JsonResourceRulesTest extends JsonResourceRuleTestCase
{
    public function test_a_resource_with_no_property_resource_tag_is_flagged(): void
    {
        $missing = static fn (string $class): string => 'EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources\\'.$class
            .' extends JsonResource without a `@property <Model> $resource` docblock, so every '
            .'$this->resource->... read in it is unchecked. Add the tag naming the model it wraps — '
            .'`@mixin` does not count, it types $this, not $resource.';

        $this->rule = new JsonResourceAnnotationRule;

        $this->analyse(
            [JsonResourceRuleTestCase::FIXTURES.'/UnannotatedResource.php'],
            [[$missing('UnannotatedResource'), 11]],
        );
    }

    public function test_mixin_does_not_stand_in_for_the_tag(): void
    {
        $missing = static fn (string $class): string => 'EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources\\'.$class
            .' extends JsonResource without a `@property <Model> $resource` docblock, so every '
            .'$this->resource->... read in it is unchecked. Add the tag naming the model it wraps — '
            .'`@mixin` does not count, it types $this, not $resource.';

        $this->rule = new JsonResourceAnnotationRule;

        $this->analyse(
            [JsonResourceRuleTestCase::FIXTURES.'/MixinOnlyResource.php'],
            [[$missing('MixinOnlyResource'), 17]],
        );
    }

    public function test_property_mixed_resource_is_flagged_as_typing_nothing(): void
    {
        $this->rule = new JsonResourceAnnotationRule;

        $this->analyse(
            [JsonResourceRuleTestCase::FIXTURES.'/MixedAnnotatedResource.php'],
            [[
                'EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources\MixedAnnotatedResource declares `@property mixed '
                    .'$resource`, which types nothing. Name the model (or the array shape / DTO) the '
                    .'resource actually wraps.',
                14,
            ]],
        );
    }

    public function test_the_exempt_shapes_are_left_alone(): void
    {
        // analyse() asserts the EXACT error set, so the empty expectation is the
        // assertion: a tagged resource, a subclass inheriting that tag, an abstract
        // base that does not know its model yet, a ResourceCollection (where the
        // typed thing is $collection), and a class that is not a resource at all.
        $this->rule = new JsonResourceAnnotationRule;

        $this->analyse(
            [
                JsonResourceRuleTestCase::FIXTURES.'/AnnotatedResource.php',
                JsonResourceRuleTestCase::FIXTURES.'/InheritsAnnotationResource.php',
                JsonResourceRuleTestCase::FIXTURES.'/AbstractBaseResource.php',
                JsonResourceRuleTestCase::FIXTURES.'/FixtureResourceCollection.php',
                JsonResourceRuleTestCase::FIXTURES.'/NotAResourceAtAll.php',
            ],
            [],
        );
    }

    public function test_only_reads_that_must_go_through_get_are_flagged(): void
    {
        // Everything else in the fixture's toArray() is a pass case asserted by its
        // absence: the $this->resource-> read, a native JsonResource property, the
        // class's own private property, a dynamic $this->{$column}, and a read off
        // something that is not $this.
        $this->rule = new JsonResourceMagicPropertyRule;

        $this->analyse(
            [
                JsonResourceRuleTestCase::FIXTURES.'/MagicAccessResource.php',
                JsonResourceRuleTestCase::FIXTURES.'/NotAResourceEither.php',
            ],
            [[
                '$this->name reads through JsonResource::__get() and is not checked against the '
                    .'wrapped model. Use $this->resource->name.',
                26,
            ]],
        );
    }

    public function test_only_calls_that_must_go_through_call_are_flagged(): void
    {
        $this->rule = new JsonResourceMagicMethodRule;

        $this->analyse(
            [
                JsonResourceRuleTestCase::FIXTURES.'/MagicAccessResource.php',
                JsonResourceRuleTestCase::FIXTURES.'/NotAResourceEither.php',
            ],
            [[
                '$this->profile() is forwarded by JsonResource::__call() and is not checked against '
                    .'the wrapped model. Use $this->resource->profile().',
                33,
            ]],
        );
    }
}
