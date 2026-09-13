# AI guideline and skill coverage audit

Audited 2026-09-13 against this working tree and Laravel Boost's [third-party guideline](https://laravel.com/framework/docs/boost#third-party-package-ai-guidelines) and [skill](https://laravel.com/framework/docs/boost#third-party-package-skills) conventions.

## Scope and scoring

This is a **documentation coverage assessment**, not a score for implementation quality, security, or proven agent performance. The inventory covers each runtime feature, all nine boot-time configurables, both publishers and every custom rule. Related Arr methods are grouped by workflow; their full method families are enumerated in the skill.

Before this change, the package shipped no `resources/boost` guideline or skill files: Boost-discoverable coverage was **0/10** for both across the inventory. Existing source/tests and the short README were useful evidence but were not discoverable package instructions.

Each guideline and skill is assessed separately on five criteria, 0–2 each:

1. Discoverability: correct package location and clear feature routing/trigger.
2. Accuracy: API/config names and defaults checked against implementation.
3. Actionability: setup/usage example or precise route to the focused workflow.
4. Boundaries: non-obvious failure cases, optional policy and project-specific assumptions.
5. Verification: concrete checks/examples grounded in source or existing behavior tests.

Every guideline row scores **8 = 2+2+1+2+1**: the core gives accurate routing and constraints, while detailed examples and checks live in the skills to avoid loading the entire manual upfront. Skill rows score **9 = 2+2+2+2+1** with source and fixture evidence. Configurables and StringHelper score **8 = 2+2+2+1+1**: guidance covers their source contract, but dedicated standalone behavioral coverage is thinner. No row is scored 10: actual agent task success and Boost installation across versions have not been independently benchmarked.

**Result: 42 features; minimum guideline score 8/10; minimum skill score 8/10.** The requested threshold is met for documentation coverage.

## Per-feature scorecard

All guideline scores refer to [core.blade.php](../resources/boost/guidelines/core.blade.php). Skill links contain feature-specific usage, limits and verification steps. Evidence links are the implementation/test artifacts used during review; they do not imply a new test was added for every existing feature.

