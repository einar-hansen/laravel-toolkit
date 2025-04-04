<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Mixins;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CollectionMixin
{
    /**
     * Wrap the given value in a list collection if applicable.
     */
    public function wrapList()
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
