<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\Tests\Support\PHPStan\ResourceIdRuleTestCase;

class ResourceIdIsNotAutoIncrementRuleTest extends ResourceIdRuleTestCase
{
    public function test_integer_id_keys_in_a_payload_are_flagged_including_nullable_ones(): void
    {
        // The `['id' => …]` in lookupCriteria() is the same expression outside
        // toArray(); it must not appear here, which is what pins the rule to the
        // payload rather than to every array literal in the class.
        $this->analyse(
            [ResourceIdRuleTestCase::FIXTURES.'/AutoIncrementIdResource.php'],
            [
                [sprintf(ResourceIdRuleTestCase::EXPOSED_MESSAGE, 'id'), 23],
                [sprintf(ResourceIdRuleTestCase::EXPOSED_MESSAGE, 'location_id'), 24],
            ],
        );
    }

    public function test_the_same_keys_carrying_a_ulid_or_slug_and_non_resources_are_left_alone(): void
    {
        // PublicIdentifierResource proves the check is on the VALUE's type: the
        // keys are identical to the flagged ones above.
        $this->analyse(
            [
                ResourceIdRuleTestCase::FIXTURES.'/PublicIdentifierResource.php',
                ResourceIdRuleTestCase::FIXTURES.'/NotAResourceWithIntId.php',
            ],
            [],
        );
    }
}
