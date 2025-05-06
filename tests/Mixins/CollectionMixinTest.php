<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Mixins;

use EinarHansen\Toolkit\Mixins\CollectionMixin;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(CollectionMixin::class)]
final class CollectionMixinTest extends TestCase
{
    private CollectionMixin $mixin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mixin = new CollectionMixin;
    }

    public static function wrapListProvider(): array
    {
        $object = new stdClass;
        $object->property = 'value';

        $associativeArray = ['a' => 1, 'b' => 2];
        $listArray = [1, 2, 3];
        $mixedArray = [0 => 'zero', 'one' => 1, 2 => 'two']; // Arr::isAssoc will see this as assoc

        $associativeCollection = new Collection($associativeArray);
        $listCollection = new Collection($listArray);
        $mixedCollection = new Collection($mixedArray);

        return [
            'null input' => [
                'input' => null,
                'expectedArray' => [],
            ],
            'empty array input' => [
                'input' => [],
                'expectedArray' => [],
            ],
            'string input' => [
                'input' => 'hello',
                'expectedArray' => ['hello'],
            ],
            'integer input' => [
                'input' => 123,
                'expectedArray' => [123],
            ],
            'boolean true input' => [
                'input' => true,
                'expectedArray' => [true],
            ],
            'list array input' => [
                'input' => $listArray,
                'expectedArray' => $listArray, // Should remain a list
            ],
            'associative array input' => [
                'input' => $associativeArray,
                'expectedArray' => [$associativeArray], // Should be wrapped
            ],
            'mixed key array input' => [
                'input' => $mixedArray,
                'expectedArray' => [$mixedArray], // Should be wrapped (isAssoc is true)
            ],
            'list collection input' => [
                'input' => $listCollection,
                'expectedArray' => $listArray, // Should remain a list
            ],
            'associative collection input' => [
                'input' => $associativeCollection,
                'expectedArray' => [$associativeArray], // Should be wrapped
            ],
            'mixed key collection input' => [
                'input' => $mixedCollection,
                'expectedArray' => [$mixedArray], // Should be wrapped
            ],
            'object input' => [
                'input' => $object,
                'expectedArray' => [$object], // Objects get wrapped in a single-element list
            ],
            'empty collection input' => [
                'input' => new Collection,
                'expectedArray' => [],
            ],
        ];
    }

    #[DataProvider('wrapListProvider')]
    #[Test]
    public function it_wraps_values_into_list_collection(mixed $input, array $expectedArray): void
    {
        $closure = $this->mixin->wrapList();

        $result = $closure($input);

        // Ensure the output is always a Collection instance
        $this->assertInstanceOf(Collection::class, $result);

        // Compare the underlying array structure
        $this->assertEquals($expectedArray, $result->all());
    }
}
