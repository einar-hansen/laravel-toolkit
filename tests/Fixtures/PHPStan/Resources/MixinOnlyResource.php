<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * `@mixin` resolves `$this->name` for the IDE without typing `$resource` — the
 * exact combination this rule exists to reject.
 *
 * @mixin FixtureModel
 */
final class MixinOnlyResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[Override]
    public function toArray(Request $request): array
    {
        return [];
    }
}
