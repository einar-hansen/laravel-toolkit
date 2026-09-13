@verbatim
## Laravel Toolkit

`einar-hansen/laravel-toolkit` supports PHP 8.4+ and Laravel 12/13. Its auto-discovered `EinarHansen\Toolkit\ToolkitServiceProvider` applies configurable defaults and registers Arr, Collection and HTTP-client Response mixins. Configure the application, never vendor files:

```sh
php artisan vendor:publish --tag=toolkit-config
```

### Testing preference

Prefer unit tests over feature tests whenever the behavior can be verified without the database. Test pure logic directly with plain objects, in-memory data, or focused fakes; avoid database setup, persisted factories, and `RefreshDatabase` when they are unnecessary. The cost of database access adds up across the suite. Use feature or integration tests when verifying HTTP behavior, persistence, queries, relationships, database constraints, or other framework integration; keep database setup limited to what the behavior requires. Do not mock away database behavior that the test is meant to verify.

### Boot-time defaults — skill: toolkit-configuration

Set flags before provider boot; changing config later does not undo global state. All are under `toolkit` and default true except automatic relationship loading:

| Feature | Config key | Invariant / verification |
| --- | --- | --- |
| Eloquent strictness | `eloquent.strict_mode` | All environments; explicitly load needed relations and test missing attributes. |
| Automatic eager loading | `eloquent.eager_load_relationships` | Default false; only when framework supports it. Check query counts. |
| Vite prefetch | `app.enable_aggressive_prefetching` | Enables aggressive asset prefetch; inspect generated assets/network behavior. |
| HTTPS URLs | `app.enforce_https_scheme` | All environments; verify generated URLs for local HTTP setups. |
| Immutable dates | `app.enable_immutable_dates` | Laravel Date uses CarbonImmutable; assign modification results and test original value. |
| Destructive commands | `app.disable_destructive_commands` | Production protection; test only against isolated databases. |
| Password defaults | `app.use_default_password` | Production min 12/max 255/uncompromised; verify validation environment. |
| Fake sleep | `tests.enable_fake_sleep` | Tests only; assert requested sleep rather than elapsed time. |
| Stray HTTP prevention | `tests.prevent_stray_requests` | Tests only; use Http::fake and assert requests. |

`mixins.arr`, `mixins.collection`, `mixins.response` default true. Disable before boot for macro conflicts. Response means `Illuminate\Http\Client\Response`, not the server Response facade. `casting.empty_as_null` defaults false.

### Data helpers — skill: toolkit-data

Use `Arr::toInteger($data, 'count', 0)` and corresponding `toString`, `toStringable`, `toFloat`, `toBoolean`, `toArray`, `toCollection`, `toDate`, `toDateTime`; `OrNull` variants preserve missing/invalid data. These coerce, not validate. Test missing/null/zero/false separately. Date casts return immutable dates; non-null invalid dates can fall back to now. Array casts need validated JSON array/object shapes, not JSON scalar text.

`Arr::tryKeys` returns the first non-null value. `isset`, `isEmpty` and `whenHas`/`whenMissing`/`whenEmpty`/`whenNotEmpty`/`whenNull`/`whenNotNull` distinguish presence from truthiness. Check callback arguments and returned values. `Arr::wrapList` / `Collection::wrapList` wrap associative data once; verify empty and list input.

`StringHelper::canBeCastToString` includes values intended for JSON; pair with `shouldCastToJson`, not an unconditional string cast. Verify unsupported objects and encoding failures.

`Concerns\Memoizable` caches per instance, including null, via protected `memoize`, `forget`, `forgetAll`, `hasMemoized`. Add the `@use Memoizable<T>` annotation. No TTL/shared cache; key by context and invalidate after writes. Test callback count and instance isolation.

### Value objects — skill: toolkit-value-objects

