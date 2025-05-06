<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Mixins;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class CollectionMixin
{
    /**
     * Wrap the given value in a list collection if applicable.
     */
    public function wrapList(): Closure
    {
        return function ($value): Collection {
            $collection = Collection::wrap($value);
            if (Arr::isAssoc($collection->all())) {
                return new Collection([$collection->all()]);
            }

            return $collection;
        };
    }
}
