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
}
