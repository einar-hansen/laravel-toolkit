---
name: toolkit-http
description: Use Laravel Toolkit Jsonable responses, lazy HTTP-client JSON iteration, and opt-in page-cache middleware with their actual wrapping, stream, and privacy constraints.
---

# Toolkit HTTP features

## JSON value/response objects

Extend `EinarHansen\Toolkit\Abstracts\Jsonable`, implement `toArray(): array`, and describe the array shape through `@extends Jsonable<...>`.

```php
use EinarHansen\Toolkit\Abstracts\Jsonable;

/** @extends Jsonable<array{name: string}> */
final class ProfilePayload extends Jsonable
{
    public function __construct(private string $name) {}

    public function toArray(): array
    {
        return ['name' => $this->name];
    }
}

$payload = new ProfilePayload('Ada');
$json = $payload->toJson(); // {"name":"Ada"}
$response = $payload->toResponse(request()); // {"data":{"name":"Ada"}}
```

`toArray`, `jsonSerialize`, `toJson`, and string casting are unwrapped. HTTP responses default to a `data` wrapper. `withWrap('profile')` and `withoutWrap()` mutate the object and return it; null, empty string and `'0'` disable wrapping. `toMetaResponse($request, $meta, $status, $headers)` adds a sibling `meta` key; it overwrites an existing top-level meta entry. `toJson($options)` uses `JSON_THROW_ON_ERROR`, so encoding failures can throw even during string casting.

Verify exact JSON shapes, wrapper changes, response status/headers, metadata and invalid UTF-8 encoding. Jsonable is not an Eloquent JsonResource and is not subject to Toolkit's JsonResource annotation rules.

## Lazy HTTP-client JSON

With `toolkit.mixins.response` enabled, `Illuminate\Http\Client\Response::lazy(?string $key)` returns a LazyCollection over JsonMachine. The argument is a **JSON Pointer**, not Laravel dot notation.

```php
use Illuminate\Support\Facades\Http;

foreach (Http::get($url)->throw()->lazy('/data') as $key => $row) {
    // Process one decoded item; objects are decoded as arrays.
}
```

Check HTTP status with `throw()` as appropriate before iteration. A missing pointer or unavailable stream yields no items; malformed JSON can fail during iteration. It rewinds the response resource, so do not assume compatibility with non-seekable streams or simultaneous consumers. Calling `all()` removes the memory advantage, and this helper alone does not guarantee the HTTP transport streamed the body from the network. Test with `Http::fake()` (Toolkit blocks stray requests in tests), nested pointers, missing paths and malformed input.

## Public page cache headers

`EinarHansen\Toolkit\Middleware\CachePageMiddleware` is **not registered automatically**. Attach it explicitly to routes whose successful content is safe for shared caching:

```php
Route::get('/public-info', PublicInfoController::class)
    ->middleware(\EinarHansen\Toolkit\Middleware\CachePageMiddleware::class);
```

It sets `Cache-Control: max-age=1800, public` when the app is production, the default auth guard reports a guest, the request is GET and the response is successful. It does not store pages, purge a CDN, inspect cookies, check every auth guard, or detect personalized data. It replaces an existing Cache-Control header on eligible responses. Do not attach it to guest-specific sessions, private content, token-guard routes, or responses that must retain private/no-store headers. Verify guest/authenticated behavior, method/status/environment conditions, and the exact header in route tests; inspect alternate authentication guards in the consuming application.
