<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property IdentifiableFixtureModel $resource
 */
final class AutoIncrementIdResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'location_id' => $this->resource->location_id,
            'ulid' => $this->resource->ulid,
            'slug' => $this->resource->slug,
        ];
    }

    /**
     * Not a payload, so the integer here is nobody's business but this
     * method's.
     *
     * @return array<string, int>
     */
    public function lookupCriteria(): array
    {
        return ['id' => $this->resource->id];
    }
}
