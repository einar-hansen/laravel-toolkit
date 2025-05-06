<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Configurables;

use EinarHansen\Toolkit\Contracts\Configurable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

final readonly class AutomaticallyEagerLoadRelationships implements Configurable
{
    public function __construct(
        private string $modelClass = Model::class
    ) {}

    /**
     * Whether the configurable is enabled or not.
     */
    public function enabled(): bool
    {
        return Config::boolean('toolkit.eloquent.eager_load_relationships', false);
    }

    /**
     * Run the configurable.
     */
    public function configure(): void
    {
        if (! method_exists($this->modelClass, 'automaticallyEagerLoadRelationships')) {
            return;
        }

        Model::automaticallyEagerLoadRelationships();
    }
}
