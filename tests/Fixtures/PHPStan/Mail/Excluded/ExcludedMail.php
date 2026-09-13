<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail\Excluded;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;

final class ExcludedMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Skipped by namespace');
    }
}
