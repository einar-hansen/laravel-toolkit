---
name: toolkit-configuration
description: Configure Laravel Toolkit boot-time defaults, mixin registration, test behavior, and its published Pint preset in applications using einar-hansen/laravel-toolkit.
---

# Toolkit configuration

Use the application's installed `vendor/einar-hansen/laravel-toolkit/config/toolkit.php` and published `config/toolkit.php` as the authority. The auto-discovered `ToolkitServiceProvider` merges defaults and applies enabled configurables during boot; changing a flag after boot does not undo an already-applied global setting.

```sh
php artisan vendor:publish --tag=toolkit-config
```

Change flags before application boot, through environment settings or published configuration. Follow the application's normal configuration-cache refresh process after deployment. Do not edit the vendor copy.

## Defaults and effects

All config keys below are relative to `toolkit`. All flags default to true except automatic relationship loading.

| Feature / key | Environment variable | Effect and verification |
| --- | --- | --- |
| Eloquent strict mode: `eloquent.strict_mode` | `ELOQUENT_STRICT_MODE` | Calls `Model::shouldBeStrict()` in all environments. Test missing attributes and lazy-loading behavior; explicitly load needed relations. |
| Automatic relationship loading: `eloquent.eager_load_relationships` | `ELOQUENT_EAGER_LOAD_RELATIONSHIPS` | Defaults false; calls `Model::automaticallyEagerLoadRelationships()` only if available. Test query counts on the supported Laravel version. |
| Vite prefetch: `app.enable_aggressive_prefetching` | `APP_ENABLE_AGGRESSIVE_PREFETCHING` | Calls `Vite::useAggressivePrefetching()`. Check generated asset output and network behavior. |
| HTTPS URLs: `app.enforce_https_scheme` | `APP_ENFORCE_HTTPS_SCHEME` | Calls `URL::forceHttps()` in every environment. Assert generated URLs; disable before boot for HTTP-only local applications. |
| Immutable dates: `app.enable_immutable_dates` | `APP_ENABLE_IMMUTABLE_DATES` | Uses `CarbonImmutable` through Laravel's Date facade. Assign results of date modifications; test that the original date is unchanged. This does not rewrite explicit mutable Carbon usage. |
| Destructive commands: `app.disable_destructive_commands` | `APP_DISABLE_DESTRUCTIVE_COMMANDS` | Calls `DB::prohibitDestructiveCommands(app()->isProduction())`. Exercise command protection in an isolated production-configured test, never against production data. |
| Password defaults: `app.use_default_password` | `APP_USE_DEFAULT_PASSWORD` | In production: min 12, max 255, uncompromised. Outside production the callback returns null. Use `Password::defaults()` in validation and test the application's environment-specific behavior. |
| Fake sleep: `tests.enable_fake_sleep` | `TESTS_ENABLE_FAKE_SLEEP` | Only while `runningUnitTests()`: `Sleep::fake()`. Assert requested sleep, rather than elapsed wall time. |
| Block stray HTTP: `tests.prevent_stray_requests` | `TESTS_PREVENT_STRAY_REQUESTS` | Only while `runningUnitTests()`: `Http::preventStrayRequests()`. Supply `Http::fake()` responses; assert expected requests. |

The provider registers Arr, Collection, and **HTTP client Response** mixins by default. Disable them before boot using `toolkit.mixins.arr`, `.collection`, `.response` (environment variables `MIXIN_REGISTER_ARR`, `MIXIN_REGISTER_COLLECTION`, `MIXIN_REGISTER_RESPONSE`) if they collide with application macros. The response mixin does not target the server Response facade. `toolkit.casting.empty_as_null` / `CASTING_EMPTY_AS_NULL` defaults false and affects selected Arr casts; an explicit per-call argument overrides it.

For example, in the test framework's environment configuration hook before providers boot:

```php
$app['config']->set('toolkit.app.enforce_https_scheme', false);
$app['config']->set('toolkit.mixins.arr', false);
```

## Pint publishing

```sh
php artisan toolkit:publish:pint --backup
vendor/bin/pint --test
```

The command writes `pint.json` and asks before replacement. `--backup` saves `pint.json.backup`; `--force` skips confirmation. Install Pint in the consuming project's dev dependencies if absent. The opinionated rules include strict types/comparisons, immutable date suggestions, global imports and ordered class elements: inspect the diff when applying it to an existing project. `toolkit-stubs` publishes stub sources, not active tool configuration.

Verify the published config contents and run the project's targeted tests for each changed global default. In Toolkit itself, use `composer test` and `composer analyse`; do not assume the consuming application has those script names.
