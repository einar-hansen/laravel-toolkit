<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property FixtureModel $resource
 */
final class MagicAccessResource extends JsonResource
{
    private string $cachedLabel = '';

    /** @return array<string, mixed> */
    #[Override]
    public function toArray(Request $request): array
    {
        $column = 'name';

        return [
            'throughResource' => $this->resource->name,
            'magicProperty' => $this->name,
            'nativeProperty' => $this->with,
            'ownProperty' => $this->cachedLabel,
            'dynamicProperty' => $this->{$column},
            'throughResourceMethod' => $this->resource->profile(),
            'nativeMethod' => $this->whenLoaded('profile'),
            'ownMethod' => $this->label(),
            'magicMethod' => $this->profile(),
            'notOnThis' => $request->getRequestUri(),
        ];
    }

    private function label(): string
    {
        return $this->cachedLabel;
    }
}
