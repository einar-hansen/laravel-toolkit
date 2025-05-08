<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Concerns\Stubs;

use EinarHansen\Toolkit\Concerns\Memoizable;

final class TestMemoizable
{
    use Memoizable;

    /**
     * Public wrapper for the protected memoize method.
     */
    public function publicMemoize(string $key, callable $callback): mixed
    {
        return $this->memoize($key, $callback);
    }

    /**
     * Public wrapper for the protected forget method.
     */
    public function publicForget(string $key): void
    {
        $this->forget($key);
    }

    /**
     * Public wrapper for the protected forgetAll method.
     */
    public function publicForgetAll(): void
    {
        $this->forgetAll();
    }

    /**
     * Public wrapper for the protected hasMemoized method.
     */
    public function publicHasMemoized(string $key): bool
    {
        return $this->hasMemoized($key);
    }

    /**
     * Get access to the memoized array for testing.
     */
    public function getMemoized(): array
    {
        return $this->memoized;
    }
}
