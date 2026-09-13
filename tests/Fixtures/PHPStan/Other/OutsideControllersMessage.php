<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Other;

final class OutsideControllersMessage
{
    public function hardcoded(): mixed
    {
        return response()->json(['message' => 'Post deleted'], 200);
    }
}
