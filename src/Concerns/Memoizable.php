<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Concerns;

/**
 * Provides memoization capabilities to cache expensive operations.
 *
 * Usage with type constraint:
 * ```php
 * /**
 *  * @use Memoizable<\App\Models\User>
 *  *\/
 * class UserService
 * {
 *     use Memoizable;
 *
 *     public function getUser(int $id): User
 *     {
 *         return $this->memoize("user_{$id}", function() use ($id) {
 *             return User::find($id);
 *         });
 *     }
 * }
 * ```
 *
 * Usage with mixed types:
 * ```php
 * class GeneralService
 * {
 *     use Memoizable; // Uses default T = mixed
 * }
 * ```
 *
 * @template T = mixed
 */
trait Memoizable
{
    /**
     * @var array<string, T>
     */
    private array $memoized = [];

    /**
     * Memoize the result of a callback function.
     *
     * @param  string  $key  The cache key
     * @param  callable(): T  $callback  The function to memoize
     * @return T The memoized value
     */
    protected function memoize(string $key, callable $callback): mixed
    {
        if (! array_key_exists($key, $this->memoized)) {
            $this->memoized[$key] = $callback();
        }

        return $this->memoized[$key];
    }

    /**
     * Remove a specific memoized value.
     */
    protected function forget(string $key): void
    {
        unset($this->memoized[$key]);
    }

    /**
     * Clear all memoized values.
     */
    protected function forgetAll(): void
    {
        $this->memoized = [];
    }

    /**
     * Check if a key has been memoized.
     */
    protected function hasMemoized(string $key): bool
    {
        return array_key_exists($key, $this->memoized);
    }
}
