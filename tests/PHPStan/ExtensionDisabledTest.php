<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use Override;
use PHPStan\Testing\PHPStanTestCase;

class ExtensionDisabledTest extends PHPStanTestCase
{
    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__.'/../../extension.neon'];
    }

    public function test_rules_are_opt_in(): void
    {
        $rules = self::getContainer()->getServicesByTag('phpstan.rules.rule');
        $toolkitRules = array_filter($rules, fn ($rule): bool => str_starts_with($rule::class, 'EinarHansen\\Toolkit\\PHPStan\\'));

        $this->assertCount(0, $toolkitRules);
    }
}
