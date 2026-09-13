<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property IdentifiableFixtureModel $resource
 */
final class PublicIdentifierResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->ulid,
            'location_id' => $this->resource->slug,
            'slug' => $this->resource->slug,
        ];
    }
}
