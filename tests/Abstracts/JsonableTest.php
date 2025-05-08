<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Abstracts;

use EinarHansen\Toolkit\Abstracts\Jsonable;
use EinarHansen\Toolkit\Tests\Abstracts\Stubs\TestJsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable as JsonableContract;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use JsonSerializable;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stringable;

#[CoversClass(Jsonable::class)]
final class JsonableTest extends TestCase
{
    private TestJsonable $jsonable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jsonable = new TestJsonable();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_implements_expected_interfaces(): void
    {
        $this->assertInstanceOf(Arrayable::class, $this->jsonable);
        $this->assertInstanceOf(JsonableContract::class, $this->jsonable);
        $this->assertInstanceOf(JsonSerializable::class, $this->jsonable);
        $this->assertInstanceOf(Responsable::class, $this->jsonable);
        $this->assertInstanceOf(Stringable::class, $this->jsonable);
    }

    #[Test]
    public function to_array_returns_expected_data(): void
    {
        $expected = ['name' => 'Test', 'value' => 123];
        $this->assertSame($expected, $this->jsonable->toArray());
    }

    #[Test]
    public function json_serialize_returns_same_as_to_array(): void
    {
        $this->assertSame($this->jsonable->toArray(), $this->jsonable->jsonSerialize());
    }

    #[Test]
    public function to_json_returns_json_string(): void
    {
        $expected = json_encode($this->jsonable->toArray());
        $this->assertSame($expected, $this->jsonable->toJson());
    }

    #[Test]
    public function to_string_returns_json_string(): void
    {
        $this->assertSame($this->jsonable->toJson(), (string) $this->jsonable);
    }

    #[Test]
    public function with_wrap_sets_wrapper_key(): void
    {
        $result = $this->jsonable->withWrap('items');

        $this->assertSame($this->jsonable, $result, 'Method should return $this for chaining');

        $mockedRequest = Mockery::mock(Request::class);
        $mockedResponse = Mockery::mock(JsonResponse::class);

        Response::shouldReceive('json')
            ->once()
            ->with(['items' => $this->jsonable->toArray()])
            ->andReturn($mockedResponse);

        $this->assertSame($mockedResponse, $this->jsonable->toResponse($mockedRequest));
    }

    #[Test]
    public function without_wrap_disables_wrapping(): void
    {
        $result = $this->jsonable->withoutWrap();

        $this->assertSame($this->jsonable, $result, 'Method should return $this for chaining');

        $mockedRequest = Mockery::mock(Request::class);
        $mockedResponse = Mockery::mock(JsonResponse::class);

        Response::shouldReceive('json')
            ->once()
            ->with($this->jsonable->toArray())
            ->andReturn($mockedResponse);

        $this->assertSame($mockedResponse, $this->jsonable->toResponse($mockedRequest));
    }

    #[Test]
    public function to_response_wraps_data_by_default(): void
    {
        $mockedRequest = Mockery::mock(Request::class);
        $mockedResponse = Mockery::mock(JsonResponse::class);

        Response::shouldReceive('json')
            ->once()
            ->with(['data' => $this->jsonable->toArray()])
            ->andReturn($mockedResponse);

        $this->assertSame($mockedResponse, $this->jsonable->toResponse($mockedRequest));
    }

    #[Test]
    public function to_meta_response_includes_metadata(): void
    {
        $meta = ['total' => 100, 'page' => 1];
        $status = 201;
        $headers = ['X-Custom' => 'Value'];

        $mockedRequest = Mockery::mock(Request::class);
        $mockedResponse = Mockery::mock(JsonResponse::class);

        Response::shouldReceive('json')
            ->once()
            ->with([
                'data' => $this->jsonable->toArray(),
                'meta' => $meta,
            ], $status, $headers)
            ->andReturn($mockedResponse);

        $actualResponse = $this->jsonable->toMetaResponse($mockedRequest, $meta, $status, $headers);

        $this->assertSame($mockedResponse, $actualResponse);
    }

    #[Test]
    public function to_meta_response_respects_wrapper_setting(): void
    {
        $this->jsonable->withoutWrap();
        $meta = ['total' => 100];

        $mockedRequest = Mockery::mock(Request::class);
        $mockedResponse = Mockery::mock(JsonResponse::class);

        Response::shouldReceive('json')
            ->once()
            ->with([
                'name' => 'Test',
                'value' => 123,
                'meta' => $meta,
            ], 200, [])
            ->andReturn($mockedResponse);

        $actualResponse = $this->jsonable->toMetaResponse($mockedRequest, $meta);

        $this->assertSame($mockedResponse, $actualResponse);
    }
}
