---
name: toolkit-data
description: Use Laravel Toolkit Arr and Collection mixins, StringHelper conversion predicates, and Memoizable for typed extraction and instance-local caching.
---

# Toolkit data helpers

The Toolkit provider registers these macros unless disabled in `toolkit.mixins`. Import `Illuminate\Support\Arr` and `Illuminate\Support\Collection`; do not construct the mixin classes for normal application usage. Helpers coerce data; validate untrusted input before relying on domain constraints.

## Arr extraction and casting

All keyed casts use Laravel dot notation. Pick a non-null method when a fallback is intentional, and its `OrNull` counterpart when missing/invalid values must stay distinguishable.

```php
$count = Arr::toInteger(['order' => ['count' => '3']], 'order.count', 0);
$enabled = Arr::toBoolean(['enabled' => 'false'], 'enabled', true); // false
$missing = Arr::toIntegerOrNull([], 'count'); // null
$label = Arr::toStringOrNull(['label' => '0'], 'label', true); // null
```

- `toString`, `toStringable`, `toInteger`, `toFloat`, `toArray`, `toCollection`, `toDate`, `toDateTime` have nullable counterparts. Non-null fallbacks are empty string/Stringable, 0, 0.0, empty array/Collection, or the current date/time unless supplied otherwise.
- `toBoolean` / `toBooleanOrNull` recognize case-insensitive true/false, 1/0, yes/no, on/off and numeric values. Unknown text yields the default/null, not PHP's ordinary truthiness.
- Selected casts accept `emptyAsDefault` / `emptyAsNull`; null uses `toolkit.casting.empty_as_null` (false by default). Exact empty handling varies: string conversions treat `''` and `'0'` as empty, while numeric/date conversions use their own checks. Test 0, `'0'`, false, null and missing separately.
- `toArray` supports arrays, Arrayable, Jsonable, JsonSerializable, Stringable/JSON and scalar casting; `toCollection` builds on it. JSON scalar text such as `'123'` is not a safe array input: decoding it can violate the declared array return type. Validate shape before casting.
- `toDate` returns `CarbonImmutable` at start of day; `toDateTime` preserves time. Failed parsing on non-null variants falls back to the default or now. Nullable variants return null on failure. Use nullable variants for validation-sensitive imports so invalid dates do not silently become today.

## Presence, callbacks and lists

`Arr::tryKeys($array, ['primary', 'fallback'])` returns the first non-null value, preserving false and zero. `Arr::isset` requires a present, non-null key; `Arr::isEmpty` follows PHP empty semantics.

`whenHas`, `whenMissing`, `whenEmpty`, `whenNotEmpty`, `whenNull`, `whenNotNull` return the callback's result or the original array if no applicable callback exists. Callbacks normally receive `(array, value)`; `whenMissing`'s main callback receives only the array. Use explicit callback signatures and verify which branch ran.

```php
$value = Arr::whenHas(['count' => 3], 'count', fn ($array, $count) => $count * 2);
$list = Arr::wrapList(['id' => 'a']); // [['id' => 'a']]
$collection = Collection::wrapList(['id' => 'a']); // collection of one array
```

`wrapList` preserves a list and wraps associative data once. Test null, empty, list, and associative input; it does not normalize every nested level.

## StringHelper

`EinarHansen\Toolkit\Utilities\StringHelper::canBeCastToString($value)` includes arrays and JSON-capable values intended for encoding. It is not permission to directly cast every accepted value to string. Pair it with `shouldCastToJson()` as the Arr helpers do. Resources are accepted by the first predicate; neither predicate proves a useful human-readable representation or successful JSON encoding. Test encoding failures and unsupported objects where they matter.

## Memoizable

```php
use EinarHansen\Toolkit\Concerns\Memoizable;

final class Counter
{
    /** @use Memoizable<int> */
    use Memoizable;

    public function value(): int
    {
        return $this->memoize('count', fn (): int => 42);
    }

    public function invalidate(): void
    {
        $this->forget('count');
    }
}
```

The trait caches per object, including null results, using string keys. `memoize`, `forget`, `forgetAll`, `hasMemoized` are protected; expose only the domain operations needed. This is not Laravel's shared cache and has no TTL. Include tenant/user/arguments in keys when a long-lived instance serves different contexts; clear it after writes that invalidate cached values. Verify callback call count, cached null, explicit invalidation, and isolation between two instances.
