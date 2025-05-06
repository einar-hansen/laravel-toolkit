<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Mixins;

use Closure;
use Generator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use JsonMachine\Exception\PathNotFoundException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Throwable;

final class ResponseMixin
{
    public function lazy(): Closure
    {
        return fn (?string $key = null): LazyCollection => new LazyCollection(
            function () use ($key): Generator {
                $options = [
                    'decoder' => new ExtJsonDecoder(true),
                    'pointer' => $key ?? '',
                ];

                $resource = null;

                try {
                    /** @var Response $this */
                    // @phpstan-ignore-next-line varTag.nativeType
                    $stream = $this->resource(); // Get the stream resource

                    // Check if it's actually a stream resource *before* trying to rewind
                    if (! is_resource($stream) || get_resource_type($stream) !== 'stream') {
                        return;
                    }

                    $resource = $stream;
                    rewind($resource);

                } catch (Throwable) {
                    // If $this->resource() or rewind() fails, we also yield nothing.
                    return;
                }

                try {
                    foreach (Items::fromStream($resource, $options) as $arrayKey => $item) {
                        yield $arrayKey => $item;
                    }
                } catch (PathNotFoundException) {
                    // Path not found is okay, the generator simply finishes yielding nothing more.
                }
            }
        );
    }
}
