<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Mixins;

use EinarHansen\Toolkit\Mixins\ResponseMixin;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseMixin::class)]
final class ResponseMixinTest extends TestCase
{
    private ResponseMixin $mixin;

    private MockObject&Response $mockResponse;

    /** @var resource|null */
    private $stream; // Property to hold the stream for cleanup

    #[Before]
    public function setUpTest(): void
    {
        $this->mixin = new ResponseMixin;
        // Create a mock Response object
        $this->mockResponse = $this->createMock(Response::class);
    }

    #[After]
    public function tearDownTest(): void
    {
        // Ensure the stream is closed after each test
        if (is_resource($this->stream)) {
            fclose($this->stream);
            $this->stream = null;
        }
    }

    /**
     * Helper to create a stream from a JSON string.
     *
     * @return resource
     */
    private function createJsonStream(string $json)
    {
        // Close previous stream if any exists
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        $this->stream = fopen('php://memory', 'r+');
        fwrite($this->stream, $json);
        rewind($this->stream); // Go back to the start for reading

        return $this->stream;
    }

    #[DataProvider('lazyParsingProvider')]
    #[Test]
    public function it_lazily_parses_json_response(?string $json, ?string $key, array $expectedArray): void
    {
        // Handle null JSON input gracefully (results in empty collection)
        if ($json === null) {
            $this->mockResponse->method('resource')->willReturn(null); // Or maybe an empty stream? Test behavior
            // Let's assume an empty stream for null JSON for robustness
            $this->stream = $this->createJsonStream('');
            $this->mockResponse->method('resource')->willReturn($this->stream);

        } else {
            // Create the stream and configure the mock response
            $this->stream = $this->createJsonStream($json);
            $this->mockResponse->method('resource')->willReturn($this->stream);
        }

        // Get the closure from the mixin
        $closure = $this->mixin->lazy();

        // Bind the closure to the mock object and execute it
        // Closure::call() is available from PHP 7.0+
        $lazyCollection = $closure->call($this->mockResponse, $key);

        // Assert the result is a LazyCollection
        $this->assertInstanceOf(LazyCollection::class, $lazyCollection);

        // Assert the collected items match the expected array
        // ->all() triggers the iteration
        $this->assertEquals($expectedArray, $lazyCollection->all());

        // Optional: Verify rewind was called on the stream (JsonMachine should do this)
        // Although JsonMachine handles it, verifying the stream state can be useful.
        // If the stream wasn't empty, check it was read.
        // This is tricky to assert directly without more complex stream mocking.
        // Relying on the output ($lazyCollection->all()) is usually sufficient.
    }

    public static function lazyParsingProvider(): array
    {
        $jsonArray = '[{"id": 1, "name": "Alice"}, {"id": 2, "name": "Bob"}]';
        $expectedArray = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];

        $jsonObject = '{"config": {"url": "http://example.com", "port": 80}, "status": "ok"}';
        $expectedObjectRoot = [
            'config' => ['url' => 'http://example.com', 'port' => 80],
            'status' => 'ok',
        ];
        $expectedObjectConfig = [
            'url' => 'http://example.com',
            'port' => 80,
        ];

        $jsonNested = '{"data": {"items": [{"value": "A"}, {"value": "B"}]}, "meta": {"count": 2}}';
        $expectedNestedItems = [
            ['value' => 'A'],
            ['value' => 'B'],
        ];
        $expectedNestedData = [ // When pointing to 'data'
            'items' => [['value' => 'A'], ['value' => 'B']],
        ];

        return [
            'root array, null key' => [
                'json' => $jsonArray,
                'key' => null,
                'expectedArray' => $expectedArray,
            ],
            'root array, empty string key' => [
                'json' => $jsonArray,
                'key' => '',
                'expectedArray' => $expectedArray,
            ],
            'root object, null key' => [
                'json' => $jsonObject,
                'key' => null,
                'expectedArray' => $expectedObjectRoot,
            ],
            'root object, pointer to sub-object' => [
                'json' => $jsonObject,
                'key' => '/config', // JsonMachine pointer syntax
                'expectedArray' => $expectedObjectConfig,
            ],
            'nested array, pointer' => [
                'json' => $jsonNested,
                'key' => '/data/items',
                'expectedArray' => $expectedNestedItems,
            ],
            'nested object, pointer' => [ // Pointing to the object containing the array
                'json' => $jsonNested,
                'key' => '/data',
                'expectedArray' => $expectedNestedData,
            ],
            'empty json array' => [
                'json' => '[]',
                'key' => null,
                'expectedArray' => [],
            ],
            'empty json object' => [
                'json' => '{}',
                'key' => null,
                'expectedArray' => [],
            ],
            'key not found' => [
                'json' => $jsonArray,
                'key' => '/missing',
                'expectedArray' => [],
            ],
            'null json input' => [ // Test how null JSON is handled
                'json' => null,
                'key' => null,
                'expectedArray' => [],
            ],
            'deep key' => [
                'json' => '{"a": {"b": {"c": [1, 2]}}}',
                'key' => '/a/b/c',
                'expectedArray' => [1, 2],
            ],
            'key pointing to scalar' => [ // Although LazyCollection is usually for iterables
                'json' => '{"status": "ok", "code": 200}',
                'key' => '/status',
                'expectedArray' => ['status' => 'ok'], // Items yields the single value
            ],
            'key pointing to numeric scalar' => [
                'json' => '{"status": "ok", "code": 200}',
                'key' => '/code',
                'expectedArray' => ['code' => 200], // Items yields the single value
            ],
        ];
    }
}
