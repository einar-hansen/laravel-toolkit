<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\PHPStan;

use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail\HardcodedSubjectMail;
use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail\NoLocaleMail;
use EinarHansen\Toolkit\Tests\Support\PHPStan\MailableLocaleRuleTestCase;

class MailableLocaleRuleTest extends MailableLocaleRuleTestCase
{
    public function test_trait_users_this_locale_callers_staff_typed_subjects_abstract_bases_and_non_mailables_all_pass(): void
    {
        $this->analyse([
            MailableLocaleRuleTestCase::FIXTURES.'/TraitMail.php',
            MailableLocaleRuleTestCase::FIXTURES.'/LocaleCallMail.php',
            MailableLocaleRuleTestCase::FIXTURES.'/StaffTypedSubjectMail.php',
            MailableLocaleRuleTestCase::FIXTURES.'/AbstractMail.php',
            MailableLocaleRuleTestCase::FIXTURES.'/NotAMailable.php',
        ], []);
    }

    public function test_a_mailable_that_never_picks_a_locale_is_flagged_once_at_the_class(): void
    {
        $this->analyse(
            [MailableLocaleRuleTestCase::FIXTURES.'/NoLocaleMail.php'],
            [[sprintf(MailableLocaleRuleTestCase::NO_LOCALE_MESSAGE, NoLocaleMail::class), 10]],
        );
    }

    public function test_literal_subjects_are_flagged_whether_passed_to_envelope_subject_or_assigned_to_a_local_first(): void
    {
        $class = HardcodedSubjectMail::class;

        $this->analyse(
            [MailableLocaleRuleTestCase::FIXTURES.'/HardcodedSubjectMail.php'],
            [
                [sprintf(MailableLocaleRuleTestCase::HARDCODED_SUBJECT_MESSAGE, 'Oppstartsuken din er reservert', $class), 23],
                [sprintf(MailableLocaleRuleTestCase::HARDCODED_SUBJECT_MESSAGE, 'Velkommen, ', $class), 29],
                [sprintf(MailableLocaleRuleTestCase::HARDCODED_SUBJECT_MESSAGE, 'Grunnleggersøknad: ', $class), 34],
            ],
        );
    }

    public function test_an_excluded_namespace_is_never_flagged(): void
    {
        $this->analyse([MailableLocaleRuleTestCase::FIXTURES.'/Excluded/ExcludedMail.php'], []);
    }
}
