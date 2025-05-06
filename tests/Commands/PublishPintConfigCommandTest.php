<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Commands;

use EinarHansen\Toolkit\Commands\PublishPintConfigCommand;
use EinarHansen\Toolkit\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Override;
use PHPUnit\Framework\Attributes\Test;

class PublishPintConfigCommandTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        // Clean up any existing pint.json files
        if (file_exists($this->app->basePath('pint.json'))) {
            unlink($this->app->basePath('pint.json'));
        }

        if (file_exists($this->app->basePath('pint.json.backup'))) {
            unlink($this->app->basePath('pint.json.backup'));
        }
    }

    #[Test]
    public function it_publishes_pint_configuration_file(): void
    {
        new PublishPintConfigCommand;
        $this->artisan('toolkit:publish:pint', ['--force' => true])
            ->assertExitCode(0);

        $this->assertTrue(file_exists($this->app->basePath('pint.json')));
    }

    #[Test]
    public function it_creates_backup_when_requested(): void
    {
        // Create a dummy pint.json file first
        File::put($this->app->basePath('pint.json'), '{"test": "original"}');

        $this->artisan('toolkit:publish:pint', ['--backup' => true, '--force' => true])
            ->assertExitCode(0);

        $this->assertTrue(file_exists($this->app->basePath('pint.json.backup')));
    }

    #[Test]
    public function it_warns_when_file_exists_and_no_force_option(): void
    {
        // Create a dummy pint.json file first
        File::put($this->app->basePath('pint.json'), '{"test": "original"}');

        $this->artisan('toolkit:publish:pint')
            ->expectsConfirmation('Do you wish to publish the Pint configuration file? This will override the existing [pint.json] file.', 'no')
            ->assertExitCode(0);

        // File should remain unchanged
        $this->assertEquals('{"test": "original"}', file_get_contents($this->app->basePath('pint.json')));
    }
}
