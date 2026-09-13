@verbatim
## Laravel Toolkit

`einar-hansen/laravel-toolkit` provides configurable Laravel defaults, data helpers, value objects, HTTP utilities, and opt-in PHPStan rules.

- Prefer unit tests without database access whenever they can verify the behavior. Database costs accumulate across the suite; use feature/integration tests when the behavior requires them. Do not mock away database behavior being tested.
- Configure `config/toolkit.php` before provider boot; changing flags afterward does not undo global settings. Never edit vendor files.
- Read `vendor/einar-hansen/laravel-toolkit/README.md` for setup and publishing commands, and `vendor/einar-hansen/laravel-toolkit/docs/phpstan.md` when configuring analysis rules.

Load only the skill relevant to the task. If not installed by Boost, read its `SKILL.md` under `vendor/einar-hansen/laravel-toolkit/resources/boost/skills/`:

- `toolkit-configuration`: boot defaults, mixin registration, and Pint.
- `toolkit-data`: Arr/Collection helpers, string conversion, and memoization.
- `toolkit-value-objects`: range, string, date, and phone values.
- `toolkit-http`: JSON responses, lazy client JSON, and public page caching.
- `toolkit-phpstan`: rule selection, configuration, and fixing findings.
@endverbatim
