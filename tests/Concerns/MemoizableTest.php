<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Concerns;

use EinarHansen\Toolkit\Concerns\Memoizable;
use EinarHansen\Toolkit\Tests\Concerns\Stubs\TestMemoizable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Memoizable::class)]
final class MemoizableTest extends TestCase
{
    private TestMemoizable $memoizable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->memoizable = new TestMemoizable();
    }

    #[Test]
    public function it_memoizes_values_correctly(): void
    {
        $counter = 0;

        $result1 = $this->memoizable->publicMemoize('test_key', function () use (&$counter): string {
            $counter++;

            return 'test_value';
        });

        $result2 = $this->memoizable->publicMemoize('test_key', function () use (&$counter): string {
            $counter++;

            return 'different_value';
        });

        $this->assertEquals('test_value', $result1);
        $this->assertEquals('test_value', $result2);
        $this->assertEquals(1, $counter, 'Callback should only be executed once');
    }

    #[Test]
    public function it_can_check_if_key_is_memoized(): void
    {
        $this->assertFalse($this->memoizable->publicHasMemoized('non_existent_key'));

        $this->memoizable->publicMemoize('test_key', fn (): string => 'test_value');

        $this->assertTrue($this->memoizable->publicHasMemoized('test_key'));
    }

    #[Test]
    public function it_can_forget_specific_values(): void
    {
        $this->memoizable->publicMemoize('key1', fn (): string => 'value1');
        $this->memoizable->publicMemoize('key2', fn (): string => 'value2');

        $this->assertTrue($this->memoizable->publicHasMemoized('key1'));
        $this->assertTrue($this->memoizable->publicHasMemoized('key2'));

        $this->memoizable->publicForget('key1');

        $this->assertFalse($this->memoizable->publicHasMemoized('key1'));
        $this->assertTrue($this->memoizable->publicHasMemoized('key2'));
    }

    #[Test]
    public function it_can_forget_all_values(): void
    {
        $this->memoizable->publicMemoize('key1', fn (): string => 'value1');
        $this->memoizable->publicMemoize('key2', fn (): string => 'value2');

        $this->assertTrue($this->memoizable->publicHasMemoized('key1'));
        $this->assertTrue($this->memoizable->publicHasMemoized('key2'));

        $this->memoizable->publicForgetAll();

        $this->assertFalse($this->memoizable->publicHasMemoized('key1'));
        $this->assertFalse($this->memoizable->publicHasMemoized('key2'));
        $this->assertEmpty($this->memoizable->getMemoized());
    }

    #[Test]
    public function it_returns_memoized_value_on_repeated_calls(): void
    {
        $expensiveOperationCounter = 0;

        $expensiveOperation = function () use (&$expensiveOperationCounter): string {
            $expensiveOperationCounter++;

            return 'Result of expensive operation';
        };

        // First call - should execute the callback
        $result1 = $this->memoizable->publicMemoize('expensive_operation', $expensiveOperation);
        $this->assertEquals(1, $expensiveOperationCounter);
        $this->assertEquals('Result of expensive operation', $result1);

        // Second call - should return the cached value without executing callback
        $result2 = $this->memoizable->publicMemoize('expensive_operation', $expensiveOperation);
        $this->assertEquals(1, $expensiveOperationCounter);
        $this->assertEquals('Result of expensive operation', $result2);

        // After forgetting, the callback should execute again
        $this->memoizable->publicForget('expensive_operation');
        $result3 = $this->memoizable->publicMemoize('expensive_operation', $expensiveOperation);
        $this->assertEquals(2, $expensiveOperationCounter);
        $this->assertEquals('Result of expensive operation', $result3);
    }
}
