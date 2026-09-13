<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\PreferSleepRule;
use Illuminate\Support\Sleep;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<PreferSleepRule> */
class PreferSleepRuleTest extends RuleTestCase
{
    public function test_native_sleeps_and_aliases_are_reported(): void
    {
        $sleep = 'Use '.Sleep::class.'::sleep() instead of sleep() so tests can fake delays with Sleep::fake().';
        $usleep = 'Use '.Sleep::class.'::usleep() instead of usleep() so tests can fake delays with Sleep::fake().';

        $this->analyse([__DIR__.'/../Fixtures/PHPStan/Sleep/NativeSleeps.php'], [
            [$sleep, 14],
            [$sleep, 15],
            [$sleep, 16],
            [$sleep, 17],
            [$usleep, 18],
            [$usleep, 19],
            [$usleep, 20],
            [$sleep, 21],
        ]);
    }

    public function test_laravel_sleep_and_unrelated_calls_are_allowed(): void
    {
        $this->analyse([__DIR__.'/../Fixtures/PHPStan/Sleep/Custom/AllowedSleeps.php'], []);
    }

    protected function getRule(): Rule
    {
        return new PreferSleepRule(self::createReflectionProvider());
    }
}
