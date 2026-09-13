<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;

final class LocaleCallMail extends Mailable
{
    public function __construct(string $locale)
    {
        $this->locale($locale);
    }

    public function envelope(): Envelope
    {
        $subject = trans('emails.fixture.subject', [], $this->locale);

        return new Envelope(subject: $subject);
    }
}
