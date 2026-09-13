<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail;

use EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail\LocaleAwareMailable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;

final class HardcodedSubjectMail extends Mailable
{
    use LocaleAwareMailable;

    public function __construct(public string $gym, public bool $founding)
    {
        $this->applyRecipientLocale();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Oppstartsuken din er reservert',
        );
    }

    public function build(): static
    {
        return $this->subject("Velkommen, {$this->gym}");
    }

    public function viaVariable(): Envelope
    {
        $subject = $this->founding
            ? 'Grunnleggersøknad: '.$this->gym
            : sprintf('Venteliste: %s', $this->gym);

        return new Envelope(subject: $subject);
    }
}