| Feature / implementation | Guidelines | Skill | Evidence |
| --- | ---: | --- | --- |
| [Provider / config and mixin registration](../src/ToolkitServiceProvider.php) | 8/10 | [9/10 — toolkit-configuration](../resources/boost/skills/toolkit-configuration/SKILL.md) | [Source / tests](../tests/TestCase.php) |
| [Eloquent strict mode](../src/Configurables/ShouldBeStrict.php) | 8/10 | [8/10 — toolkit-configuration](../resources/boost/skills/toolkit-configuration/SKILL.md) | [Source / tests](../config/toolkit.php) |
| [Automatic relationship loading](../src/Configurables/AutomaticallyEagerLoadRelationships.php) | 8/10 | [8/10 — toolkit-configuration](../resources/boost/skills/toolkit-configuration/SKILL.md) | [Source / tests](../config/toolkit.php) |
| [Vite aggressive prefetch](../src/Configurables/AggressivePrefetching.php) | 8/10 | [8/10 — toolkit-configuration](../resources/boost/skills/toolkit-configuration/SKILL.md) | [Source / tests](../config/toolkit.php) |
| [HTTPS URL scheme](../src/Configurables/ForceScheme.php) | 8/10 | [8/10 — toolkit-configuration](../resources/boost/skills/toolkit-configuration/SKILL.md) | [Source / tests](../config/toolkit.php) |
| [Immutable dates](../src/Configurables/ImmutableDates.php) | 8/10 | [8/10 — toolkit-configuration](../resources/boost/skills/toolkit-configuration/SKILL.md) | [Source / tests](../config/toolkit.php) |
| [Production command protection](../src/Configurables/ProhibitDestructiveCommands.php) | 8/10 | [8/10 — toolkit-configuration](../resources/boost/skills/toolkit-configuration/SKILL.md) | [Source / tests](../config/toolkit.php) |
| [Password defaults](../src/Configurables/SetDefaultPassword.php) | 8/10 | [8/10 — toolkit-configuration](../resources/boost/skills/toolkit-configuration/SKILL.md) | [Source / tests](../config/toolkit.php) |
| [Fake sleep in tests](../src/Configurables/FakeSleep.php) | 8/10 | [8/10 — toolkit-configuration](../resources/boost/skills/toolkit-configuration/SKILL.md) | [Source / tests](../config/toolkit.php) |
| [Stray HTTP prevention](../src/Configurables/PreventStrayRequests.php) | 8/10 | [8/10 — toolkit-configuration](../resources/boost/skills/toolkit-configuration/SKILL.md) | [Source / tests](../config/toolkit.php) |
| [Arr keyed casts (all eight families + booleans)](../src/Mixins/ArrMixin.php) | 8/10 | [9/10 — toolkit-data](../resources/boost/skills/toolkit-data/SKILL.md) | [Source / tests](../tests/Mixins/ArrMixinTest.php) |
| [Arr presence / conditional callbacks](../src/Mixins/ArrMixin.php) | 8/10 | [9/10 — toolkit-data](../resources/boost/skills/toolkit-data/SKILL.md) | [Source / tests](../tests/Mixins/ArrMixinTest.php) |
| [Arr list wrapping](../src/Mixins/ArrMixin.php) | 8/10 | [9/10 — toolkit-data](../resources/boost/skills/toolkit-data/SKILL.md) | [Source / tests](../tests/Mixins/ArrMixinTest.php) |
| [Collection list wrapping](../src/Mixins/CollectionMixin.php) | 8/10 | [9/10 — toolkit-data](../resources/boost/skills/toolkit-data/SKILL.md) | [Source / tests](../tests/Mixins/CollectionMixinTest.php) |
| [String conversion predicates](../src/Utilities/StringHelper.php) | 8/10 | [8/10 — toolkit-data](../resources/boost/skills/toolkit-data/SKILL.md) | [Source / tests](../src/Mixins/ArrMixin.php) |
| [Instance memoization](../src/Concerns/Memoizable.php) | 8/10 | [9/10 — toolkit-data](../resources/boost/skills/toolkit-data/SKILL.md) | [Source / tests](../tests/Concerns/MemoizableTest.php) |
| [IntegerRangeValue](../src/ValueObjects/IntegerRangeValue.php) | 8/10 | [9/10 — toolkit-value-objects](../resources/boost/skills/toolkit-value-objects/SKILL.md) | [Source / tests](../tests/ValueObjects/IntegerRangeValueTest.php) |
| [FloatRangeValue](../src/ValueObjects/FloatRangeValue.php) | 8/10 | [9/10 — toolkit-value-objects](../resources/boost/skills/toolkit-value-objects/SKILL.md) | [Source / tests](../tests/ValueObjects/FloatRangeValueTest.php) |
| [StringLengthValue](../src/ValueObjects/StringLengthValue.php) | 8/10 | [9/10 — toolkit-value-objects](../resources/boost/skills/toolkit-value-objects/SKILL.md) | [Source / tests](../tests/ValueObjects/StringLengthValueTest.php) |
| [StringRegexValue](../src/ValueObjects/StringRegexValue.php) | 8/10 | [9/10 — toolkit-value-objects](../resources/boost/skills/toolkit-value-objects/SKILL.md) | [Source / tests](../tests/ValueObjects/StringRegexValueTest.php) |
| [DateRangeValue](../src/ValueObjects/DateRangeValue.php) | 8/10 | [9/10 — toolkit-value-objects](../resources/boost/skills/toolkit-value-objects/SKILL.md) | [Source / tests](../tests/ValueObjects/DateRangeValueTest.php) |
| [PhoneNumberValue](../src/ValueObjects/PhoneNumberValue.php) | 8/10 | [9/10 — toolkit-value-objects](../resources/boost/skills/toolkit-value-objects/SKILL.md) | [Source / tests](../tests/ValueObjects/PhoneNumberTest.php) |
| [Phone parsing / normalization / matching](../src/Concerns/UsesPhoneNumbers.php) | 8/10 | [9/10 — toolkit-value-objects](../resources/boost/skills/toolkit-value-objects/SKILL.md) | [Source / tests](../tests/Concerns/UsesPhoneNumbersTest.php) |
| [JSON contracts / wrapped HTTP responses](../src/Abstracts/Jsonable.php) | 8/10 | [9/10 — toolkit-http](../resources/boost/skills/toolkit-http/SKILL.md) | [Source / tests](../tests/Abstracts/JsonableTest.php) |
| [Lazy HTTP-client JSON](../src/Mixins/ResponseMixin.php) | 8/10 | [9/10 — toolkit-http](../resources/boost/skills/toolkit-http/SKILL.md) | [Source / tests](../tests/Mixins/ResponseMixinTest.php) |
| [Public page-cache headers](../src/Middleware/CachePageMiddleware.php) | 8/10 | [9/10 — toolkit-http](../resources/boost/skills/toolkit-http/SKILL.md) | [Source / tests](../tests/Middleware/CachePageMiddlewareTest.php) |
| [Pint publishing](../src/Commands/PublishPintConfigCommand.php) | 8/10 | [9/10 — toolkit-configuration](../resources/boost/skills/toolkit-configuration/SKILL.md) | [Source / tests](../tests/Commands/PublishPintConfigCommandTest.php) |
| [PHPStan publishing](../src/Commands/PublishPhpstanConfigCommand.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/Commands/PublishPhpstanConfigCommandTest.php) |
| [Opt-in extension / configuration](../extension.neon) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/ExtensionEnabledTest.php) |
| [EmptyCatchMustBeExplainedRule](../src/PHPStan/Rules/EmptyCatchMustBeExplainedRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/EmptyCatchMustBeExplainedRuleTest.php) |
| [JsonResourceAnnotationRule](../src/PHPStan/Rules/JsonResourceAnnotationRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/JsonResourceRulesTest.php) |
| [JsonResourceMagicMethodRule](../src/PHPStan/Rules/JsonResourceMagicMethodRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/JsonResourceRulesTest.php) |
| [JsonResourceMagicPropertyRule](../src/PHPStan/Rules/JsonResourceMagicPropertyRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/JsonResourceRulesTest.php) |
| [LiteralStringArgumentRule](../src/PHPStan/Rules/LiteralStringArgumentRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/LiteralStringRulesTest.php) |
| [LiteralStringReturnRule](../src/PHPStan/Rules/LiteralStringReturnRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/LiteralStringRulesTest.php) |
| [MailableLocaleRule](../src/PHPStan/Rules/MailableLocaleRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/MailableLocaleRuleTest.php) |
| [MigrationTimestampTzRule](../src/PHPStan/Rules/MigrationTimestampTzRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/MigrationTimestampTzRuleTest.php) |
| [ModelDocblockNoHandwrittenAnnotationsRule](../src/PHPStan/Rules/ModelDocblockNoHandwrittenAnnotationsRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/ModelDocblockNoHandwrittenAnnotationsRuleTest.php) |
| [ModelMixinRequiredRule](../src/PHPStan/Rules/ModelMixinRequiredRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/ModelMixinRequiredRuleTest.php) |
| [NoHardcodedApiMessageRule](../src/PHPStan/Rules/NoHardcodedApiMessageRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/NoHardcodedApiMessageRuleTest.php) |
| [RawSqlNoInterpolationRule](../src/PHPStan/Rules/RawSqlNoInterpolationRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/RawSqlNoInterpolationRuleTest.php) |
| [ResourceIdIsNotAutoIncrementRule](../src/PHPStan/Rules/ResourceIdIsNotAutoIncrementRule.php) | 8/10 | [9/10 — toolkit-phpstan](../resources/boost/skills/toolkit-phpstan/SKILL.md) | [Source / tests](../tests/PHPStan/ResourceIdIsNotAutoIncrementRuleTest.php) |

