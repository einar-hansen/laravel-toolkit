<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail;

use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail\LocaleAwareMailable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;

final class StaffTypedSubjectMail extends Mailable
{
    use LocaleAwareMailable;

    public function __construct(public string $subjectLine)
    {
        $this->applyRecipientLocale();
    }

    public function envelope(): Envelope
    {
        // A subject typed by staff at send time is data, not copy.
        return new Envelope(subject: $this->subjectLine);
    }
}
