<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Support\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\MailableLocaleRule;
use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail\LocaleAwareMailable;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<MailableLocaleRule>
 */
abstract class MailableLocaleRuleTestCase extends RuleTestCase
{
    public const string NO_LOCALE_MESSAGE = 'Mailable %s never picks a locale. Call $this->locale() or use a configured locale-aware trait so the mail renders in the recipient\'s language.';

    public const string HARDCODED_SUBJECT_MESSAGE = 'Hardcoded subject "%s" in %s. Use __(\'emails.<mail>.subject\', [], $this->locale) so the subject follows the recipient\'s locale.';

    public const string FIXTURES = __DIR__.'/../../Fixtures/PHPStan/Mail';

    protected function getRule(): Rule
    {
        return new MailableLocaleRule(
            ['EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail\Excluded\\'],
            [LocaleAwareMailable::class],
        );
    }
}
