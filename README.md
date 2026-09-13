# Laravel Toolkit

A collection of helpful utilities and extensions to speed up your Laravel development. This package gives you practical solutions for common Laravel challenges - less code, more power, and faster development for your projects.

From database shortcuts to frontend helpers, Laravel Toolkit simplifies your workflow whether you're building your first app or your hundredth.

Build better Laravel apps, faster.

# PHPStan rules and configuration

Publish an opinionated PHPStan configuration in your Laravel application:

```sh
composer require --dev larastan/larastan:^3.7
php artisan toolkit:publish:phpstan
vendor/bin/phpstan analyse
```

The command writes `phpstan.neon` in your application's root. It asks for confirmation before overwriting the file. Use `--backup` to preserve an existing file as `phpstan.neon.backup`, or `--force` to skip confirmation:

```sh
php artisan toolkit:publish:phpstan --backup --force
```

The published configuration enables Toolkit’s API-message, mailable, resource, SQL and empty-catch policies and analyses `app/` at level 6 with nullable, uninitialized and dynamic property checks, plus Larastan checks for model properties, Octane compatibility, unnecessary collection calls and `env()` calls outside configuration files. Adjust `paths` and `level` in the published file for your application. See the [PHPStan configuration reference](https://phpstan.org/config-reference) and [Larastan rules](https://github.com/larastan/larastan/blob/3.x/docs/rules.md) for details.

Larastan must be installed in the consuming application; this package's development dependencies are not installed transitively. If you use `phpstan.neon.dist`, merge the published settings into it and remove `phpstan.neon`, which takes precedence.


For an existing project, include `vendor/einar-hansen/laravel-toolkit/extension.neon` and opt in to the rules you want. All 13 custom rules, policy switches, configuration options and migration steps are documented in [Reusable PHPStan rules](docs/phpstan.md).

# Laravel Boost

This package ships `resources/boost/guidelines/core.blade.php` and five focused skills for configuration, data helpers, value objects, HTTP features and PHPStan. In a consuming application with Boost installed, run `php artisan boost:install` and select the package resources. For an existing Boost installation, run `php artisan boost:update --discover` to discover newly added resources.

See [Boost package guidelines](https://laravel.com/framework/docs/boost#third-party-package-ai-guidelines), [package skills](https://laravel.com/framework/docs/boost#third-party-package-skills), and our [per-feature coverage audit](docs/ai-coverage-audit.md).

# Requirements

* Laravel v12 and above

# Environment variables

```dotenv
ELOQUENT_STRICT_MODE=true
ELOQUENT_EAGER_LOAD_RELATIONSHIPS=false
APP_ENABLE_AGGRESSIVE_PREFETCHING=true
APP_ENFORCE_HTTPS_SCHEME=true
APP_ENABLE_IMMUTABLE_DATES=true
APP_DISABLE_DESTRUCTIVE_COMMANDS=true
APP_USE_DEFAULT_PASSWORD=true
TESTS_ENABLE_FAKE_SLEEP=true
TESTS_PREVENT_STRAY_REQUESTS=true
```
