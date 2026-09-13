<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Other;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A non-Tz column built outside `database/migrations` — a test-suite helper
 * that spins up a scratch table, for instance. Out of the rule's scope.
 */
final class OutsideMigrationsTimestamps
{
    public function up(): void
    {
        Schema::create('scratch', function (Blueprint $table): void {
            $table->timestamp('published_at');
            $table->timestamps();
        });
    }
}
