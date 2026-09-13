<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Migrations;

/**
 * Same method names, different receiver. Nothing here creates a column, so the
 * rule must stay quiet even though the file sits inside the restricted path.
 */
final class NotABlueprint
{
    public function timestamp(string $label): string
    {
        return $label;
    }

    public function timestamps(): int
    {
        return 0;
    }

    public function softDeletes(): bool
    {
        return false;
    }

    public function use(): void
    {
        $this->timestamp('published_at');
        $this->timestamps();
        $this->softDeletes();
    }
}
