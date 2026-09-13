<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Controllers;

use Illuminate\Support\Facades\Lang;

final class MessageController
{
    public function hardcoded(): mixed
    {
        return response()->json(['message' => 'Post deleted'], 200);
    }

    public function translated(): mixed
    {
        return response()->json(['message' => __('messages.feed.post_deleted')], 200);
    }

    public function langGet(): mixed
    {
        return response()->json(['message' => Lang::get('messages.feed.post_deleted')], 200);
    }

    public function variable(string $text): mixed
    {
        return response()->json(['message' => $text], 200);
    }

    public function sentinelOk(): mixed
    {
        return response()->json(['message' => 'OK'], 200);
    }

    public function sentinelPong(): mixed
    {
        return response()->json(['message' => 'PONG'], 200);
    }

    public function sentinelAck(): mixed
    {
        return response()->json(['message' => 'ACK'], 200);
    }

    public function emptyMessage(): mixed
    {
        return response()->json(['message' => ''], 200);
    }

    public function nonResponseJsonCaller(mixed $httpClientResponse): mixed
    {
        return $httpClientResponse->json(['message' => 'External payload']);
    }
}
