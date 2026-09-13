<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Support\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\RawSqlNoInterpolationRule;
use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Sql\SqlIdentifier;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RawSqlNoInterpolationRule>
 */
abstract class RawSqlRuleTestCase extends RuleTestCase
{
    public const string FIXTURES = __DIR__.'/../../Fixtures/PHPStan/Sql';

    /**
     * The rule's message, with the method name and the culprit's type left to
     * be interpolated. Kept here so the test file reads as expectations.
     */
    public const string MESSAGE = '%s() builds its SQL from a value that is not provably literal (%s). '
        .'Pass the value as a binding — %1$s(\'... = ?\', [$value]). If it is a SQL '
        .'fragment rather than a value, annotate whatever produces it '
        .'`@return literal-string`; if it is an identifier, which cannot be a '
        .'placeholder, use a validated identifier helper registered in safeCalls.';

    public static function message(string $method, string $type = 'string'): string
    {
        return sprintf(self::MESSAGE, $method, $type);
    }

    protected function getRule(): Rule
    {
        return new RawSqlNoInterpolationRule(
            [SqlIdentifier::class.'::quote'],
            ['Fixtures/PHPStan/Sql/Excluded'],
        );
    }
}
