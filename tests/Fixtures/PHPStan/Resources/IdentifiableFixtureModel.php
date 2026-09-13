<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

/**
 * Stand-in for a model that carries both an auto-increment primary key and the
 * public identifiers that should be exposed in its place.
 */
final class IdentifiableFixtureModel
{
    public int $id = 0;

    public ?int $location_id = null;

    public string $ulid = '';

    public string $slug = '';
}
