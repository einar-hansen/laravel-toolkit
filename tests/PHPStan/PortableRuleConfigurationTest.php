<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\PHPStan\Rules\LiteralStringArgumentRule;
use EinarHansen\Toolkit\PHPStan\Rules\MailableLocaleRule;
use EinarHansen\Toolkit\PHPStan\Rules\RawSqlNoInterpolationRule;
use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail\CustomLocaleMethodMail;
use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail\TraitMail;
use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Sql\NamedAndCallableSql;
use EinarHansen\Toolkit\Tests\Support\PHPStan\JsonResourceRuleTestCase;
use EinarHansen\Toolkit\Tests\Support\PHPStan\LiteralStringRuleTestCase;
use EinarHansen\Toolkit\Tests\Support\PHPStan\MailableLocaleRuleTestCase;
use EinarHansen\Toolkit\Tests\Support\PHPStan\RawSqlRuleTestCase;

class PortableRuleConfigurationTest extends JsonResourceRuleTestCase
{
    public function test_named_sql_arguments_are_checked_and_callable_references_do_not_crash(): void
    {
        $this->rule = new RawSqlNoInterpolationRule;
        $this->analyse([__DIR__.'/../Fixtures/PHPStan/Sql/NamedAndCallableSql.php'], [
            [RawSqlRuleTestCase::message('statement'), 14],
        ]);
    }

    public function test_named_literal_arguments_are_checked_and_callable_references_do_not_crash(): void
    {
        $this->rule = new LiteralStringArgumentRule(['EinarHansen\\Toolkit\\Tests\\Fixtures\\']);
        $this->analyse([__DIR__.'/../Fixtures/PHPStan/Sql/NamedAndCallableSql.php'], [
            [sprintf(LiteralStringRuleTestCase::ARGUMENT_MESSAGE, 'column', NamedAndCallableSql::class, 'column', 'string'), 24],
        ]);
    }

    public function test_an_unregistered_locale_trait_is_not_implicitly_trusted(): void
    {
        $this->rule = new MailableLocaleRule;
        $this->analyse([__DIR__.'/../Fixtures/PHPStan/Mail/TraitMail.php'], [
            [sprintf(MailableLocaleRuleTestCase::NO_LOCALE_MESSAGE, TraitMail::class), 11],
        ]);
    }

    public function test_a_locale_method_requires_explicit_configuration(): void
    {
        $this->rule = new MailableLocaleRule;
        $this->analyse([__DIR__.'/../Fixtures/PHPStan/Mail/CustomLocaleMethodMail.php'], [
            [sprintf(MailableLocaleRuleTestCase::NO_LOCALE_MESSAGE, CustomLocaleMethodMail::class), 9],
        ]);
    }

    public function test_a_configured_locale_method_is_accepted(): void
    {
        $this->rule = new MailableLocaleRule(localeMethods: ['chooseRecipientLocale']);
        $this->analyse([__DIR__.'/../Fixtures/PHPStan/Mail/CustomLocaleMethodMail.php'], []);
    }
}