## Gaps repaired

- Added the exact Boost package guideline path and five skills with required YAML metadata and task-specific triggers.
- Documented all default flags, when they apply, and why toggling configuration after boot does not reverse global state.
- Distinguished HTTP client streaming from server responses, JSON wrapping from serialization, coercion from validation, and instance memoization from shared caching.
- Recorded actual edge cases: date fallback to now; JSON scalar array casts; phone normalization/region behavior; default-guard-only caching; FloatRangeValue's float-returning `integer()`.
- Documented every rule's policy switch, fix, limits and verification. Model helper, timestamp and public-ID conventions remain opt-in; locale traits and SQL helpers are application configuration.
- Added reusable rule fixtures and tests for configuration loading, named arguments, first-class callables and configured locale behavior.

## Validation and residual limits

- Skill frontmatter/naming validated with the skill-creator validator for all five skills.
- Guideline Blade rendering and local Markdown links checked; PHP examples checked against source and representative runtime smoke cases.
- PHP 8.4 validation: 592 tests / 1,069 assertions pass; PHPStan passes. Rule fixtures assert findings and the absence of false positives; extension tests check disabled defaults and all 13 enabled services. A temporary consuming-project smoke test loads the published preset and rejects an unexplained empty catch, then passes after an explanation is added.
- Existing runtime tests provide evidence for the established helpers; global configurables have source-based coverage, not an exhaustive cross-version/environment matrix.
- Actual `boost:install` discovery in a separate application with Boost is not exercised here because Boost is not installed in this package workspace. Paths/metadata follow the linked official contract; that integration gap prevents a 10/10 claim.
- Scores should be revisited when APIs, defaults, rules, dependencies or Boost packaging conventions change. Add a row for each new feature; update its routed skill and source/test evidence before assigning a score. Do not raise scores merely to meet the threshold.
