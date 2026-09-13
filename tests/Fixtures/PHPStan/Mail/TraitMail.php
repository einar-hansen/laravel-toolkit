<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail;

use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail\LocaleAwareMailable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;

final class TraitMail extends Mailable
{
    use LocaleAwareMailable;

    public function __construct(public string $name)
    {
        $this->applyRecipientLocale();
    }

    public function envelope(): Envelope
    {
        // Punctuation-only literal glue around a translated subject is fine.
        return new Envelope(subject: __('emails.fixture.subject', ['name' => $this->name], $this->locale).' – '.$this->name);
    }
}
