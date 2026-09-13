<?php

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail;

trait LocaleAwareMailable
{
    public function applyRecipientLocale(): void
    {
        $this->locale('en');
    }
}
