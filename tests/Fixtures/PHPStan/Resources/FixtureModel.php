<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

/**
 * Stand-in for the Eloquent model a resource wraps. Deliberately a plain
 * object: the rules only ask what the docblock declares and what the class
 * natively has, so pulling a real tenant model in would add a database and
 * an IDE-helper dependency for nothing.
 */
final class FixtureModel
{
    public string $name = '';

    public ?string $email = null;

    public function profile(): string
    {
        return '';
    }
}
