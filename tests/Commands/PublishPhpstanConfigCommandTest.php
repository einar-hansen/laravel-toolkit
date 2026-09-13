<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Commands;

use EinarHansen\Toolkit\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Override;
use PHPUnit\Framework\Attributes\Test;

class PublishPhpstanConfigCommandTest extends TestCase
{
    private string $directory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/toolkit-phpstan-'.bin2hex(random_bytes(8));
        File::makeDirectory($this->directory);
        $this->app->setBasePath($this->directory);
    }

    #[Override]
    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    #[Test]
    public function it_publishes_the_configuration(): void
    {
        $this->artisan('toolkit:publish:phpstan', ['--force' => true])
            ->assertExitCode(0);

        $this->assertFileEquals(
            __DIR__.'/../../stubs/phpstan.stub',
            $this->directory.'/phpstan.neon',
        );
    }

    #[Test]
    public function it_preserves_the_original_in_a_backup_before_overwriting(): void
    {
        File::put($this->directory.'/phpstan.neon', "parameters:\n    level: 5\n");

        $this->artisan('toolkit:publish:phpstan', ['--backup' => true, '--force' => true])
            ->assertExitCode(0);

        $this->assertSame("parameters:\n    level: 5\n", File::get($this->directory.'/phpstan.neon.backup'));
        $this->assertFileEquals(__DIR__.'/../../stubs/phpstan.stub', $this->directory.'/phpstan.neon');
    }

    #[Test]
    public function it_leaves_the_existing_configuration_untouched_when_declined(): void
    {
        File::put($this->directory.'/phpstan.neon', "parameters:\n    level: 5\n");

        $this->artisan('toolkit:publish:phpstan', ['--backup' => true])
            ->expectsConfirmation('Do you wish to publish the PHPStan configuration file? This will override the existing [phpstan.neon] file.', 'no')
            ->assertExitCode(0);

        $this->assertSame("parameters:\n    level: 5\n", File::get($this->directory.'/phpstan.neon'));
        $this->assertFileDoesNotExist($this->directory.'/phpstan.neon.backup');
    }
}
