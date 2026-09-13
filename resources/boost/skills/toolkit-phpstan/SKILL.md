---
name: toolkit-phpstan
description: Install, configure, migrate, and fix findings from Laravel Toolkit's 13 optional PHPStan rules for resources, SQL, translations, mail, models, migrations, and empty catches.
---

# Toolkit PHPStan rules

Inspect the application's existing PHPStan file, Larastan/PHPStan versions, source paths and policies. Toolkit requires PHPStan 2.1.31+ within 2.x for these optional rules; install Larastan 3.7+ in the application's dev dependencies. Do not raise its level or replace its baseline as a side effect of enabling Toolkit.

```neon
includes:
    - vendor/larastan/larastan/extension.neon
    - vendor/einar-hansen/laravel-toolkit/extension.neon

parameters:
    toolkit:
        jsonResources: true
        rawSql: true
        explainedEmptyCatches: true
```

All extension policy switches default false. For a new config, `php artisan toolkit:publish:phpstan --backup` publishes level 6 with API-message, mailable, resource, SQL and empty-catch policies enabled. It asks before replacing `phpstan.neon`; `--force` skips that prompt. Merge into an existing `.dist` file deliberately because `.neon` takes precedence.

## Fix the policy, preserve application intent

| Switch / rules | Fix and verification |
| --- | --- |
| `noHardcodedApiMessages` / `NoHardcodedApiMessageRule` | Translate literal `message` values in direct `response()->json()` controller calls, e.g. `['message' => __('messages.saved')]`. `OK`, `PONG`, `ACK`, blank strings are exempt. Test output in two supported locales; variables/facade calls are outside this syntax check. |
| `mailableLocale` / `MailableLocaleRule` | Choose locale with `$this->locale($recipientLocale)` or configured locale-aware behavior; translate subject expressions. Test recipient locale and rendered subject/body. The rule does not prove that a trait/method actually sets the right locale or inspect Blade bodies. |
| `jsonResources` / `JsonResourceAnnotationRule` | Declare `@property User $resource`, naming the wrapped model/DTO/shape; `mixed` and `@mixin` do not satisfy it. Inherited annotations count. Test typed resources and serialization. |
| `jsonResources` / `JsonResourceMagicPropertyRule` | Replace `$this->name` with `$this->resource->name` for wrapped fields; native resource properties remain valid. Verify missing/nullable attributes under the application's model settings. |
| `jsonResources` / `JsonResourceMagicMethodRule` | Replace forwarded `$this->relation()` with `$this->resource->relation()`; keep real resource helpers such as `whenLoaded()`. Test loaded/unloaded relations. |
| `modelDocblocks` / `ModelDocblockNoHandwrittenAnnotationsRule` | Opt in only to a generated IDE Helper policy. Remove duplicate model property/method tags, keep the expected `@mixin IdeHelper<Model>`, and generate the helper. Preserve meaningful prose. Verify regenerated types, not just a clean diff. |
| `modelMixins` / `ModelMixinRequiredRule` | Add the correct `@mixin IdeHelper<Model>`; `php artisan ide-helper:models --write-mixin` requires the separately installed IDE Helper package. The rule checks text, not generated helper availability, and includes abstract named models. |
| `migrationTimestampTz` / `MigrationTimestampTzRule` | Opt in when the database policy needs Blueprint `timestampTz`, `timestampsTz`, `softDeletesTz`. Include migrations in analysed paths. Preserve already-deployed migrations; baseline existing findings or make a new migration. Verify generated schema on the actual engine. |
| `noIntegerResourceIds` / `ResourceIdIsNotAutoIncrementRule` | Opt in to a public-ID policy; return the ULID/slug under `id` or `*_id` keys instead of an integer. Confirm API compatibility and authorization separately. Integer types do not prove auto-increment semantics. |
| `rawSql` / `RawSqlNoInterpolationRule` | Use `whereRaw('email = ?', [$email])` instead of interpolation. Literal fragments and numeric/boolean types can pass; validate/quote dynamic identifiers through explicitly trusted helpers. Test hostile input and bindings; this is not a security proof. |
| `rawSql` / `LiteralStringReturnRule` | A `@return literal-string` must return source-derived strings. Do not add a misleading annotation to silence a SQL finding. Check a dynamic-string negative fixture. |
| `rawSql` / `LiteralStringArgumentRule` | Pass source-derived strings to literal-string method parameters; configure every application namespace. Free functions/unpacked arguments are not covered. Keep all three SQL rules enabled together. |
| `explainedEmptyCatches` / `EmptyCatchMustBeExplainedRule` | Handle the failure or document why discarding this exception is acceptable. Comments are detected but their reasoning needs review. Verify both failure and success paths. |

## Adapt paths and helper contracts

All settings below live under `parameters.toolkit`. Path settings match substrings, not globs. Empty restricted-path lists select no files. PHPStan merges lists; use `!` to replace defaults.

```neon
parameters:
    toolkit:
        controllerPaths!: [src/App/Http/Controllers]
        migrationPaths!: [database/migrations]
        applicationNamespaces!: [App\, Domain\]
        localeAwareTraits: [App\Mail\Concerns\LocaleAwareMailable]
        localeMethods: [applyRecipientLocale]
        safeSqlCalls: [App\Support\Database\SqlIdentifier::quote]
        excludedSqlPaths: []
        excludedCatchPaths: []
        excludedMailableNamespaces: []
```

Defaults are `app/Http/Controllers`, `database/migrations`, `App\`, and locale method `locale`; all trusted trait/helper and exclusion lists are empty. Register only helpers that validate/quote identifiers for the actual database. There is no shipped `SqlIdentifier` or locale trait. Prefer narrow exclusions where the policy genuinely does not apply.

For migration from Memberflow, replace `MemberFlow\PHPStan\Rules` service registrations with the extension, retain project-specific options explicitly, and update specific `memberflow.*` identifiers to `toolkit.*` in accepted baselines. Do not register the same rule twice.

Run `vendor/bin/phpstan analyse` and relevant application tests. When editing Toolkit rules, use `vendor/bin/phpunit tests/PHPStan` and `composer analyse`; add positive/negative fixtures and configuration cases for changed behavior. See the installed package's `docs/phpstan.md` and `extension.neon` for the full public settings.
