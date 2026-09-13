@verbatim
## Laravel Toolkit

`einar-hansen/laravel-toolkit` provides configurable Laravel defaults, data helpers, value objects, HTTP utilities, and opt-in PHPStan rules.

- Configure `config/toolkit.php` before provider boot; changing flags afterward does not undo global settings. Never edit vendor files.
- Read `vendor/einar-hansen/laravel-toolkit/README.md` for setup and publishing commands, and `vendor/einar-hansen/laravel-toolkit/docs/phpstan.md` when configuring analysis rules.

Load only the skill relevant to the task. If not installed by Boost, read its `SKILL.md` under `vendor/einar-hansen/laravel-toolkit/resources/boost/skills/`:

- `toolkit-configuration`: boot defaults, mixin registration, and Pint.
- `toolkit-data`: Arr/Collection helpers, string conversion, and memoization.
- `toolkit-value-objects`: range, string, date, and phone values.
- `toolkit-http`: JSON responses, lazy client JSON, and public page caching.
- `toolkit-phpstan`: rule selection, configuration, and fixing findings.

### Testing preference

Prefer unit tests over feature tests whenever the behavior can be verified without the database. Test pure logic directly with plain objects, in-memory data, or focused fakes; avoid database setup, persisted factories, and `RefreshDatabase` when they are unnecessary. The cost of database access adds up across the suite. Use feature or integration tests when verifying HTTP behavior, persistence, queries, relationships, database constraints, or other framework integration; keep database setup limited to what the behavior requires. Do not mock away database behavior that the test is meant to verify.
@endverbatim
