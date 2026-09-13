<?php

namespace EinarHansen\Toolkit\Tests\Fixtures\PHPStan\Sql;

final class SqlIdentifier
{
    public static function quote(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
}
