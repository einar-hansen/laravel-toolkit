<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\Tests\Support\PHPStan\HardcodedApiMessageRuleTestCase;

class NoHardcodedApiMessageRuleTest extends HardcodedApiMessageRuleTestCase
{
    public function test_only_the_hardcoded_string_literal_message_is_flagged(): void
    {
        // The fixture also covers __(), Lang::get(), a plain variable, each of the
        // 'OK'/'PONG'/'ACK' sentinels, an empty string, and a non-response
        // ->json() caller. analyse() asserts the EXACT error set for the file, so
        // any pass-case that wrongly fired would show up as an extra error here.
        $this->analyse(
            [HardcodedApiMessageRuleTestCase::FIXTURES.'/Controllers/MessageController.php'],
            [
                [
                    sprintf(HardcodedApiMessageRuleTestCase::HARDCODED_MESSAGE, 'Post deleted'),
                    13,
                ],
            ],
        );
    }

    public function test_a_file_outside_the_restricted_path_is_never_flagged(): void
    {
        $this->analyse(
            [HardcodedApiMessageRuleTestCase::FIXTURES.'/Other/OutsideControllersMessage.php'],
            [],
        );
    }
}
