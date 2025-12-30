<?php

declare(strict_types=1);

namespace App\Infrastructure\Ulid;

use Ulid\Ulid;

final class RobinvdvleutenUlidGenerator implements UlidGenerator
{
    public function generate(): string
    {
        return (string)Ulid::generate();
    }
}
