<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Mail;

use Illuminate\Mail\Mailable;

final class CustomLocaleMethodMail extends Mailable
{
    public function __construct()
    {
        $this->chooseRecipientLocale();
    }

    private function chooseRecipientLocale(): void {}
}
