<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;

final class NoLocaleMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: __('emails.fixture.subject'));
    }
}
