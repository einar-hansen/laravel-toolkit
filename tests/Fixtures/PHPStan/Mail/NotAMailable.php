<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail;

use Illuminate\Mail\Mailables\Envelope;

final class NotAMailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Not a Mailable');
    }
}