Subclass `ValueObjects\IntegerRangeValue` (min/max), `FloatRangeValue` (min/max/precision), `StringLengthValue` (min/max length), `StringRegexValue` (pattern), `DateRangeValue` (min/max date) or `PhoneNumberValue` (region/string format). Bounds are inclusive, null disables supported constraints. Test boundaries, malformed input and Unicode/rounding/timezones as relevant.

`from` throws on validation failure; `tryFrom` returns null for InvalidArgumentException, not arbitrary type errors. Array factories do not universally replace invalid present values with defaults. Keep inherited constructor signatures compatible. Float `integer()` returns a float; readonly date storage does not imply immutable contents.

`UsesPhoneNumbers` defaults to NO. Validate independently of normalization/matching fallbacks. Prefer explicit-region `e164()` for canonical phone storage; `PhoneNumberValue::value()` currently does not forward the object's region override. Test local/international/invalid numbers.

### HTTP — skill: toolkit-http

`Abstracts\Jsonable` implements Laravel/PHP JSON contracts through a typed `toArray()`. JSON/string output is unwrapped; HTTP output wraps in `data`. `withoutWrap`/`withWrap` mutate, `toMetaResponse` adds metadata, encoding can throw. Assert exact shapes and response status.

`Http::get($url)->throw()->lazy('/data')` uses JSON Pointer and yields a LazyCollection. Missing paths/streams can yield no items; malformed JSON can fail during iteration. Test with fake HTTP; avoid `all()` for large payloads.

`Middleware\CachePageMiddleware` must be explicitly attached. It overwrites Cache-Control with `max-age=1800, public` for production successful guest GETs under the default guard. Use only genuinely public content; it does not inspect cookies/alternate guards or preserve private headers. Test auth, environment, method and status.

### Tooling — skill: toolkit-phpstan

`toolkit:publish:pint --backup` writes an opinionated Pint preset; inspect formatting changes and run Pint. `toolkit:publish:phpstan --backup` writes a level 6 preset with five policies enabled. Both confirm replacement; `--force` bypasses the prompt. Install analysis/formatting dependencies in the consuming project. Preserve `.dist` precedence and local config edits.

Include `vendor/einar-hansen/laravel-toolkit/extension.neon` alongside Larastan for reusable rules. Its switches under `parameters.toolkit` all default false; choose policies explicitly:

| Policy | Rules / intended correction |
| --- | --- |
| `noHardcodedApiMessages` | NoHardcodedApiMessageRule: translate literal API messages. |
| `mailableLocale` | MailableLocaleRule: select recipient locale and translate subjects. |
| `jsonResources` | JsonResourceAnnotationRule: type `$resource`; JsonResourceMagicPropertyRule / JsonResourceMagicMethodRule: access wrapped fields/methods through it. |
| `rawSql` | RawSqlNoInterpolationRule: bind runtime values; LiteralStringReturnRule / LiteralStringArgumentRule: uphold literal-string contracts. Enable together. |
| `explainedEmptyCatches` | EmptyCatchMustBeExplainedRule: handle failures or explain discarded exceptions. |
| `modelDocblocks`, `modelMixins` | ModelDocblockNoHandwrittenAnnotationsRule / ModelMixinRequiredRule: opt-in IDE Helper conventions; require separate helper generation. |
| `migrationTimestampTz` | MigrationTimestampTzRule: use Tz Blueprint methods where the database policy requires them; preserve deployed migrations. |
| `noIntegerResourceIds` | ResourceIdIsNotAutoIncrementRule: opt-in ULID/slug policy; identifiers never replace authorization. |

Configure application namespaces/controller/migration paths and trusted locale/SQL helpers; no Memberflow helper is included. These are targeted checks, not a proof of security. Test the relevant failure paths and run PHPStan after policy changes. Errors use `toolkit.*`; migrate old service registrations/baselines deliberately. Detailed settings and limitations are in the toolkit-phpstan skill and the package's `docs/phpstan.md`.
@endverbatim
