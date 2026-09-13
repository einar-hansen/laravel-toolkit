<?php

declare(strict_types=1);

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class TzColumns
{
    public function up(): void
    {
        Schema::create('gadgets', function (Blueprint $table): void {
            $table->id();
            $table->timestampTz('published_at');
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }
}
