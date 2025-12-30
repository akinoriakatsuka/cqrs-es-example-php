<?php

declare(strict_types=1);

namespace App\Infrastructure\Ulid;

use Ulid\Exception\InvalidUlidStringException;
use Ulid\Ulid;

final class RobinvdvleutenUlidValidator implements UlidValidator
{
    public function isValid(string $value): bool
    {
        try {
            Ulid::fromString($value);
            return true;
        } catch (InvalidUlidStringException) {
            return false;
        }
    }
}
