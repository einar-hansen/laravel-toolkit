<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class NonTzColumns
{
    public function up(): void
    {
        Schema::create('widgets', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('published_at');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * The Blueprint is not named `$table`, so a rule keyed on the variable
     * name would miss this one.
     */
    public function alterUnderAnotherName(): void
    {
        Schema::table('widgets', function (Blueprint $blueprint): void {
            $blueprint->timestamp('archived_at')->nullable();
        });
    }
}
