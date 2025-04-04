<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Mixins;

use Generator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use JsonMachine\Exception\PathNotFoundException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Throwable;

class ResponseMixin
{
    public function lazy()
    {
        return fn (?string $key = null): LazyCollection => new LazyCollection(function () use ($key): Generator {
            $options = [
                'decoder' => new ExtJsonDecoder(true),
                'pointer' => $key ?? '',
            ];

            /** @var Response $this */
            try {
                rewind($resource = $this->resource());
            } catch (Throwable) {
            }
            if (! is_resource($resource)) {
                return null;
            }

            try {
                foreach (Items::fromStream($resource, $options) as $arrayKey => $item) {
                    yield $arrayKey => $item;
                }
            } catch (PathNotFoundException) {
            }
        });
    }
}
