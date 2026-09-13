<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\Tests\Support\PHPStan\MigrationTimestampTzRuleTestCase;

class MigrationTimestampTzRuleTest extends MigrationTimestampTzRuleTestCase
{
    public function test_each_non_timezone_aware_column_call_is_flagged_with_its_tz_replacement(): void
    {
        // The last expectation is the Blueprint named `$blueprint` rather than
        // `$table` — it fires because the rule reads the receiver's type.
        $this->analyse(
            [MigrationTimestampTzRuleTestCase::FIXTURES.'/Migrations/NonTzColumns.php'],
            [
                [
                    sprintf(MigrationTimestampTzRuleTestCase::NON_TZ_MESSAGE, 'timestamp', 'timestampTz'),
                    16,
                ],
                [
                    sprintf(MigrationTimestampTzRuleTestCase::NON_TZ_MESSAGE, 'softDeletes', 'softDeletesTz'),
                    17,
                ],
                [
                    sprintf(MigrationTimestampTzRuleTestCase::NON_TZ_MESSAGE, 'timestamps', 'timestampsTz'),
                    18,
                ],
                [
                    sprintf(MigrationTimestampTzRuleTestCase::NON_TZ_MESSAGE, 'timestamp', 'timestampTz'),
                    29,
                ],
            ],
        );
    }

    public function test_the_tz_variants_and_a_non_blueprint_receiver_are_left_alone(): void
    {
        // analyse() asserts the EXACT error set, so a rule that keyed on the method
        // name alone would surface here as extra errors from NotABlueprint.
        $this->analyse(
            [
                MigrationTimestampTzRuleTestCase::FIXTURES.'/Migrations/TzColumns.php',
                MigrationTimestampTzRuleTestCase::FIXTURES.'/Migrations/NotABlueprint.php',
            ],
            [],
        );
    }

    public function test_a_file_outside_the_restricted_path_is_never_flagged(): void
    {
        $this->analyse(
            [MigrationTimestampTzRuleTestCase::FIXTURES.'/Other/OutsideMigrationsTimestamps.php'],
            [],
        );
    }
}
