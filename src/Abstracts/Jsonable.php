<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Abstracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable as JsonableContract;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use JsonException;
use JsonSerializable;
use Override;
use Stringable;

/**
 * Abstract class that provides JSON conversion and response functionality.
 *
 * This class implements several Laravel interfaces to make objects easily
 * convertible to arrays, JSON, and HTTP responses.
 *
 * @template T of array<string, mixed>
 */
abstract class Jsonable implements Arrayable, JsonableContract, JsonSerializable, Responsable, Stringable
{
    /**
     * The "data" wrapper that should be applied.
     */
    protected ?string $wrap = 'data';

    /**
     * Convert the object to its string representation.
     *
     * @return string JSON representation of the object
     */
    #[Override]
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Get the instance as an array.
     *
     * @return T The object represented as an array
     */
    #[Override]
    abstract public function toArray(): array;

    /**
     * Set the wrapper key to use for the response.
     *
     * @param  string|null  $key  The wrapper key, or null to disable wrapping
     * @return $this
     */
    public function withWrap(?string $key): static
    {
        $this->wrap = $key;

        return $this;
    }

    /**
     * Disable wrapping of the response.
     *
     * @return $this
     */
    public function withoutWrap(): static
    {
        $this->wrap = null;

        return $this;
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return T Data to encode as JSON
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options  JSON encoding options
     * @return string JSON representation of the object
     *
     * @throws JsonException When encoding fails
     */
    #[Override]
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request  The request instance
     */
    #[Override]
    public function toResponse($request): JsonResponse
    {
        return Response::json(
            $this->wrap ? [$this->wrap => $this->jsonSerialize()] : $this->jsonSerialize()
        );
    }

    /**
     * Create a custom response with additional metadata.
     *
     * @param  Request  $request  The request instance
     * @param  array<string, mixed>  $meta  Additional metadata to include
     * @param  int  $status  HTTP status code
     * @param  array<string, string>  $headers  Custom headers
     */
    public function toMetaResponse($request, array $meta = [], int $status = 200, array $headers = []): JsonResponse
    {
        return Response::json(
            array_merge(
                $this->wrap ? [$this->wrap => $this->jsonSerialize()] : $this->jsonSerialize(),
                ['meta' => $meta]
            ),
            $status,
            $headers
        );
    }
}
