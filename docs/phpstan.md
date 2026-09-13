# Reusable PHPStan rules

Install Larastan as a **development dependency in the consuming project**. Toolkit's runtime provider does not register PHPStan rules or load PHPStan. These rules support PHPStan 2.1.31+ (2.x), PHP 8.4+, and Laravel 12/13 via Larastan 3.7+.

```sh
composer require --dev larastan/larastan:^3.7
```

For an existing configuration, add the extension and choose policies explicitly:

```neon
includes:
    - vendor/larastan/larastan/extension.neon
    - vendor/einar-hansen/laravel-toolkit/extension.neon

parameters:
    level: 6
    paths:
        - app
    toolkit:
        jsonResources: true
        rawSql: true
        explainedEmptyCatches: true
```

All policies default to **false** in `extension.neon`. Including it does not choose a level, application paths, baselines, or error suppressions. It is not automatically installed by `phpstan/extension-installer`.

For a new configuration, `php artisan toolkit:publish:phpstan` publishes a level 6 Laravel preset and enables `noHardcodedApiMessages`, `mailableLocale`, `jsonResources`, `rawSql`, and `explainedEmptyCatches`. The command confirms replacement; `--backup` saves the old `phpstan.neon` as `.backup`, and `--force` skips confirmation. If your project uses `.dist`, merge the new settings into it: `phpstan.neon` takes precedence. Re-publishing overwrites local edits.

## Policies

All classes are in `EinarHansen\Toolkit\PHPStan\Rules`. They can also be registered individually as services tagged `phpstan.rules.rule`, using the constructor arguments shown in their source. Prefer grouped switches for resources and SQL so their complementary checks stay together.

| Switch | Rule classes | Required behavior and scope |
| --- | --- | --- |
| `noHardcodedApiMessages` | `NoHardcodedApiMessageRule` | In `controllerPaths`, replace literal `response()->json(['message' => 'Saved'])` with a translation. Empty strings and machine sentinels `OK`, `PONG`, `ACK` are allowed. |
| `mailableLocale` | `MailableLocaleRule` | Concrete mailables choose a locale and translate hardcoded subjects. Allows configured traits/methods and namespace exclusions. |
| `jsonResources` | `JsonResourceAnnotationRule`, `JsonResourceMagicPropertyRule`, `JsonResourceMagicMethodRule` | Declare `@property Model $resource`; use `$this->resource->name` and `$this->resource->relation()`. A `@mixin` or `mixed` annotation does not satisfy the resource declaration. Abstract/anonymous resources and resource collections are exempt from the annotation requirement; inherited annotations count. |
| `modelDocblocks` | `ModelDocblockNoHandwrittenAnnotationsRule` | For projects using IDE Helper: move generated model properties/methods out of class docblocks; only the expected `@mixin IdeHelper<Model>` mixin is accepted. |
| `modelMixins` | `ModelMixinRequiredRule` | Require the `@mixin IdeHelper<Model>` tag on named Eloquent subclasses. Install/configure IDE Helper before opting in; abstract models are also checked. |
| `migrationTimestampTz` | `MigrationTimestampTzRule` | In `migrationPaths`, replace Blueprint `timestamp`, `timestamps`, `softDeletes` with their `Tz` variants. Intended for database policies requiring timezone-aware timestamps; database engines differ. Add migrations to PHPStan's analysed paths. Preserve deployed migrations and baseline existing findings if needed. |
| `noIntegerResourceIds` | `ResourceIdIsNotAutoIncrementRule` | Reject integer (including nullable integer) values under `id` / `*_id` keys in a resource's `toArray()`. This enforces a public-identifier policy; it cannot establish whether an integer actually auto-increments. Public identifiers do not replace authorization. |
| `rawSql` | `RawSqlNoInterpolationRule`, `LiteralStringReturnRule`, `LiteralStringArgumentRule` | Put dynamic values in bindings. Allow literal fragments, safe numeric/boolean types, and explicitly trusted identifier helpers. Enforce literal-string return contracts and method arguments in `applicationNamespaces`. |
| `explainedEmptyCatches` | `EmptyCatchMustBeExplainedRule` | Handle an exception or explain an empty catch with a comment. `excludedCatchPaths` defaults to empty, including console commands. Comments are detected, not evaluated for quality. |

## Project-specific settings

Settings live under `parameters.toolkit`:

| Setting | Default | Meaning |
| --- | --- | --- |
| `controllerPaths` | `[app/Http/Controllers]` | File-path fragments for API message checks. |
| `migrationPaths` | `[database/migrations]` | File-path fragments for timestamp checks. |
| `applicationNamespaces` | `[App\]` | Declaring-class prefixes for literal-string argument checks. Include each application/module namespace. |
| `localeAwareTraits` | `[]` | Fully qualified trait names trusted to arrange recipient locale. |
| `localeMethods` | `[locale]` | Calls on `$this` accepted as locale selection. |
| `excludedMailableNamespaces` | `[]` | Class prefixes exempt from both mailable checks. |
| `safeSqlCalls` | `[]` | Trusted static `Class::method` calls whose result can be embedded in SQL. Only register helpers that actually validate/quote identifiers for your database. |
| `excludedSqlPaths` | `[]` | File-path fragments exempt from the raw-SQL check. |
| `excludedCatchPaths` | `[]` | File-path fragments exempt from the empty-catch check. |

Lists merge with PHPStan's defaults; use NEON's `!` suffix to replace a list rather than append to it:

```neon
parameters:
    toolkit:
        controllerPaths!:
            - src/App/Http/Controllers
        applicationNamespaces!:
            - App\
            - Domain\
        localeAwareTraits:
            - App\Mail\Concerns\LocaleAwareMailable
        localeMethods:
            - applyRecipientLocale
        safeSqlCalls:
            - App\Support\Database\SqlIdentifier::quote
```

No application helper, tenant path, baseline, or IDE Helper installation is shipped by this package. Error identifiers use the `toolkit.*` prefix. When migrating from Memberflow's local rules, remove the old service registrations and update specific baseline identifiers from `memberflow.*` to `toolkit.*`; keep your existing paths and exclusions explicit.

## Examples and limits

```php
// Translated response, rather than hardcoded user-facing copy.
return response()->json(['message' => __('messages.saved')]);

// Dynamic SQL values belong in bindings.
$query->whereRaw('email = ?', [$email]);

// Deliberate empty catch.
try {
    $cache->forget($key);
} catch (CacheUnavailable $exception) {
    // Cache eviction is best effort; the stored entry expires after one minute.
}
```

These are targeted syntax/type checks, not complete data-flow or security analysis. The API-message rule only handles literal messages in direct `response()->json()` calls. Mailable checks look for locale selection and literal subject expressions, not translated Blade bodies or runtime locale correctness. Literal-string argument checks cover method/static calls, not free functions, unpacked arguments, or arbitrary data flow. Raw query-builder method names are matched even on unknown receivers. Do not present a clean PHPStan run as proof that arbitrary SQL is safe.

Run `vendor/bin/phpstan analyse` and the application's relevant tests after enabling a policy. Fix new findings at their source; preserve necessary compatibility baselines rather than copying another project's suppressions. PHPStan's [extension configuration documentation](https://phpstan.org/developing-extensions/dependency-injection-configuration) explains the service and parameter mechanism.
